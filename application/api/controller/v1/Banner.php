<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/22
 * Time: 21:36
 */

namespace app\api\controller\v1;
use app\api\controller\validate\IDMustBePostiveInt;
use app\api\model\Banner as BannerModel;

use app\lib\exception\BannerMissException;
use think\Exception;

class Banner {
    /**
     * 获取指定id的Banner信息
     * @url /banner/:id
     * @http GET
     * @id $id Banner 的id
     */
    public function getBanner($id){
        //AOP 面向切面编程
        (new IDMustBePostiveInt())->goCheck();

        $banner = BannerModel::getBannerById($id);
//        $banner->hidden(['delete_time','update_time']);
//        $banner->visible(['id']);

        if(!$banner){
            throw new BannerMissException();
        }
        return json($banner);

    }
}