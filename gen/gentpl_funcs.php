<?php
//-- ajoute les champs qui manquent, les defaults
function gentpl_tbsavtb($v_ttarrayname,$v_ttfilename,$v_tt)
{
global $cfdir_tab;
	$ttp='$'.$v_ttarrayname."=";
	$fw=fopen($cfdir_tab.$v_ttfilename,"w");
	$ttarray=f_ttrecarray($v_tt);
	fwrite($fw,"<"."?php\n".'$'.$v_ttarrayname.'='.$ttarray.';'."\n?".">");
	fclose($fw);
}

//-- ajoute les champs qui manquent, les defaults
function gentpl_tbajoutdeftbid($tb)
{
	$hasid=0;foreach($tb as $tk=>$tbk) if ($tbk["type"]=="id") $hasid=1;
	if ($hasid) return($tb);
	$tbks=array_keys($tb);
	$tb[$tbks[0]]["type"]="id";//force 1er=id
	return($tb);
}
function gentpl_tbajoutdef($tb)
{
	$tb=gentpl_tbajoutdeftbid($tb);//force 1er=id

	foreach(array_keys($tb) as $tk)
	{
		global $tbtypes;
		if (!$tb[$tk]["label"]) 
		{
			$tkl=preg_replace("/_/"," ",$tk);
			$tkl=ucwords(strtolower($tkl));
			$tb[$tk]["label"]=$tkl;
		}
		
		if(!$tbtypes[$tb[$tk]["type"]]) die("type de donnée inconnu : $tk ".$tb[$tk]["type"].print_r($tb[$tk],1));
		foreach($tbtypes[$tb[$tk]["type"]] as $typk=>$typkv)
			if ($tb[$tk][$typk]=="") 
				$tb[$tk][$typk]=$typkv;

		if (!$tb[$tk]["default"]) $tb[$tk]["default"]="";
		
		if (!$tb[$tk]["typeaff"]) $tb[$tk]["typeaff"]=$tb[$tk]["type"];
		if ($tb[$tk]["menu"]) $tb[$tk]["typeaff"]="menu";
		if ($tb[$tk]["type"]=="id"){
			if (!$tb[$tk]["whereclause"]) $tb[$tk]["whereclause"]="";
			if (!$tb[$tk]["pagination_pag"]) $tb[$tk]["pagination_pag"]=25;
			if (!$tb[$tk]["orderby"]) $tb[$tk]["orderby"]=$tbid;
		}
	}

	return $tb;
}

function tbcretb($db,$tablename){
global $cfdir_tab,$ttarrayname;
	$ttfilename=$tablename."_inc.php";
	$tb=array();
	$hasid=0;
	$result = mysqli_query($db, "select * from $tablename limit 1" );
    $fields = mysqli_fetch_fields($result);
//print_r($fields);
$mysql_data_type_hash = array(
    1=>'tinyint',
    2=>'smallint',
    3=>'int',
    4=>'float',
    5=>'double',
    7=>'timestamp',
    8=>'bigint',
    9=>'mediumint',
    10=>'date',
    11=>'time',
    12=>'datetime',
    13=>'year',
    16=>'bit',
    //252 is currently mapped to all text and blob types (MySQL 5.0.51a)
    252=>'text',
    253=>'varchar',
    254=>'char',
    246=>'decimal'
);
    foreach($fields as $i=>$fld) {
                $field = $fld->name;
        //sql2tt($db,"SHOW COLUMNS FROM ".$tablename);
                $tb[$field]=array(
                        'type'  => $mysql_data_type_hash[$fld->type],
                        'len'   => $fld->length,
            );
		$flags = mysqli_fetch_field_direct ($result , $i);
		//AUTO_INCREMENT_FLAG=512
		if ($flags->flags & 512) $tb[$field]["flag_auto_increment"]=1;
// $flags = mysql_field_flags($fields, $i);
// foreach (explode(' ',$flags) as $flag) if ($flag) $tb[$field]["flag_".$flag]=1;
// not_null, primary_key, unique_key, multiple_key, blob, unsigned, 
// zerofill, binary, enum, auto_increment, timestamp
		if ($tb[$field]["flag_auto_increment"]) {$tb[$field]["type"]="id";$hasid=1;}
                if ($tb[$field]["type"]=="string") $tb[$field]["type"]="text";
                if ($tb[$field]["type"]=="varchar") $tb[$field]["type"]="text";
                if ($tb[$field]["type"]=="blob") $tb[$field]["type"]="longtext";
                if ($tb[$field]["type"]=="real") $tb[$field]["type"]="int";
                if ($tb[$field]["type"]=="float") $tb[$field]["type"]="int";
                if ($tb[$field]["type"]=="tinyint") $tb[$field]["type"]="int";
                if ($tb[$field]["type"]=="datetime") $tb[$field]["type"]="date";
                if ($tb[$field]["type"]=="timestamp") $tb[$field]["type"]="date";
    }
    mysqli_free_result($result);
    if (!$hasid) $tb[$fields[0]->name]["type"]="id";//force 1 chp
	if (file_exists($cfdir_tab.$ttfilename) and ($_REQUEST["over"]==1)) {
		if (file_exists($cfdir_tab."OLD_".$ttfilename)) unlink($cfdir_tab."OLD_".$ttfilename);
		rename($cfdir_tab.$ttfilename,$cfdir_tab."OLD_".$ttfilename);
	}
	if (file_exists($cfdir_tab.$ttfilename)){
		if (file_exists($cfdir_tab."NEW_".$ttfilename))
			unlink($cfdir_tab."NEW_".$ttfilename);
		print "création de ".$cfdir_tab."NEW_".$ttfilename.
			" <a href='".f_getpagename()."?action=gentab&over=1'>écraser</a><br/>\n";
		$fw=fopen($cfdir_tab."NEW".$ttfilename,"w");
	} else {
		print "création de ".$cfdir_tab.$ttfilename." <br/>\n";
		$fw=fopen($cfdir_tab.$ttfilename,"w");
	}
	fwrite($fw,"<"."?php\n".'$'.$ttarrayname.'='.f_ttrecarray($tb).';'."\n?".">");
	fclose($fw);
	return $tb;
}


//--------------------GENERATION DES PAGES---------------------------------------------------------------------------------

function gentpl_action_gentpl_htm($tb)
{
global $tbtypes,$tthtm,$tablename,$cfrep_inc,$ttfilename,$cfrep_inc,$cfrep_img,$ttarrayname;
global $exthp,$ttphp,$t_codespec,$t_morchtml;
global $cfdir_root,$cfdir_inc,$cfdir_gen,$cfdir_src,$cfdir_ins,$cfrep_inc,$cfrep_img,$cfrep_fck;
//ajout des inclusions specifiques
//foreach($t_codespec as $exthp)
//$tablename="ged_indicateurs_ref";//table

$gentpltpl="gentpl_tpl";
$gentplhtm=$gentpltpl.".htm";
$gentplphp=$gentpltpl.".php";
$tgentplhtm=array();
foreach($t_morchtml as $morchtml)
{
	$fmorchtml=$cfdir_inc.$gentpltpl."_".$morchtml.".htm";//def
	if (file_exists($cfdir_src.$tablename."_".$fmorchtml))
		$fmorchtml=$cfdir_src.$tablename."_".$fmorchtml;
	//print "+".$fmorchtml."<br>\n";
	$tgentplhtm=array_merge($tgentplhtm,file($fmorchtml));
}
//die(implode("",$tgentplhtm));
//$f=fopen($gentplhtm,"w");
//fwrite($f,gentpl_action_gentpl_html());
//fclose($f);

//-- ajoute les champs qui manquent
foreach(array_keys($tb) as $tk)
	$tb[$tk]["id"]=$tk;//id pour utilité
//alrdn $tb=gentpl_tbajoutdeftbid($tb);//force 1er=id
		
//--decoupe en plusieurs trucs
//champs triés par type
$tbt=array();
foreach(array_keys($tbtypes) as $ttk) $tbt[$ttk]=array();
foreach(array_keys($tb) as $tbk)
	array_push($tbt[$tb[$tbk]["type"]],$tbk);

//champs non id
$tbnid=array();
foreach(array_keys($tb) as $tbk)
	if ($tb[$tbk]["type"])
		if ($tb[$tbk]["type"]!="id")
			array_push($tbnid,$tbk);

//champs list
$tblst=array();
foreach(array_keys($tb) as $tbk)
	if ($tb[$tbk]["list"])
		if ($tb[$tbk]["list"]!=1)
			array_push($tblst,$tbk);

//champ id (un seul pour l'instant
$tbid=$tbt["id"][0];
$whereclause=$tb[$tbid]["whereclause"];
$pagination_pag=$tb[$tbid]["pagination_pag"];
$openpopup=$tb[$tbid]["openpopup"];
//ordre de tri des champs 
$orderby=$tb[$tbid]["orderby"];
/*
$tborderby=array(); foreach(array_keys($tb) as $tbk) if ($tb[$tbk]["order"]) {
	$tborderby[$tb[$tbk]["order"]]=$tbk; if (strtolower($tb[$tbk]["order"])=="desc") $tborderby[$tb[$tbk]["order"]].=" desc";
} $orderby=implode(",",array_values($tborderby));
*/
if ($orderby=="") $orderby=$tbid;
			
//champs menus
$tbmenu=array();
foreach(array_keys($tb) as $tbk)
	if ($tb[$tbk]["menu"])
		array_push($tbmenu,array(
			"name"=>$tbk,
			"f_menu"=> is_array($tb[$tbk]["menu"])?"f_menu_dyn":"f_sql2menu",
			));
			//"menu"=> f_ttrecarray($tb[$tbk]["menu"],



//--------------HTML----------------

$tthtm=array();
$tthtm["tbid"]=$tbid;
$tthtm["tablename"]=$tablename;
$tthtm["ttfilename"]=$cfdir_tab.$ttfilename;
$tthtm["ttarrayname"]=$ttarrayname;
$tthtm["openpopup"]=$openpopup;
//ajout des inclusions specifiques
foreach($t_codespec as $exthp)
	foreach(array($tablename,"everytable") as $tablenamev)
		if (file_exists($cfdir_src.$tablenamev."_".$exthp.".htm")) 
			$tthtm["tablename_".$exthp]=
				"\n<!-- ".$tablenamev."_".$exthp.".htm -->\n".
				implode("",file($cfdir_src.$tablenamev."_".$exthp.".htm")).
				"\n<!-- /".$tablenamev."_".$exthp.".htm -->\n";

$tthtm["fields"]=array();
$tthtm["fields_noid"]=array();
$tthtm["fields_search"]=array();//TODO automatiser..
foreach($tb as $ttb)
{
	$ttb["tbid"]=$tbid;
	$ttbf=$ttb;
	$typeaff=($ttb["editcode"]=="")?$ttb["typeaff"]:"editcode";
	$ttbf[$typeaff]=$ttb; //fields/text/champs
	array_push($tthtm["fields"],$ttbf);
	if ($ttb["type"]!="id")
		array_push($tthtm["fields_noid"],$ttbf);
	if ($ttb["flag_search"]==1)
		array_push($tthtm["fields_search"],$ttbf);
}

$tthtm["line"]=array();
foreach(array_keys($tb) as $tbk)
	if ($tb[$tbk]["list"]==1)
	{
		$tline=array("id"=>$tbk,"tbid"=>$tbid,
					"label"=>$tb[$tbk]["label"],
					"postfix"=>$tb[$tbk]["postfix"],
				"idaff"=>(!$tb[$tbk]["fk_id"])?$tbk:$tb[$tbk]["fk_label"]);
		$href=null;
		if ($tb[$tbk]["flag_href"])
		{
			$tbkid=array_merge($tb[$tbk],array("tbid"=>$tbid,"id"=>$tbk,"tablename"=>$tablename));
			if ($tb[$tbk]["fk_id"])
				$href=$tb[$tbk]["fk_href"];
			else
				$href=$tb[$tbk]["href"];
			$tbkid["href"]=filtpl_compile($href,$tbkid);
			$tline["if_href"]=$tbkid;
		}
		array_push($tthtm["line"],$tline);
	}
/* foreach(array_keys($tbtypes) as $ttk)  foreach($tbt[$ttk] as $tbtk)  array_push($tthtm[$ttk],$tb[$tbtk]); */
$filehtm=filtpl_compile(implode("",$tgentplhtm),$tthtm);
$filehtm=str_replace("[!","{",$filehtm);
$filehtm=str_replace("!]","}",$filehtm);
$filehtm=str_replace("<!--#","<!-- ",$filehtm);
$filehtm=str_replace("\r\n","\n",$filehtm);
$filehtm=preg_replace("/[\n \t]*\n/","\n",$filehtm);
//debug print_r($tthtm);print(implode("",$tgentplhtm));print($filehtm);

if (file_exists($cfdir_gen.$tablename.".htm")){
	if (file_exists($cfdir_gen."OLD_".$tablename.".htm"))
		unlink($cfdir_gen."OLD_".$tablename.".htm");
	rename($cfdir_gen.$tablename.".htm",$cfdir_gen."OLD_".$tablename.".htm");
}
return($filehtm);
}

function gentpl_action_gentpl($tb)
{
global $tbtypes,$tthtm,$tablename,$ttfilename,$ttarrayname;
global $exthp,$ttphp,$t_codespec,$t_morchtml;
global $cfdir_inc,$cfdir_gen,$cfdir_src,$cfdir_ins;
$filehtm=gentpl_action_gentpl_htm($tb);
$f=fopen($cfdir_gen.$tablename.".htm","w");
print "<br>write(".$cfdir_gen.$tablename.".htm)\n";
fwrite($f,$filehtm);
fclose($f);
}


function gentpl_upload($kname,$filezname,$v_id)
{
	global $tablename,$tb,$tbid;
	$file=$_FILES[$filezname];
	if (!(($file["name"]) and ($file["size"])))
		return(null);
	$imgtyp=preg_replace("/^.*\./","",$file["name"]);
	if (preg_match("/^(php|sh|csh|cgi|htm|html|pl|php4|php5|php3)$/",$imgtyp))
		return(null);//security (bof)
	// and (!preg_match(":\.(jp[e]*g|png|gif)$:",$file["type"]))){
	$updir=null;
	{
		$updir=preg_replace(":^/:","",preg_replace(":/$:","",$tb[$kname]["dir"]));
		$upname=$tb[$kname]["name_format"];
		if (preg_match("/select /i",$upname))
		{
			$upname=preg_replace("/\{tbid\}/",$v_id,$upname);
			$tupname=f_sql2tt0($upname);
			$upname=array_pop($tupname);
		}
		else
		{
			foreach($_REQUEST as $k=>$v)
				$upname=str_replace($k,stripslashes($v),$upname);
			foreach(f_sql2tt0("select * from $tablename where $tbid=".f_qqsql($v_id)) as $k=>$v)
				$upname=str_replace($k,stripslashes($v),$upname);
		}
		if ($upname=='') $upname=$v_id;
		$f=$upname;
		if (!preg_match("/\./",substr($f,-5))) // si extension pas definie
			$f.=".".$imgtyp;
	}
	if ($updir)
	{
		//maj chemin du fichier (relatif, sans hr br etc)
		f_sql2tt0("update $tablename set $kname=".f_qqsqlq($upname)." where $tbid=".f_qqsql($v_id));
		$pupdir=preg_replace(":/+:","/",$_SERVER["DOCUMENT_ROOT"]."/".$updir);
		//print("move_uploaded_file(".$file['tmp_name'].",". $updir."/".$f.")");
		$dupdir=preg_replace(":/[^/]*$:","",$pupdir."/".$f);
		if (!is_dir($dupdir)) {mkdir($dupdir,0755,1);chmod($dupdir,0755);}
		if (move_uploaded_file($file['tmp_name'], $pupdir."/".$f))
		{
			if ($tb[$kname]["resize"]!='')
				f_mkbrmrhr($pupdir,$f,$tb[$kname]["resize"]);
			$msg.="OK  ".$file["name"]." => ".$f."<br>\n";
		}
		else
			$msg.="ERR ".$file["name"]." non chargé <br>\n";
	}
	else //updir
		$msg.="ERR ".$file["name"]." non chargé <br>\n";
	return($msg);
}
?>
