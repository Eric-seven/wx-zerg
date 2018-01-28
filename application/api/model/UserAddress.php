<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/28 0028
 * Time: 11:28
 */

namespace app\api\model;


class UserAddress extends BaseModel
{
    protected $hidden = ['id', 'delete_time', 'user_id'];
}