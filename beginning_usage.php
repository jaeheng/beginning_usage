<?php
/*
Plugin Name: 模版使用情况
Version: 1.2.1
Plugin URL:
Description: 接收并统计自研模版使用情况
Author: jaeheng
Author URL: https://blog.phpat.com
*/

!defined('EMLOG_ROOT') && exit('access deined!');

function beginning_usage_side_menu()
{
    $icon = '<i class="icofont-dashboard icofont-1x"></i>';
    echo '<li class="nav-item" id="beginning_usage"><a class="nav-link" href="plugin.php?plugin=beginning_usage">' . $icon . '模版使用统计</a></li>';
}

addAction('adm_menu', 'beginning_usage_side_menu');
