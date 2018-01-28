<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/28 0028
 * Time: 13:49
 */

namespace app\lib\exception;


class ForbiddenException extends BaseException
{
    public $code = 403;
    public $msg = '权限不够';
    public $errorCode = 10001;
}