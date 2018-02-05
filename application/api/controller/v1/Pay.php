<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/2/5 0005
 * Time: 16:54
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\controller\validate\IDMustBePostiveInt;

class Pay extends BaseController
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'getPreOrder']
    ];


    public function getPreOrder(){
        (new IDMustBePostiveInt())->goCheck();

    }

}