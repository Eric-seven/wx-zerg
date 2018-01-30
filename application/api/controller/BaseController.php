<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/29 0029
 * Time: 11:12
 */

namespace app\api\controller;


use think\Controller;
use app\api\service\Token as TokenService;

class BaseController extends Controller
{

    protected function checkPrimaryScope(){
        TokenService::needPrimaryScope();
    }

    protected function checkExclusiveScope(){
        TokenService::needExclusiveScope();
    }
}