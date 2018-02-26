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
use think\Loader;
use think\Log;

Loader::import('WxPay.WxPay',EXTEND_PATH,'.Api.php');
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
        return $this->makeWxPreOrder($status['orderPrice']);
    }

    private function makeWxPreOrder($totalPrice){
        $openid = Token::getCurrentTokenVar('openid');
        if(!$openid){
            throw new TokenException();
        }
        $wxOrderData = new \WxPayUnifiedOrder();
        $wxOrderData->SetOut_trade_no($this->orderNO);
        $wxOrderData->SetTrade_type('JSAPI');
        $wxOrderData->SetTotal_fee($totalPrice*100);
        $wxOrderData->SetBody('零食商贩');
        $wxOrderData->SetOpenid($openid);
        $wxOrderData->SetNotify_url(config('secure.pay_back_url'));
        return $this->getPaySignature($wxOrderData);
    }

    private function getPaySignature($wxOrderData){
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData);
        // 失败时不会返回result_code
        if($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] !='SUCCESS'){
            Log::record($wxOrder,'error');
            Log::record('获取预支付订单失败','error');
//            throw new Exception('获取预支付订单失败');
        }
        $this->recordPreOrder($wxOrder);
        $signature = $this->sign($wxOrder);
        return $signature;
//        return $wxOrder; // 返回客户端测试查看结果
    }

    private function sign($wxOrder){
        $jsApiPayData = new \WxPayJsApiPay();
        $jsApiPayData->SetAppid(config('wx.app_id'));
        $jsApiPayData->SetTimeStamp((string)time());
        $rand = md5(time().mt_rand(0,1000));
        $jsApiPayData->SetNonceStr($rand);
        $jsApiPayData->SetPackage('prepay_id='.$wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');

        $sign = $jsApiPayData->makeSign();

        $rawValues = $jsApiPayData->GetValues();
        $rawValues['paySign'] = $sign;

        unset($rawValues['appId']);
        return $rawValues;

    }

    private function recordPreOrder($wxOrder){

        OrderModel::where('id', '=', $this->orderID)
            ->update(['prepay_id' => $wxOrder['prepay_id']]);
    }

    /**
     * 检测订单号是否有效
     * 需要对客户端传过来的订单号进行三种情况的验证：
     * 1.根据订单号查询订单是否存在；2.订单号与当前用户是否匹配；3.要处理的订单是否已支付过
     * @return bool
     * @throws OrderException
     * @throws TokenException
     */
    private function checkOrderValid(){
        $order = OrderModel::where('id', '=', $this->orderID)
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