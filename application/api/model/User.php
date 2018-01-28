<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/25 0025
 * Time: 18:34
 */

namespace app\api\model;


class User extends BaseModel
{
    public function address(){
        return $this->hasOne('UserAddress', 'user_id', 'id');
    }

    public static function getByOpenID($openid){
        $user = self::where('openid', '=', $openid)
            ->find();
        return $user;
    }
}