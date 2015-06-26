<?php
include_once ("gentpl_includes.php") ;
/*
session_start();
include_once ("gentpl_config.php") ;
include_once ($cfdir_inc."filtpl.php") ;
include_once ($cfdir_inc."f_gk.php") ;
include_once ($cfdir_inc."f_ttrec.php") ;
include_once ("gentpl_conftab.php") ;
include_once ("gentpl_funcs.php") ;
*/

print '<head>
<link rel="stylesheet" type="text/css" href="../gentpl.css" />
<link rel="stylesheet" type="text/css" href="../clic_gentpl.css" />
<link rel="stylesheet" type="text/css" href="../gentpl_cli.css" />
<script src="/filweb/FCKeditor/fckeditor.js" type="text/javascript"></script>
<script src="../inc/jquery.min.js"         type="text/javascript" ></script>
<link rel="stylesheet" type="text/css" href="../inc/jquery-ui.min.css" />
<script src="../inc/jquery-ui.min.js"         type="text/javascript" ></script>
</head>
<html>
';
//--------------------AFFICHAGE---------------------------------------------------------------------------------
// recup login
if (file_exists($cfdir_inc."db_conf.php")){
	include($cfdir_inc."db_conf.php");
}
	
if ($_REQUEST["action"]=="db_conf"){
	$_SESSION["gttablename"]=$_REQUEST["tablename"];
	$dbstr="";$dbdiff=0;
	foreach(array("db_host","db_user","db_name","db_pass") as $dbk){
		if ($_REQUEST[$dbk]!=$$dbk) { $dbdiff=1; $$dbk=$_REQUEST[$dbk]; }
		$dbstr.="\$$dbk='".$$dbk."';";
	}
	if ($dbdiff){
		print "création de ".$cfdir_inc."db_conf.php <br/>\n";
		file_put_contents($cfdir_inc."db_conf.php","<"."?php ".$dbstr." ?".">");
	}
}
	$db=mysql_connect($db_host,$db_user,$db_pass);
	if ($db) {
		foreach(array("db_host","db_user","db_name","db_pass") as $dbk)
			$_SESSION[$dbk]=$$dbk;
		mysql_select_db($db_name,$db)||die;
		$stabs=f_sql2tt("show tables");
		$stabsks=array_keys($stabs[0]);
		$stabsk=$stabsks[0];
		for($itab=0;$itab<count($stabs);$itab++)
			$stabs[$itab]["zaff"]=$stabs[$itab][$stabsk].
			((file_exists($cfdir_inc.$stabs[$itab][$stabsk]."_inc.php"))?"*":"");
		array_unshift($stabs,array($stabsk=>"","zaff"=>"--selectionner une table--"));//ajout blanc
		$stabsm= f_menu_dyn("stabs","",f_tt2menutab($stabs),"onChange='tablename.value=this.value;submit();'");
	}
$typeshowdb=($db and $_SESSION["gttablename"])?"hidden":"text";
print '
	<form action='.f_getpagename().'>
	<input name=action value=db_conf type=hidden>
	<input name=tablename placeholder=table size=10 type='.$typeshowdb.'>'.$stabsm.'
	<input name=db_host placeholder=db_host size=10 type='.$typeshowdb.' value="localhost"> 
	<input name=db_user placeholder=db_user size=10 type='.$typeshowdb.' value="'.$_SESSION["db_user"].'"> 
	<input name=db_name placeholder=db_name size=10 type='.$typeshowdb.' value="'.$_SESSION["db_name"].'">  
	<input name=db_pass placeholder=db_pass size=10 type='.$typeshowdb.' value="'.$_SESSION["db_pass"].'">
	</form>';
if (!$db) die();

$tablename=$_SESSION["gttablename"];
$ttfilename=$tablename."_inc.php";//fichier tab de conf
$ttarrayname=$tablename;

print $tablename.' : 
	<a href='.f_getpagename().'?action=gentab>Def tb</a> /
	<a href='.f_getpagename().'?action=edit>Edit tb</a> /
	<a href='.f_getpagename().'?action=codespec>Code Spec</a>-
	<a href='.f_getpagename().'?action=codespec&table=everytable>(E)</a> /
	<a href='.f_getpagename().'?action=install>Install</a> / 
	<a href='.$cfdir_ins.$tablename.'.php target=test>Use</a> /
	<a href='.f_getpagename().'?action=regenerate&over=1>Genere tout</a> / 
	<a href='.f_getpagename().'?tablename=&action=db_conf>logout</a> / 
	<br>
';


//------ALL-----
if ($_REQUEST["action"]=="regenerate"){
	$headermenustr="";
	array_shift($stabs);
	foreach($stabs as $tstabs){
		$tablename=$tstabs[$stabsk];
		$ttfilename=$tablename."_inc.php";
		$ttarrayname=$tablename;
		print "$tablename \n";
		if (!file_exists($cfdir_tab.$tablename."_inc.php"))
			$tb=tbcretb($db,$tablename);
		else
		{
			include_once ($cfdir_tab.$ttfilename);
			$tb=$$ttarrayname;
		}
		$tb=gentpl_tbajoutdef($tb);
		gentpl_tbsavtb($ttarrayname,$ttfilename,$tb);
		//gentpl_action_gentpl($tb);
		copy($cfdir_gen."table_tpl.php",$cfdir_ins.$tablename.".php");
		$headermenustr.="'$tablename',";
	}
	file_put_contents($cfdir_ins."tables_inc.php","<"."?php $tables_list=Array(".$headermenustr."); ?".">");
	exit;
}

if (!$_SESSION["gttablename"]) exit;

//--------------------CREATION TABLEAU---------------------------------------------------------------------------------

if ($_REQUEST["action"]=="gentab")
{
	$tb=tbcretb($db,$tablename);

    mysql_close();
    exit;
}
//--------------------EDITION TABLEAU---------------------------------------------------------------------------------

if ($_REQUEST["action"]=="edit"){
	if ($_REQUEST["editarray_action"]=="mod")
	{
		$req=array();
		foreach(array_keys($_REQUEST) as $rqk)
			if (substr($rqk,0,1)=='#')
				$req[substr($rqk,1)]=$_REQUEST[$rqk];
		$tt=f_ttrecmakt($req);
		if ($_REQUEST["editarray_del"])
			unset($tt[$_REQUEST["editarray_del"]]);
		if ($_REQUEST["editarray_add"])
			$tt[$_REQUEST["editarray_add"]]=array("type"=>"hidden");
		if (($_REQUEST["editarray_move_from"]) and ($_REQUEST["editarray_move_to"])
			and ($tt[$_REQUEST["editarray_move_from"]]) and ($tt[$_REQUEST["editarray_move_to"]]))
		{
			$ttbis=array();
			foreach($tt as $ttk=>$ttv)
			{
				if ($ttk==$_REQUEST["editarray_move_to"]) 
					$ttbis[$_REQUEST["editarray_move_from"]]=$tt[$_REQUEST["editarray_move_from"]];
				if ($ttk!=$_REQUEST["editarray_move_from"]) 
					$ttbis[$ttk]=$ttv;
			}
			$tt=$ttbis;
		}
		gentpl_tbsavtb($ttarrayname,$ttfilename,$tt);
	}
}


if (! file_exists($cfdir_tab.$ttfilename)) exit;
include_once ($cfdir_tab.$ttfilename);
$tb=$$ttarrayname;
$tb=gentpl_tbajoutdef($tb);

//--------------------EDITION TABLEAU---------------------------------------------------------------------------------
	
if ($_REQUEST["action"]=="edit"){
	print '
		<form action="'.f_getpagename().'" method=post>
		<input type=hidden name=editarray_action value="mod">
		<input type=hidden name=action value="edit">
		<style>
		.f_ttrechtm2      {width:90%;}
		.f_ttrechtm2_line {width:90%;height: 28px;}
		.f_ttrechtm2_label{float:left;width:50%;text-align:right;height:25px;}
		.f_ttrechtm2_input{float:right;width:50%;height:25px;}
		</style>
		';

//	print f_ttrechtm2("",$tb);
	$tttab=array("field"=>array());
	foreach($tb as $field=>$tfv){
		$ttfield=array("name"=>array(),"field"=>$field);
		foreach($tfv as $name=>$tbv){
			$id="#$field#$name";
			$ttname=array("field"=>$field,"name"=>$name,"label"=>ucwords($name),"id"=>$id,"help"=>$tbtypes_help[$name]);
			$if_type=$type_edit[$name];
			if (substr($name,0,5)=="flag_") $if_type=$type_edit["flag"];
			if (!$if_type) $if_type="else";
			if (!is_array($tbv)) $ttname["value"]=$tbv;
			if (is_array($if_type)) $ttname["menu"]=f_menu_dyn($id ,$tbv,$if_type);
			if (is_array($if_type)) $if_type="menu";
			if ($if_type=="flag") $ttname["checked".$tbv]="checked";
			array_push($ttfield["name"],array("if_".$if_type => $ttname));
		}
		array_push($tttab["field"],$ttfield);
	}
	print(filtpl_compile(implode("",file("gentpl_tab.htm")),$tttab));
	print '
		add field:<input type=text name=editarray_add value=""><br/>
		del field:<input type=text name=editarray_del value=""><br/>
		move field:<input type=text name=editarray_move_from value=""> 
			before <input type=text name=editarray_move_to value=""><br/>
		<input type=submit value=save>
		</form>
		';
exit;
}

//--------------------EDITION DE CODE SPECIFIQUE--------------------------------------------------------------------------
if ($_REQUEST["action"]=="codespec")
{
print "<form action=".f_getpagename()." method=post>
	<input name=action value=codespec type=hidden>
	<input name=action2 value=codespec type=hidden>\n";
	$tablenamev=$tablename;
	if ($_REQUEST["table"]){ // nom de table forcee
		$tablenamev=$_REQUEST["table"];
		print "<input name=table value=$tablenamev type=hidden>";
	}
	foreach($t_codespec as $exthp)
		foreach(array("htm","php") as $exthph){
			$cspe=$tablenamev."_".$exthp."_".$exthph;
			$cspf=$tablenamev."_".$exthp.".".$exthph;
			if ($_REQUEST["action2"]=="codespec"){
				file_put_contents($cfdir_src.$cspf,stripslashes($_REQUEST[$cspe]));
				if (!$_REQUEST[$cspe]) unlink($cfdir_src.$cspf);
			}
			print "<br/>$cspe:<br>\n<textarea cols=70 rows=10 name=".$cspe.">";
			if (file_exists($cfdir_src.$cspf))
				print htmlentities(implode("",file($cfdir_src.$cspf)));
			print "</textarea><br>\n";
		}
print "<input type=submit></form>\n";
}

//------INSTALL-----
if ($_REQUEST["action"]=="install"){
	copy($cfdir_gen."table_tpl.php",$cfdir_ins.$tablename.".php");
	print "$tablename.php copiés dans $cfdir_ins";
}

?>
