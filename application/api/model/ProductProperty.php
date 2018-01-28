<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/27 0027
 * Time: 14:36
 */

namespace app\api\model;


class ProductProperty extends BaseModel
{
    protected $hidden=['product_id', 'delete_time', 'id'];
}