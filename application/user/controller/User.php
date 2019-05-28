<?php
/**
 * Created by PhpStorm.
 * User: 朱帅
 * Date: 2019/5/20
 * Time: 17:26
 */

namespace app\user\controller;

use common\base\BaseApiController;

class User extends BaseApiController
{

    function userinfo() {
        if(!$this->check_login()) return $this->setNotLogin();
        return $this->setSuccessData(session('user'));
    }

}