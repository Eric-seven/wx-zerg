<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/29 0029
 * Time: 15:54
 */

namespace app\lib\exception;


class OrderException extends BaseException
{
    public $code = 404;
    public $msg = '订单不存在，请检查参数';
    public $errorCode = 80000;
}