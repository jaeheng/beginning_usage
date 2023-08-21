<?php
/*
Plugin Name: 模版使用情况
Version: 1.0
Plugin URL:
Description: 接收并统计自研模版使用情况
Author: jaeheng
Author URL: https://blog.phpat.com
*/

!defined('EMLOG_ROOT') && exit('access deined!');

// 1. 记录信息
$url = Input::getStrVar('url', 'unkown');
$blogname = Input::getStrVar('blogname', 'unkown');
$type = Input::getStrVar('type', 'unkown');

$db = Database::getInstance();
$res = $db->query("select * from " . DB_PREFIX . "beginning_usage where url = '{$url}' and type='{$type}'");

if (!$res->fetch_array()) {
    // 没有则记录
    $now = time();
    $db->query("insert into " . DB_PREFIX . "beginning_usage (url, blogname, created_at, type) values ('{$url}', '{$blogname}', $now, '{$type}')");
}

// 2. 输出图片
header('Content-Type: image/jpeg');
readfile(__DIR__ . '/logo.jpg');

