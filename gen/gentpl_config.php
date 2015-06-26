<?php
$cfdir_gentpl="gen";
$cfdir_root=".";
while(!is_dir("$cfdir_root/$cfdir_gentpl")) $cfdir_root=$cfdir_root."/..";
$cfdir_gen="$cfdir_root/$cfdir_gentpl/";
$cfdir_inc="$cfdir_root/$cfdir_gentpl/";
$cfdir_src="$cfdir_root/$cfdir_gentpl/";
$cfdir_tab="$cfdir_root/$cfdir_gentpl/tab/";
$cfdir_ins="$cfdir_root/";
$cfdir_fck="/inc/lib_js/FCKeditor/";

mysql_connect($db_host,$db_user,$db_pass)||die;
mysql_select_db($db_name)||die;
?>
