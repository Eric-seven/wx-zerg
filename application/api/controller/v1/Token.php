<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/25 0025
 * Time: 17:42
 */

namespace app\api\controller\v1;


use app\api\controller\validate\TokenGet;
use app\api\service\UserToken;

class Token
{

    public function getToken($code=''){
        (new TokenGet())->goCheck();
        $ut = new UserToken($code);
        $token = $ut->get();
        $result['token'] = $token;
        return json($result);

        //微信客户端不能直接以数组形式返回
//        return [
//            'token' => $token
//        ];
    }
}