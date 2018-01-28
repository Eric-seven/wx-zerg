<?php
/**
 * Created by Eric-seven.
 * User: Administrator
 * Date: 2018/1/24 0024
 * Time: 21:03
 */

namespace app\api\controller\v1;

use app\api\model\Category as CategoryModel;
use app\lib\exception\CategoryException;

class Category
{
    public function getAllCategories(){
        $categories = CategoryModel::all([], 'img');
        if($categories->isEmpty()){
            throw new CategoryException();
        }
        return $categories;
    }
}