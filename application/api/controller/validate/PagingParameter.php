<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/2/13 0013
 * Time: 10:27
 */

namespace app\api\controller\validate;


class PagingParameter extends BaseValidate
{
    protected $rule = [
        'page' => 'isPositiveInteger',
        'size' => 'isPositiveInteger',
    ];

    protected $message = [
        'page' => '分页参数必须是正整数',
        'size' => '分页参数必须是正整数',
    ];
}