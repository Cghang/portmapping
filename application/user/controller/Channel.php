<?php
/**
 * Created by PhpStorm.
 * User: 朱帅
 * Date: 2019/5/20
 * Time: 16:51
 */

namespace app\user\controller;

use common\base\BaseApiController;
use common\utils\TokenUtils;
use think\Db;

class Channel extends BaseApiController
{

    //查询用户的所有隧道
    function index() {

        if(!$this->check_login()) return $this->setNotLogin();
        $user = session('user');

        $ispage = input('is_page/b') ?? true;
        $page_size = input('size/d') ?? DEFAULT_PAGE_SIZE;

        $channels = [];

        if($ispage) {
            $channels = Db::table('channel')->alias('c')
                ->join('host h', 'c.host_id = h.id')
                ->join('protocol p', 'c.protocol = p.id')
                ->where('c.user_id', $user['id'])
                ->where('c.enable', ENABLE)
                ->field('c.id, c.name, p.protocol_name, c.local_ip, c.local_port, c.over_date, c.cus_domain, c.token, h.domain, c.ins_date')
                ->paginate($page_size);
        } else {
            $channels = Db::table('channel')->alias('c')
                ->join('host h', 'c.host_id = h.id')
                ->join('protocol p', 'c.protocol = p.id')
                ->where('c.user_id', $user['id'])
                ->where('c.enable', ENABLE)
                ->field('c.id, c.name, p.protocol_name, c.local_ip, c.local_port, c.over_date, c.cus_domain, c.token, h.domain, c.ins_date')
                ->select();
        }
        return $this->setSuccessData($channels);
    }

    /**
     * 查询的服务器
     * 传入id查询指定服务器
     * 不传参数查询可显示的所有服务器
     */
    function hosts() {

        $id = input('id');
        if($id != null) {
            //查询指定id服务器
            $host = db('host')
                ->where('enable', ENABLE)
                ->where('showable', ENABLE)
                ->where('id', $id)
                ->find();

            if(empty($host))
                return $this->setError('host id is not found');

            /*
             * 验证服务器隧道是否已满
             * 查询隧道数量，是否大于服务器最大端口减去最小端口
             */
            $max = $host['open_max_port'] - $host['open_min_port'];
            $count = db('channel')->where('host_id', $host['id'])->count();
            if($count >= $max)
                return $this->setError('隧道数量已满');

            return $this->setSuccessData($host);
        }

        //查询服务器列表
        $host_list = db('host')
            ->where('enable', 1)
            ->where('showable', 1)
            ->order('sort asc')
            ->select();
        return $this->setSuccessData($host_list);
    }

    //查询所有传输协议
    function protocols() {
        return $this->setSuccessData(db('protocol')->select());
    }

    //检测指定服务器的指定端口是否可用
    function check_port() {
        $host_id = input('host/d');
        $port = input('port/d');

        if($host_id == null || $host_id <= 0)
            return $this->setError('param error：host is wrong');

        if($port == null || $port <= 0)
            return $this->setError('端口号不能为空');

        //验证端口是否在服务器规定范围内
        $host = db('host')->where('id', $host_id)->field('open_min_port,open_max_port')->find();

        if(!$host)
            return $this->setError('param error：host is not found');

        if($port < $host['open_min_port'] || $port > $host['open_max_port'])
            return $this->setError('端口不在规定范围内');

        //验证端口号是否被http占用
        $count = db('domain_port')->where('host_id', $host_id)->where('open_port', $port)->count();
        if($count > 0)
            return $this->setError('端口号已经被占用');

        //验证端口号是否被tcp占用
        $count = db('channel')
            ->where('protocol', TCP_CODE)
//            ->where('enable', ENABLE)
            ->where('host_id', $host_id)
            ->where('cus_domain', $port)
            ->count();
        if($count > 0)
            return $this->setError('端口号已经被占用');

        return $this->setSuccess();

    }

    //检测指定服务器的指定前缀域名是否可用
    function check_domain() {

        $host_id = input('host');
        $domain = input('domain');

        //TODO 验证domain格式是否正确


        //验证hostid是否正确
        $host = db('host')->where('id', $host_id)->count();
        if($host <= 0)
            return $this->setError('服务器id不正确');

        $count = db('domain_port')
            ->where('host_id', $host_id)
            ->where('domain', $domain)
            ->count();

        if($count > 0)//存在该domain，验证失败
            return $this->setError('前缀域名已被注册');
        else
            return $this->setSuccess();
    }

    /**
     * 添加隧道
     * 免费的隧道直接添加
     * 非免费的隧道添加后enable未0，即未启用
     * 订单支付后将enable改为1
     */
    function add() {

        if(!$this->check_login()) return $this->setNotLogin();
        $user = session('user');

        $form = json_decode(input('form'), true);

        //表单验证
        if($form['name'] == '')
            return $this->setError('隧道名称不能为空');
        if($form['protocol'] == TCP_CODE) {
            //TCP，验证远程端口
            if($form['cus_domain'] == '') return $this->setError('远程端口不能为空');
            //TODO 验证端口是否可用

        } else if($form['protocol'] == HTTP_CODE) {
            //HTTP
            if($form['cus_domain'] == '') return $this->setError('前缀域名不能为空');
        } else
            return $this->setError('隧道协议不正确');

        //验证hostid是否正确
        $host = db('host')->where('id', $form['host_id'])
            ->where('enable', 1)->where('showable', 1)->find();

        if(empty($host))
            return $this->setError('服务器id不正确');

        $channel = [
            'name' => $form['name'],
            'token' => TokenUtils::uuid(),
            'user_id' => $user['id'],
            'protocol' => $form['protocol'],
            'host_id' => $form['host_id'],
            'local_ip' => $form['local_ip'],
            'local_port' => $form['local_port'],
            'cus_domain' => $form['cus_domain'],
            'ins_date' => Db::raw('NOW()'),
            'upd_date' => Db::raw('NOW()')
        ];
        if($host['price'] > 0) {
            //收费隧道
            $channel['enable'] = 0;
        } else {
            //免费隧道
            $channel['enable'] = 1;
        }

        //添加隧道记录
        $channel_id = db('channel')->insertGetId($channel);

        //如果是http隧道，添加域名端口映射
        if($form['protocol'] == HTTP_CODE) {
            //生成可用端口
            $channel_model = new \app\user\model\Channel();
            $port = $channel_model->randomUsablePort($form['host_id']);
            //添加映射
            db('domain_port')->insert([
                'host_id' => $form['host_id'],
                'domain' => $form['cus_domain'],
                'open_port' => $port
            ]);
        }

        if($host['price'] > 0) {
            //收费隧道，添加订单记录

            //获取购买时常time
            $time = $form['time'] ?? 1;
            //计算总价
            $money = $time * $host['price'];

            $order_no = TokenUtils::order_no();
            db('order')->insertGetId([
                'order_no' => $order_no,
                'order_type' => ORDER_TYPE_OPEN,
                'user_id' => $user['id'],
                'channel_id' => $channel_id,
                'num' => $time,
                'money' => $money,
                'stauts' => ORDER_WAIT,
                'ins_date' => Db::raw('NOW()')
            ]);
            return $this->setSuccessData(['free' => 0, 'order_no' => $order_no, 'channel_id' => $channel_id]);
        } else {
            //免费隧道
            return $this->setSuccessData(['free' => 1, 'channel_id' => $channel_id]);
        }

    }

}