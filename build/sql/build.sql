CREATE DATABASE IF NOT EXISTS port_map;
USE port_map;

-- 用户表
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
   id INT PRIMARY KEY AUTO_INCREMENT,
   username VARCHAR(50) COMMENT '账号',
   `password` VARCHAR(100) COMMENT '登陆密码',
   nickname VARCHAR(50) COMMENT '用户昵称',
   email VARCHAR(50) COMMENT '用户邮箱',
   role INT COMMENT '角色，0：用户 1：管理员',
   icon VARCHAR(100) COMMENT '头像路径',
   reg_date DATETIME COMMENT '注册时间'
) COMMENT = '用户表';
-- 默认用户
INSERT INTO `user` VALUE(NULL, 'xzcoder', MD5('123'), '七友', '249795005@qq.com', 1, 'qiyou.png', NOW());


-- 用户登陆日志表
DROP TABLE IF EXISTS user_login_log;
CREATE TABLE user_login_log (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT COMMENT '外键，用户id',
  login_date DATETIME COMMENT '登陆时间',
  login_ip VARCHAR(20) COMMENT '登陆IP'
) COMMENT = '用户登陆日志表';


-- 服务器表
DROP TABLE IF EXISTS `host`;
CREATE TABLE `host` (
  id INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(50) COMMENT '服务器标题',
  price FLOAT COMMENT '价格',
  params VARCHAR(100) COMMENT '服务器参数，json',
  detail VARCHAR(100) COMMENT '服务器详细描述',
  `enable` TINYINT(1) COMMENT '是否启用',
  showable TINYINT(1) COMMENT '是否显示',
  payable TINYINT(1) COMMENT '是否可以购买',
  domain VARCHAR(50) COMMENT '服务器ip地址',
  `port` VARCHAR(20) COMMENT '服务器端口号',
  open_min_port VARCHAR(20) COMMENT '开放端口最小值',
  open_max_port VARCHAR(20) COMMENT '开放端口最大值',
  logo VARCHAR(100) COMMENT '服务器logo地址',
  sort INT COMMENT '排序',
  ins_date DATETIME COMMENT '添加日期',
  upd_date DATETIME COMMENT '修改日期'
) COMMENT = '服务器表';
INSERT INTO `host`
VALUE(NULL, '广州内地200M服务器1', 0, '{"内存": "2G","地区": "广州"}',
'广州FRP服务器，自定义域名需要腾讯云备案 如果在阿里云备案的域名，请尝试使用cdn过来',
1, 1, 1, 'free.xzcoder.com', '10000', '50000', '60000', 'hk-introduction_hover%20(1).jpg', 1, NOW(), NOW());
INSERT INTO `host`
VALUE(NULL, '广州内地200M服务器2', 10, '{"内存": "2G","地区": "广州"}',
'广州FRP服务器，自定义域名需要腾讯云备案 如果在阿里云备案的域名，222222222',
1, 1, 0, 'free2.xzcoder.com', '10000', '50000', '60000', 'hk-introduction_hover%20(1).jpg', 2, NOW(), NOW());


-- 传输协议表
DROP TABLE IF EXISTS protocol;
CREATE TABLE protocol (
  id INT PRIMARY KEY AUTO_INCREMENT,
  protocol_name VARCHAR(20)
) COMMENT = '传输协议表';
-- 默认传输协议
INSERT INTO protocol VALUES(NULL, 'tcp'),(NULL, 'http');


-- 隧道表
DROP TABLE IF EXISTS channel;
CREATE TABLE channel (
  id INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(20) COMMENT '隧道名称',
  token VARCHAR(50) COMMENT '隧道token',
  user_id int COMMENT '用户id',
  protocol INT COMMENT '外键，隧道协议',
  host_id INT COMMENT '外键，服务器id',
  local_ip VARCHAR(20) COMMENT '本地ip',
  local_port VARCHAR(20) COMMENT '本地端口',
  cus_domain VARCHAR(20) COMMENT '自定义域名',
  over_date DATETIME COMMENT '过期时间，NULL为免费不过期',
  description VARCHAR(100) COMMENT '隧道描述',
  enable TINYINT(1) COMMENT '是否启用',
  ins_date DATETIME,
  upd_date DATETIME
) COMMENT = '隧道表';
INSERT INTO channel VALUE(NULL, 'nginx映射', '2fdeeb799a9cc7f3', 1, 2, 1, '127.0.0.1', '80', 'zz', NULL, '本机80端口映射到外网', 1, NOW(), NOW());
INSERT INTO channel VALUE(NULL, 'mysql映射', '2fdeeb799a9cc7f3', 1, 1, 1, '127.0.0.1', '3306','12478', NULL, '3306端口映射到外网12478端口', 1, NOW(), NOW());

-- 自定义域名与端口对应表
DROP TABLE IF EXISTS domain_port;
CREATE TABLE domain_port(
  id INT PRIMARY KEY AUTO_INCREMENT,
  host_id INT COMMENT '服务器id',
  domain VARCHAR(20) COMMENT '自定义域名',
  open_port VARCHAR(10) COMMENT '外网端口'
) COMMENT = '域名关联端口表';
INSERT INTO domain_port VALUE(NULL, 1, 'zz', 50001);


-- 订单类型表
DROP TABLE IF EXISTS order_type;
CREATE TABLE order_type(
  id INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(50) COMMENT '订单类型名称'
) COMMENT = '订单类型表';
-- 默认类型
INSERT INTO order_type VALUES(NULL, '隧道开通'), (NULL, '隧道续费');


-- 订单表
DROP TABLE IF EXISTS `order`;
CREATE TABLE `order`(
  id INT PRIMARY KEY AUTO_INCREMENT,
  order_no VARCHAR(50) COMMENT '订单编号',
  order_type INT COMMENT '外键，订单类型',
  user_id int COMMENT '用户id',
  channel_id INT COMMENT '外键，服务器id',
  num INT COMMENT '下单数量，数量*30=开通天数',
  money FLOAT COMMENT '付款金额',
  stauts INT COMMENT '订单状态',
  ins_date DATETIME
) COMMENT = '订单表';








