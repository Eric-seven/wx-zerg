<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/27 0027
 * Time: 17:02
 */

namespace app\lib\exception;


class UserException extends BaseException
{
    public $code = 404;
    public $msg = '用户不存在';
    public $errorCode = 60000;
}