<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/27 0027
 * Time: 15:36
 */

namespace app\api\controller\v1;


use app\api\controller\validate\AddressNew;
use app\api\service\Token as TokenService;
use app\api\model\User as UserModel;
use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenException;
use app\lib\exception\SuccessMessage;
use app\lib\exception\TokenException;
use app\lib\exception\UserException;
use think\Controller;

class Address extends Controller
{
    protected $beforeActionList = [
        'checkPrimaryScope' => ['only' => 'createOrUpdateAddress']
    ];

    protected function checkPrimaryScope(){
        $scope = TokenService::getCurrentTokenVar('scope');
        //判断有没有token，是不是已经过期了
        if($scope){
            if($scope >= ScopeEnum::User){
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

    public function createOrUpdateAddress(){
        $validate = new AddressNew();
        $validate->goCheck();

        //根据Token来获取uid
        //根据uid查询用户数据，判断用户是否存在，如果不存在抛出异常
        //获取用户从客户端提交过来的地址信息
        //根据用户地址信息是否存在，从而判断是添加地址还是更新地址

        $uid = TokenService::getCurrentUid();
        $user = UserModel::get($uid);
        if(!$user){
            throw new UserException();
        }

        $dataArray = $validate->getDataByRule(input('post.'));
        $userAddress = $user->address;
        if(!$userAddress){
            $user->address()->save($dataArray);
        }
        else{
            $user->address->save($dataArray);
        }
//        return $user;
        return json(new SuccessMessage(), 201);
    }
}