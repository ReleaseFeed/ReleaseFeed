<?php
//phpinfo();
//初期設定
ini_set( 'display_errors', "1" );//エラー表示
date_default_timezone_set('Asia/Tokyo');//タイムゾーン設定
// 言語
mb_language('Japanese');
// 文字コード
mb_internal_encoding('utf-8');

require_once("func/func.php");
require_once("func/db_func.php");
$release = new Release;
$db = new DataBase;
?>