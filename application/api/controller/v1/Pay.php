<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/2/5 0005
 * Time: 16:54
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\controller\validate\IDMustBePostiveInt;
use app\api\service\Pay as PayService;
use app\api\service\WxNotify;
class Pay extends BaseController
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'getPreOrder']
    ];


    public function getPreOrder($id=''){
        (new IDMustBePostiveInt())->goCheck();
        $pay = new PayService($id);
        $result = $pay->pay();
        return json($result);

    }

    public function redirectNotify()
    {
        $notify = new WxNotify();
        $notify->handle();
    }

    public function receiveNotify(){
        // 通知频率为15/15/30/180/1800/1800/1800/1800/3600 单位：秒

        // 1.检查库存量，超卖
        // 2.更新这个订单的status状态
        // 3.减库存
        // 如果处理成功，我们返回处理成功的信息。否则，我们需要返回没有成功的处理。

        // 特点：post: xml格式：不会携带参数

//        $xmlData = file_get_contents('php://input');
//        Log::error($xmlData);
//        $notify = new WxNotify();
//        $notify->handle();
        $xmlData = file_get_contents('php://input');
        $result = curl_post_raw('http:/zerg.cn/api/v1/pay/re_notify?XDEBUG_SESSION_START=13133',
            $xmlData);

        // 如果不return 微信永远接收不到处理通知结果
//        return $result;
//        Log::error($xmlData);
    }

}