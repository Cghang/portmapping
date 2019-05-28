<?php
/**
 * Created by PhpStorm.
 * User: 朱帅
 * Date: 2019/5/20
 * Time: 16:48
 */

namespace app\user\controller;

use think\Controller;

class Order extends Controller
{

    /**
     * 查询订单
     */
    function index() {

        if(!$this->check_login()) return $this->setNotLogin();
        $user = session('user');

        //是否分页，默认分页
        $ispage = input('is_page/b') ?? true;
        //页面大小
        $page_size = input('size/d') ?? DEFAULT_PAGE_SIZE;








    }

}