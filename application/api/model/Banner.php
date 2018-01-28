<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/11
 * Time: 22:30
 */

namespace app\api\model;


use think\Db;
use think\Exception;
use think\Model;

class Banner extends BaseModel
{
    protected $hidden = ['delete_time','update_time'];

    //定义关联模型的方法items()
    public function items(){
        return $this->hasMany('BannerItem','banner_id','id');
    }
//    protected $table = 'category';
    public static function getBannerById($id)
    {
        //在当前模型内部,直接用self,不需要use
        $banner = self::with(['items','items.img'])->find($id);
        return $banner;
    }
}