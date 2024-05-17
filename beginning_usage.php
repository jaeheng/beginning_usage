<?php
/*
Plugin Name: 模版使用情况
Version: 1.1.2
Plugin URL:
Description: 接收并统计自研模版使用情况
Author: jaeheng
Author URL: https://blog.phpat.com
*/

!defined('EMLOG_ROOT') && exit('access deined!');

function beginning_usage_side_menu()
{
    echo '<a class="collapse-item" id="beginning_usage" href="plugin.php?plugin=beginning_usage">模版使用统计</a>';
}

addAction('adm_menu_ext', 'beginning_usage_side_menu');
