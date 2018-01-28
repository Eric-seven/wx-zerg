<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/26 0026
 * Time: 14:00
 */

namespace app\lib\exception;


class TokenException extends BaseException
{
    public $code = 401;
    public $msg = 'Token已过期或无效Token';
    public $errorCode = 10001;
}