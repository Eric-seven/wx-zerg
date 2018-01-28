<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/10
 * Time: 23:21
 */

namespace app\api\controller\validate;


use think\Validate;

class IDMustBePostiveInt extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',
//        'num' => 'in:1,2,3'
    ];

    protected $message = [
        'id' => 'id必须是正整数'
    ];


}