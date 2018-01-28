<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/25 0025
 * Time: 17:47
 */

namespace app\api\controller\validate;


class TokenGet extends BaseValidate
{
    protected $rule = [
        'code' => 'require|isNotEmpty'
    ];

    protected $message = [
        'code' => '请传入code'
    ];

}