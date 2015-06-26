<?php
session_start();
$cfdir_root=".";$cfdir_gentpl="gen";
while(!is_dir("$cfdir_root/$cfdir_gentpl")) $cfdir_root=$cfdir_root."/..";
include_once("$cfdir_root/$cfdir_gentpl/db_conf.php");
include_once("$cfdir_root/$cfdir_gentpl/gentpl_config.php") ;
include_once("$cfdir_root/$cfdir_gentpl/filtpl.php");
include_once("$cfdir_root/$cfdir_gentpl/f_gk.php");
include_once("$cfdir_root/$cfdir_gentpl/f_ttrec.php");
include_once("$cfdir_root/$cfdir_gentpl/inc_gentpl.php");
include_once("$cfdir_root/$cfdir_gentpl/gentpl_conftab.php") ;
include_once("$cfdir_root/$cfdir_gentpl/gentpl_funcs.php") ;
//print("$cfdir_root/$cfdir_gentpl/inc/inc_gentpl.php");
//die(file_exists("$cfdir_root/$cfdir_gentpl/inc/inc_gentpl.php")?"a":"no") ;
//die((function_exists("gentpl_template"))?"exist":"no");
?>
