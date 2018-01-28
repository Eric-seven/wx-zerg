<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/26 0026
 * Time: 12:56
 */

namespace app\api\service;


use app\lib\exception\TokenException;
use think\Cache;
use think\Exception;
use think\Request;

class Token
{
    public static function generateToken(){
        //32个字符串组成一组随机字符串
        $randChars = getRandChar(32);
        //用三组字符串进行MD5加密
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        //salt 盐
        $salt = config('secure.token_salt');
        return md5($randChars.$timestamp.$salt);
    }

    public static function getCurrentTokenVar($key){
        $token = Request::instance()
            ->header('token');
        $vars = Cache::get($token);
        if(!$vars){
            throw new TokenException();
        }
        else{
            if(!is_array($vars)){ //Redis 缓存系统会直接返回数组
                $vars = json_decode($vars, true);
            }

            if(array_key_exists($key, $vars)){
                return $vars[$key];
            }
            else{
                throw new Exception('尝试获取的token变量并不存在');
            }

        }
    }

    public static function getCurrentUid(){
        $uid = self::getCurrentTokenVar('uid');
        return $uid;
    }

}