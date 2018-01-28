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
    public function getSimpleList($ids=''){
        (new IDCollection())->goCheck();
        return 'success';
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