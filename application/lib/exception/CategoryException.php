<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/24 0024
 * Time: 21:24
 */

namespace app\lib\exception;


class CategoryException extends BaseException
{
    public $code = 404;
    public $msg = '指定类目不存在，请检查参数';
    public $errorCode = 50000;
}