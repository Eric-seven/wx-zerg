<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/25 0025
 * Time: 18:35
 */

namespace app\api\service;


use app\lib\enum\ScopeEnum;
use app\lib\exception\TokenException;
use app\lib\exception\WeChatException;
use app\api\model\User as UserModel;
use think\Db;
use think\Exception;

class UserToken extends Token
{
    protected $code;
    protected $wxAppID;
    protected $wxAppSecretID;
    protected $wxLoginUrl;

    public function __construct($code)
    {
        $this->code = $code;
        $this->wxAppID = config('wx.app_id');
        $this->wxAppSecretID = config('wx.app_secret');
        $this->wxLoginUrl = sprintf(config('wx.login_url'),
            $this->wxAppID, $this->wxAppSecretID, $this->code);

    }

    public function get(){
        $result = curl_get($this->wxLoginUrl); //返回的是字符串
        $wxResult = json_decode($result, true); //转化为数组，更好处理
        if(empty($wxResult)){
            throw new Exception('获取session_key及openID异常，微信内部错误');
        }
        else{
            $loginFail = array_key_exists('errcode', $wxResult);
            if($loginFail){
                $this->processLoginError($wxResult);
            }
            else{
                return $this->grantToken($wxResult);
            }
        }
    }

    private function grantToken($wxResult){
        //拿到openid
        //数据库里查一下，这个openid是不是已经存在
        //如果存在则不处理，如果不存在新增一条user记录
        //生成令牌，准本缓存数据，写入缓存
        //把令牌返回到客户端去
        //key: 令牌
        //value: wxResult、uid、scope
        $openid = $wxResult['openid'];
        $user = UserModel::getByOpenID($openid);
        if(!$user){
            $uid = $this->newUser($openid);
        }
        else{
            $uid = $user->id;
        }
        $cachedValue = $this->prepareCachedValue($wxResult, $uid);
        $token = $this->saveToCache($cachedValue);
        return $token;
    }

    private function saveToCache($cachedValue){
        $key = self::generateToken();
        $value = json_encode($cachedValue);
        $expire_in = config('setting.token_expire_in');

        $request = cache($key, $value, $expire_in);
        if(!$request){
            throw new TokenException([
                'msg' => '服务器缓存异常',
                'errorCode' => 10005
            ]);
        }
        //返回令牌给客户端
        return $key;
    }

    private function prepareCachedValue($wxResult, $uid){
        $cachedValue = $wxResult;
        $cachedValue['uid'] = $uid;
        $cachedValue['scope'] = ScopeEnum::User;
        return $cachedValue;
    }

    private function newUser($openid){
        $user = UserModel::create([
            'openid' => $openid
        ]);
        return $user->id;
    }

    private function processLoginError($wxResult){
        throw new WeChatException([
            'msg' => $wxResult['errmsg'],
            'errorcode' => $wxResult['errcode']
        ]);
    }

}