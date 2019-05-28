<?php
/**
 * Created by IntelliJ IDEA.
 * User: 七友
 * Date: 2019/5/28
 * Time: 12:08
 */

namespace app\user\controller;


use common\base\BaseApiController;
use common\utils\TokenUtils;
use think\Config;

class Alipay extends BaseApiController
{

    function pay() {

        if(!$this->check_login()) return $this->setNotLogin();
        $user = session('user');

        $order_no = trim(input('order_no'));
        if($order_no == '')
            return $this->setError("请传入订单编号");

        //验证订单是否属于改用户，以及订单状态
        $order = db('order')->where('order_no', $order_no)->where('user_id', $user['id'])->find();

        if(empty($order))
            return $this->setError('订单编号有误');

        if($order['stauts'] != ORDER_WAIT)
            return $this->setError('订单已经支付');

        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = trim($order['order_no']);

        //订单名称，必填
        $title = 'XZCODER内网穿透 - ' . ($order['order_type'] == ORDER_TYPE_OPEN ? ORDER_TYPE_OPEN_TEXT : ORDER_TYPE_KEEP_TEXT );
        $subject = trim($title);

        //付款金额，必填
        $total_amount = $order['money'];

        //商品描述，可空
        $body = trim('');

        vendor('alipay.pagepay.service.AlipayTradeService');
        vendor('alipay.pagepay.buildermodel.AlipayTradePagePayContentBuilder');

        //构造参数
        $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setOutTradeNo($out_trade_no);

        $config = Config::get('alipay');

        $aop = new \AlipayTradeService($config);

        /**
         * pagePay 电脑网站支付请求
         * @param $builder 业务参数，使用buildmodel中的对象生成。
         * @param $return_url 同步跳转地址，公网可以访问
         * @param $notify_url 异步通知地址，公网可以访问
         * @return $response 支付宝返回的信息
         */
        $response = $aop->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);

        //输出表单
        var_dump($response);
    }

    function notify() {
        vendor('alipay.pagepay.service.AlipayTradeService');

        $config = Config::get('alipay');

        $arr=$_POST;
        $alipaySevice = new \AlipayTradeService($config);
//        $alipaySevice->writeLog(var_export($_POST,true));
        $result = $alipaySevice->check($arr);

        if($result) {//验证成功
            //商户订单号
            $out_trade_no = $_POST['out_trade_no'];
            //支付宝交易号
            $trade_no = $_POST['trade_no'];
            //订单金额
            $total_amount = $_POST['total_amount'];
            //交易状态
            $trade_status = $_POST['trade_status'];

            /*
             * 对账验单
             */
            $order = db('order')->where('order_no', $out_trade_no)->find();
            if(empty($order))
                return "fail";

            //验证订单订单金额与实际支付金额是否相等
            if($order['money'] != $total_amount)
                return "fail";

            //验证订单状态
            if($order['stauts'] == ORDER_WAIT) {
                //修改订单状态为已支付
                db('order')->where('order_no', $order['order_no'])->update(['stauts' => ORDER_OVER]);
                //修改对应隧道为开启并计算到期时间
                $days = $order['num'] * 30;//购买天数
                $over_date = date('Y-m-d H:i:s', strtotime("+$days day"));
                db('channel')->where('id', $order['channel_id'])
                    ->update([
                        'over_date' => $over_date,
                        'enable' => ENABLE,
                    ]);
                echo "success";	//请不要修改或删除
            }else {
                echo "fail";

            }

            //echo "success";	//请不要修改或删除
        }else {
            //验证失败
            echo "fail";
        }

    }

    function return() {
        return $this->setSuccess('支付成功');
    }

    function test() {
        echo date('Y-m-d H:i:s', strtotime('+10 day'));
    }

}