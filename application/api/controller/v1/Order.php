<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/28 0028
 * Time: 16:04
 */

namespace app\api\controller\v1;


use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenException;
use app\lib\exception\TokenException;
use think\Controller;
use app\api\service\Token as TokenService;

class Order extends Controller
{
    // 用户在选中商品后，向API提交包含它所选择商品的相关信息
    // API在接收到消息后，需要检查订单相关商品的库存量
    // 有库存，把订单数据存入数据库中，下单成功，返回客户端消息，告诉客户可以支付了
    // 调用我们的支付接口，进行支付
    // 还需要再次进行库存量检测
    // 服务器这边就可以调用微信的支付接口进行支付
    // 微信会返回给我们一个支付结果（异步调用）
    // 成功：也需要进行库存量的检查
    // 成功：进行库存量的扣除（失败：返回一个支付失败的结果。微信会直接返回）

    protected function checkExclusiveScope(){
        $scope = TokenService::getCurrentTokenVar('scope');
        //判断有没有token，是不是已经过期了
        if($scope){
            if($scope == ScopeEnum::User){
                return true;
            }
            else{
                throw new ForbiddenException();
            }
        }
        else{
            throw new TokenException();
        }
    }

    public function placeOrder(){

    }

    public function deleteOrder(){
        
    }

}