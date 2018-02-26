<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/20
 * Time: 14:50
 */

namespace app\api\model;


class Theme extends BaseModel
{
    protected $hidden = ['delete_time', 'update_time'];

    public function topicImg(){
        return $this->belongsTo('Image', 'topic_img_id', 'id');
    }

    public function headImg(){
        return $this->belongsTo('Image', 'head_img_id', 'id');
    }

    public function products(){
        return $this->belongsToMany('Product', 'theme_product', 'product_id', 'theme_id');
    }

    public static function getThemeWithProducts($id){
        $theme = self::with('products,topicImg,headImg')->find($id);
        return $theme;
    }

    /**
     * 获取主题列表
     * @param $ids array
     * @return array
     */
    public static function getThemeList($ids)
    {
        if (empty($ids))
        {
            return [];
        }
        // 讲解with的用法和如何预加载关联属性的关联属性
        // 不要在这里就toArray或者toJSON
        $themes = self::with('products,img')
//            ->with('products.imgs')
            ->select($ids);
        return $themes;
        //        foreach ($themes as $theme) {
        //            foreach($theme->products as $product){
        //                $url = $product->img;
        //            }
        //        }
        // 讲解collection的用法，可以在Model中配置默认返回数据集，而非数组
        //        $themes = User::with(['orders'=>function($query){
        //            $query->where('order_no', '=', 7);
        //        }])->select();
        //        return collection($themes)->toArray();
    }
}