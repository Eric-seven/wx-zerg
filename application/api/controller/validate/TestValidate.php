<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/24
 * Time: 20:06
 */

namespace app\api\controller\validate;

use think\Validate;

class TestValidate extends Validate {
    protected $rule = [
        'name' => 'require|max:10',
        'email' => 'email'
    ];
}