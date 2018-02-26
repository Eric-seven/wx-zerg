<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/20
 * Time: 14:48
 */

namespace app\api\controller\v1;


use app\api\controller\validate\IDCollection;
use app\api\controller\validate\IDMustBePostiveInt;
use app\api\model\Theme as ThemeModel;

class Theme
{
    /**
     * @ url /theme?ids=id1,id2,id3,...
     * @ return 一组theme模型
     */
//    public function getSimpleList($ids=''){
//        (new IDCollection())->goCheck();
//        return 'success';
//    }

    /**
     * @url     /theme?ids=:id1,id2,id3...
     * @return  array of theme
     * @throws  ThemeException
     * @note 实体查询分单一和列表查询，可以只设计一个接收列表接口，
     *       单一查询也需要传入一个元素的数组
     *       对于传递多个数组的id可以选用post传递、
     *       多个id+分隔符或者将多个id序列化成json并在query中传递
     */
    public function getSimpleList($ids = '')
    {
        $validate = new IDCollection();
        $validate->goCheck();
        $ids = explode(',', $ids);
        $result = ThemeModel::with('topicImg,headImg')->select($ids);
//        $result = ThemeModel::getThemeList($ids);
        if ($result->isEmpty()) {
            throw new ThemeException();
        }

        // 框架会自动序列化数据为JSON，所以这里不要toJSON！
//        $result = $result->hidden(['products.imgs'])
//            ->toArray();
//        $result = $result->hidden([
//            'products.category_id','products.stock','products.summary']);
        return json($result);
    }

    public function getComplexOne($id){
        (new IDMustBePostiveInt())->goCheck();
        $theme = ThemeModel::getThemeWithProducts($id);
        //如果返回的$theme为空，不能直接!$theme 或 empty() 来判断。
        //因为$theme只是返回对象下面的子属性，但对象本身并不为空。
        //所以要使用 isEmpty()对象方法来判断
        if($theme->isEmpty()){
            throw new ThemeException();
        }
        return $theme;
    }

}