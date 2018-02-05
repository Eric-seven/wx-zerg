<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/2/5 0005
 * Time: 17:16
 */

namespace app\api\service;


use app\lib\enum\OrderStatusEnum;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use think\Exception;
use app\api\model\Order as OrderModel;
use app\api\service\Order as OrderService;

class Pay
{
    protected $orderID;
    protected $orderNO;

    public function __construct($orderID)
    {
        if(!$orderID){
            throw new Exception('订单号不允许为');
        }
        $this->orderID = $orderID;
    }

    public function pay(){
        // 订单号可能根本不存在
        // 订单号确实是存在的，但是，和当前用户是不匹配的
        // 订单有可能已经支付过了
        // 进行库存量检测
        // 把最有可能出现的情况放到最前面；对数据库性能消耗小的检测放到前面
        $this->checkOrderValid();
        $orderService = new OrderService();
        $status = $orderService->checkOrderStock($this->orderID);
        if(!$status['pass']){
            return $status;
        }
        // ...
    }

    private function makeWxPreOrder(){

    }

    private function checkOrderValid(){
        $order = OrderModel::where('order_id', '=', $this->orderID)
            ->find();
        if(!$order){
            throw new OrderException();
        }
        if(!Token::isValidOperate($order->user_id)){
            throw new TokenException([
               'msg' => '订单与用户不匹配',
                'errorCode' => 10003
            ]);
        }
        if($order->status != OrderStatusEnum::UNPAID){
            throw new OrderException([
                'msg' => '订单已支付过啦',
                'errorCode' => '80003',
                'code' => 400
            ]);
        }
        $this->orderNO = $order->order_no;
        return true;
    }
}