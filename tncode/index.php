<?php
/**
 * Created by PhpStorm.
 * User: xhm
 * Date: 2017/7/7
 * Time: 14:42
 */
ini_set('display_errors','On');
error_reporting(7);
require_once dirname(__FILE__).'/AuthCode.class.php';

//获取验证图片
$authCode = new AuthCode();
$authCode->make();
