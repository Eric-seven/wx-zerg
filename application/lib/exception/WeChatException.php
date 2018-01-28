<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/25 0025
 * Time: 21:38
 */

namespace app\lib\exception;


class WeChatException extends BaseException
{
    public $code = 400;
    public $msg = '微信服务器接口调用失败';
    public $errorCode = 999;
}