<?php
/**
 * Created by PhpStorm.
 * User: 朱帅
 * Date: 2019/5/20
 * Time: 16:27
 */

namespace app\user\controller;

use common\base\BaseApiController;
use common\utils\CommonUtils;
use think\Db;

class Index extends BaseApiController
{

    function login() {
        //登陆操作
        $username = input('username');
        $password = input('password');

        if($username == '') {
            return $this->setError('账号不能为空');
        }
        if($password == '') {
            return $this->setError('密码不能为空');
        }

        //查询DB
        $user = db('user')
            ->where('username', $username)
            ->where('password', md5($password))
            ->find();

        if(empty($user))
            return $this->setError('用户名或密码错误');

        //登陆成功 存放session 添加登陆日志
        session('user', $user);
        db('user_login_log')->insert([
            'user_id' => $user['id'],
            'login_date' => Db::raw('now()'),
            'login_ip' => CommonUtils::get_client_ip()
        ]);
        return $this->setSuccess();
    }


    //退出登陆
    function logout() {
        //清除session中的用户信息
        session('user', null);
        //重定向到登陆页
//        $this->redirect('index/login');
        return $this->setSuccess();
    }

}