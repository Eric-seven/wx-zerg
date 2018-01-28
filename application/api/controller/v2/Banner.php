<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/22
 * Time: 21:36
 */

namespace app\api\controller\v2;

class Banner {
    /**
     * 获取指定id的Banner信息
     * @url /banner/:id
     * @http GET
     * @id $id Banner 的id
     */
    public function getBanner($id){
        return 'This is V2';
    }
}