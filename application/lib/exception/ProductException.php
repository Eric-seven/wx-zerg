<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/24 0024
 * Time: 11:53
 */

namespace app\lib\exception;


class ProductException extends BaseException
{
    public $code = 404;
    public $msg = '指定ID的商品不存在，请检查参数';
    public $errorCode = 20000;
}