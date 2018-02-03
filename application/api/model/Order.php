<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/2/3 0003
 * Time: 21:24
 */

namespace app\api\model;


class Order extends BaseModel
{
    protected $hidden = ['user_id', 'delete_time', 'update_time'];
}