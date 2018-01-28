<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/27 0027
 * Time: 11:36
 */

namespace app\api\model;


class ProductImage extends BaseModel
{
    protected $hidden = ['img_id', 'delete_time', 'product_id'];

    public function imgUrl(){
        return $this->belongsTo('image', 'img_id', 'id');
    }
}