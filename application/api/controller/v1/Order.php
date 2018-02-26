<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/28 0028
 * Time: 16:04
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\controller\validate\IDMustBePostiveInt;
use app\api\controller\validate\OrderPlace;
use app\api\controller\validate\PagingParameter;
use app\api\model\Order as OrderModel;
use app\api\service\Order as OrderService;
use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenException;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use think\Controller;
use app\api\service\Token as TokenService;

class Order extends BaseController
{
    // 用户在选中商品后，向API提交包含它所选择商品的相关信息
    // API在接收到消息后，需要检查订单相关商品的库存量
    // 有库存，把订单数据存入数据库中，下单成功，返回客户端消息，告诉客户可以支付了
    // 调用我们的支付接口，进行支付
    // 还需要再次进行库存量检测
    // 服务器这边就可以调用微信的支付接口进行支付
    // 小程序根据服务器返回的结果拉起微信支付
    // 微信会返回给我们一个支付结果（异步调用）
    // 成功：也需要进行库存量的检查
    // 成功：进行库存量的扣除（失败：返回一个支付失败的结果。微信会直接返回）

    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'placeOrder'],
        'checkPrimaryScope' => ['only' => 'getDetail,getSummaryByUser'],
    ];

    public function getSummaryByUser($page=1, $size=15){
        (new PagingParameter())->goCheck();
        $uid = TokenService::getCurrentUid();
        $pagingOrders = OrderModel::getSummaryByUser($uid, $page, $size);
        // 对象不能直接判空 !$pagingOrders
        if ($pagingOrders->isEmpty()) {
            return [
                'data' => [],
                'current_page' => $pagingOrders->getCurrentPage(),
            ];
        }
        $data = $pagingOrders->hidden(['snap_items', 'snap_address', 'prepay_id'])->toArray(); //将paginate对象转换成数组
        $pagesData = [
            'data' => $data,
            'current_page' => $pagingOrders->getCurrentPage(),
        ];

        return json($pagesData);
    }

    public function getDetail($id){
        (new IDMustBePostiveInt())->goCheck();
        $orderDetail = OrderModel::get($id);
        if(!$orderDetail){
            throw new OrderException();
        }

        return json($orderDetail->hidden(['prepay_id']));
    }

    public function placeOrder(){
        (new OrderPlace())->goCheck();
        $products = input('post.products/a');
        $uid = TokenService::getCurrentUid();
        $order = new OrderService();
        $status = $order->place($uid, $products);
        return json($status);
    }

    public function deleteOrder(){
        
    }

}