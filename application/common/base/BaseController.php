<?php
/**
 * Created by IntelliJ IDEA.
 * User: 七友
 * Date: 2019/5/23
 * Time: 14:51
 */

namespace app\common\base;


use think\Controller;
use think\Request;

class BaseController extends Controller
{


    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    /**
     * 验证登陆
     * @return bool
     */
    function check_login() {
        //验证用户是否登陆
        $user = session('user');
        if($user == null) {

            $params = ['msg' => '请先登录', 'return_url' => urlencode(request()->baseUrl())];

            //登陆页面
            $this->redirect('user/index/login', $params);
            return false;
        }
        return true;
    }

}