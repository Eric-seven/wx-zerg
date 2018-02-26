<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/29 0029
 * Time: 14:19
 */

namespace app\api\service;


use app\api\model\OrderProduct;
use app\api\model\Product;
use app\api\model\UserAddress;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;
use think\Db;
use think\Exception;

class Order
{
    // 订单的商品列表，也就是客户端传递过来的products参数
    protected $oProducts;
    // 真实的商品信息（包含库存量）
    protected $products;

    protected $uid;

    public function place($uid, $oProducts){
        // oProducts和products 作对比
        // products从数据库中查出来
        $this->oProducts = $oProducts;
        $this->products = $this->getProductsByOrder($oProducts);
        $this->uid = $uid;
        $status = $this->getOrderStatus();
        if(!$status['pass']){
            $status['order_id'] = -1;
            return $status;
        }

        // 开始创建订单快照
        $orderSnap = $this->snapOrder($status);
        $order = $this->createOrder($orderSnap);
        $order['pass'] = true;
        return $order;
    }

    private function createOrder($snap){
        Db::startTrans(); //开启事务
        try
        {
            $orderNo = $this->makeOrderNo();
            $order = new \app\api\model\Order();
            $order->user_id = $this->uid;
            $order->order_no = $orderNo;
            $order->total_count = $snap['totalCount'];
            $order->total_price = $snap['orderPrice'];
            $order->snap_items = json_encode($snap['pStatus']);
            $order->snap_name = $snap['snapName'];
            $order->snap_img = $snap['snapImg'];
            $order->snap_address = $snap['snapAddress'];
            $order->save();

            $orderID = $order->id;
            $create_time = $order->create_time;
            // 将创建成功的订单id插入到oProducts数组中，然后更新order_product数据表
            foreach ($this->oProducts as &$p) {
                $p['order_id'] = $orderID;
            }

            $orderProduct = new OrderProduct();
            $orderProduct->saveAll($this->oProducts);

            Db::commit(); //如果都成功就提交
            return [
                'order_no' => $orderNo,
                'order_id' => $orderID,
                'create_time' => $create_time
            ];
        }
        catch (Exception $ex)
        {
            Db::rollback(); //如果有一方写入失败，就回滚
            throw $ex;
        }
    }

    /**
     * 随机字符串算法生成唯一订单号
     * @return string
     */
    public static function makeOrderNo()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn =
            $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%02d', rand(0, 99));
        return $orderSn;
    }

    /**
     * 生成订单快照
     * @param $status
     * @return array
     */
    private function snapOrder($status){
        $snap = [
            'orderPrice' => 0,
            'totalCount' => 0,
            'pStatus' => [],
            'snapAddress' => '',
            'snapName' => '',
            'snapImg' => ''
        ];

        $snap['orderPrice'] = $status['orderPrice'];
        $snap['totalCount'] = $status['totalCount'];
        $snap['pStatus'] = $status['pStatusArray'];
        $snap['snapAddress'] = json_encode($this->getUserAddress());
        $snap['snapName'] = $this->products[0]['name'];
        $snap['snapImg'] = $this->products[0]['main_img_url'];
        if(count($this->products) > 1){
            $snap['snapName'] .= '等';
        }
        return $snap;
    }

    /**
     * 获取当前下单用户的收货地址
     * 如果收获地址不存在，则不允许下单，也就是要抛出下单失败的异常
     * @return array
     * @throws UserException
     */
    private function getUserAddress(){
        $userAddress = UserAddress::where('user_id', $this->uid)
            ->find();
        if(!$userAddress){
            throw new UserException([
                'msg' => '用户收货地址不存在，下单失败',
                'errorCode' => 60001
            ]);
        }
        return $userAddress->toArray();
    }

    /**
     * 库存量检测
     * 返回选定的商品通过检测处理后的详细信息
     * @param int $orderID
     * @return array
     */
    public function checkOrderStock($orderID){
        $oProducts = OrderProduct::where('order_id' ,'=', $orderID)
            ->select();
        $this->oProducts = $oProducts;
        $this->products = $this->getProductsByOrder($oProducts);
        $status = $this->getOrderStatus();
        return $status;
    }

    /**
     * 获取订单状态
     * 是订单综合信息的一个汇总
     * 计算整个订单的总金额，订单中商品的总数量，每个商品的真实信息（以array形式返回）
     *
     * 只要订单中有一个商品出现异常（在下单支付前已下架或删除，则商品id无效）
     * 或其中一个商品库存量不足，则整个订单就创建失败 pass = false
     * @return array
     */
    private function getOrderStatus(){
        $status = [
            'pass' => true,
            'orderPrice' => 0,
            'totalCount' => 0,
            'pStatusArray' => []
        ];

        foreach ($this->oProducts as $oProduct) {
            $pStatus = $this->getProductStatus(
                $oProduct['product_id'],$oProduct['count'],$this->products
            );

            if(!$pStatus['haveStock']){
                $status['pass'] = false;
            }

            $status['orderPrice'] += $pStatus['totalPrice'];
            $status['totalCount'] += $pStatus['count'];
            array_push($status['pStatusArray'], $pStatus);
        }
        return $status;
    }

    /**
     * 获取单个商品的真实状态
     * 用户订单中的某个商品有可能已下架、被删除，所以在下单支付前要每一个都检测。
     * 这种情况就属于某个product_id实际已不存在，因而需要直接抛出异常处理。
     * 此外，还要检测库存量。
     * 如果有库存量，haveStock = true; 否则，haveStock = false;
     * 把以上每个商品的检测处理情况，逐一返回调用这个接口的上一层函数再进行处理。
     * @param $oPID
     * @param $oCount
     * @param $products
     * @return array
     * @throws OrderException
     */
    private function getProductStatus($oPID, $oCount, $products){

        $pIndex = -1;

        $pStatus = [
            'id' => null,
            'haveStock' => false,
            'count' => 0,
            'name' => '',
            'totalPrice' => 0
        ];

        for ($i=0; $i<count($products); $i++) {
            if($oPID == $products[$i]['id']){
                $pIndex = $i;
            }
        }

        if($pIndex == -1){
            // 客户端传递的product_id有可能跟本不存在
            throw new OrderException([
                'msg' => 'id为'.$oPID.'商品不存在，创建订单失败'
            ]);
        }
        else{
            $product = $products[$pIndex];
            $pStatus['id'] = $product['id'];
            $pStatus['count'] = $oCount;
            $pStatus['totalPrice'] = $oCount * $product['price'];

            if($product['stock'] - $oCount >= 0){
                $pStatus['haveStock'] = true;
            }
            return $pStatus;
        }

    }


    /**
     * 根据订单信息查找真实的商品信息
     * 通过visible() 只显示返回'id', 'price', 'stock', 'name', 'main_img_url' 这几个主要字段
     * 用toArray() 将collection 类型转换为数组再返回
     * @param $oProducts
     * @return mixed
     */
    private function getProductsByOrder($oProducts){
        $oPIDs = [];
        foreach ($oProducts as $item) {
            array_push($oPIDs, $item['product_id']);
        }
        // 默认返回的是collection，这里用toArray()转化成数组
        $products = Product::all($oPIDs)
            ->visible(['id', 'price', 'stock', 'name', 'main_img_url'])
            ->toArray();
        return $products;
    }

}