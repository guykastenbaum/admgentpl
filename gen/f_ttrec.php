<?php
function f_ttrectab($ttiroot,$tti)
{
	$ttitxt="";
	foreach(array_keys($tti) as $ttik)
	{
		$ttiv=$tti[$ttik];
		if (is_array($ttiv))
			$ttitxt.=f_ttrectab($ttiroot."[$ttik]",$ttiv);
		else
			$ttitxt.=$ttiroot."[$ttik]".'='.'"'.addslashes(stripslashes($ttiv)).'"'."\n";
	}
return($ttitxt);
}

function f_ttrechtm($ttiroot,$tti)
{
	$ttitxt="\n<!-- BEGIN loop_$ttiroot -->\n<div class=f_ttrechtm>";
	foreach(array_keys($tti) as $ttik)
	{
		$ttiv=$tti[$ttik];
		if (is_array($ttiv))
			//$ttitxt.=f_ttrechtm($ttiroot."_$ttik",$ttiv);
			$ttitxt.=f_ttrechtm("$ttik",$ttiv);
		else
			$ttitxt.="\n".'<div class=f_ttrechtm_label style="float:left;">'.$ttik.' : </div>'.
				'<div class=f_ttrechtm_input style="float:left;"><input type=text '.
				' name="'.$ttik.'" '.
				' value="'.str_replace('"','\\"',stripslashes($ttiv)).'"'.
				'></div>'."\n";
	}
	$ttitxt.="</div>\n<!-- END loop_$ttiroot -->\n";
return($ttitxt);
}

function f_ttrechtm2($ttiroot,$tti)
{
	$ttitxt="\n<div class=f_ttrechtm2>$ttiroot<ul>\n";
	foreach(array_keys($tti) as $ttik)
	{
		$ttiv=$tti[$ttik];
		if (is_array($ttiv))
			//$ttitxt.=f_ttrechtm($ttiroot."_$ttik",$ttiv);
			$ttitxt.=f_ttrechtm2($ttiroot.'#'.$ttik,$ttiv);
		else
			$ttitxt.='<div class=f_ttrechtm2_line>'.
				'<div class=f_ttrechtm2_label>'.$ttik.' : </div>'.
				'<div class=f_ttrechtm2_input >'.
				'<input type=text '.
				' name="'.$ttiroot.'#'.$ttik.'" '.
				' value="'.str_replace('"','\\"',stripslashes($ttiv)).'"'.
				'></div></div>'."\n";
	}
	//$ttitxt=preg_replace("|<br/>\n$|","",$ttitxt);
	$ttitxt.="\n</ul></div>\n";
return($ttitxt);
}

function f_ttrecarrayv($ttiroot,$tti)
{
	$ttitxt="\n".$ttiroot."Array(\n";
	foreach(array_keys($tti) as $ttik)
	{
		$ttiv=$tti[$ttik];
		$ttitxt.=$ttiroot."\t'$ttik' => ";
		if (is_array($ttiv))
			$ttitxt.=f_ttrecarrayv($ttiroot."\t",$ttiv)."\n";
		else
			$ttitxt.="'".addslashes(stripslashes($ttiv))."',\n";
	}
	$ttitxt.=$ttiroot."),";
return($ttitxt);
}
/* ARRAY => php */
function f_ttrecarray($tti)
{
	return(substr(f_ttrecarrayv("",$tti),0,-1));
}
/* version recursive */
function f_ttrecmakr($ttiroot,$ttiv,$tti)
{
	$tttiroot=explode('#',$ttiroot);
	$ttk=$tttiroot[0];
	if ($ttk=="") $tttiroot=array_pop($tttiroot);
	$ttk=$tttiroot[0];
	if (count($tttiroot)==1) 
		$tti[$ttik]=$ttiv;
	else
		$tti[$ttik]=f_ttrecmakr(implode('#',$tttiroot),$ttiv,$tti);
	return($tti);
}
		
/* version non recursive */
function f_ttrecmakvr($ttiroot,$ttiv)
{
	$tti=$ttiv;
	$ttiroot=preg_replace('/^#/','',$ttiroot);
	foreach(array_reverse(explode('#',$ttiroot)) as $ttk)
		$tti=array($ttk => $tti);
	return($tti);
}
function f_ttrecmakv($ttiroot,$ttiv)
{
	$tti=$ttiv;
	$ttiroot=preg_replace('/^#/','',$ttiroot);
	foreach(array_reverse(explode('#',$ttiroot)) as $ttk)
		$tti=array($ttk => $tti);
	return($tti);
}



/* version non recursive */
function f_ttrecmaktn($ttiroot,$ttiv,&$ttrecmak)
{
	$ttiroot=preg_replace('/^#/','',$ttiroot);
	$tti=&$ttrecmak;
	foreach(explode('#',$ttiroot) as $ttk)
		$tti=&$tti[$ttk];
	$tti=$ttiv;
	return;
}


function f_ttrecmakt($req)
{
	$ttrecmak=array();
	foreach(array_keys($req) as $rqk)
			//f_ttrecmakr($rqk,$req[$rqk],&$ttrecmak);
			f_ttrecmaktn($rqk,$req[$rqk],$ttrecmak);
	return($ttrecmak);
}

/*
$trubtabs=f_sql2tt("SELECT rub_rubrique,rub_nom,rub_parent FROM fil_rubriques WHERE rub_actif=1");
$trubtabm=f_ttr2menutab($trubtabs);
print_r(f_ttrecrmenu2arr($trubtabm));
*/
function f_ttrecrmenu2arr($trubtabm)
{
$trubphp="";
$rublevel=-99;
foreach($trubtabm as $rubc=>$rubm)
{
	$rubn=preg_replace("/^\-*/","",$rubm);
	$rubl=strlen($rubm)-strlen($rubn);
	$trubfer="";
	if ($rubl==$rublevel+1) $trubphp.=' , "type" => "sub" , "sub'.$rublevel.'" => array(';
	if ($rubl<=$rublevel) $trubphp.=' , "type" => "nosub"';
	if ($rubl<=$rublevel)
		for ($i=$rubl;$i<=$rublevel;$i++) $trubfer.=')),';
	$trubfer=substr($trubfer,1);
	$trubphp.=$trubfer."\n";
	$trubphp.=' array ( "code" => "'.$rubc.'", "nom" => "'.$rubn.'"';
	$rublevel=$rubl;
}
$trubphp.=")";
for ($i=0;$i<$rublevel;$i++) $trubphp.='))';
eval ('$'."trubarr=array(".$trubphp.');');
return($trubarr);
}
?>
