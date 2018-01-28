<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/24 0024
 * Time: 21:04
 */

namespace app\api\model;


class Category extends BaseModel
{
    protected $hidden = ['update_time', 'delete_time'];

    public function img(){
        return $this->belongsTo('Image', 'topic_img_id', 'id');
    }
}