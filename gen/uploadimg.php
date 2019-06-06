<?php
include_once("../../inc_includes.php");

$dprefix="../../";
$d=$dprefix."upload/prd";
$dcoul=$dprefix."images/couleurs";
//$dprefix=null;

$prd_id=$_REQUEST["prd_id"];
$table=$_REQUEST["table"];
//$dir=$_REQUEST["dir"];

if ((!$prd_id) or (!$table)) 
	header("Location: index.php");
$msg="";

function mkbrmrhr($v_dir,$v_name)
{
$tcomt=array("brw"=>150,"mrw"=>280,"hrw"=>500);//TODO CONFIG

	$fname = $v_dir."/".$v_name;

	$img=imagecreatefromjpeg($fname);
	list($largeur,$hauteur)=getimagesize($fname);
	foreach(array("br","mr","hr") as $pfxi)
	{
		if ($tcomt[$pfxi."h"] and $tcomt[$pfxi."w"])
			$cotefix=($largeur/$tcomt[$pfxi."w"]>$hauteur/$tcomt[$pfxi."h"])?"w":"h";//plus gd cote
		else 
			$cotefix=($tcomt[$pfxi."h"])?"h":"w";
		if ($cotefix=="w")
		{
			$largeur2=$tcomt[$pfxi."w"];
			$hauteur2=round(($largeur2/$largeur)*$hauteur);
		}
		if ($cotefix=="h")
		{
			$hauteur2=$tcomt[$pfxi."h"];
			$largeur2=round(($hauteur2/$hauteur)*$largeur);
		}
		$img3=imagecreatetruecolor($largeur2,$hauteur2);
		//imagecopyresized mais en mieux
		imagecopyresampled($img3,$img,0,0,0,0,$largeur2,$hauteur2,$largeur,$hauteur);
		imagejpeg($img3,$v_dir."/".$pfxi."_".$v_name);//qualite 70%
	}
}


if ($_REQUEST["action"]=="upload")
{
	$prd_ref=$_REQUEST["prd_ref"];
//print_r($_FILES); print_r($_REQUEST);
	foreach ($_FILES as $name=>$file)
	{
		if (($file["name"]) and ($file["size"]) 
			and (preg_match(":image/.*jp[e]*g:",$file["type"])))
		{
			$updir=null;
			if (preg_match("/^(upload|couleur)_/",$name))
			{
				if (preg_match("/^upload_/",$name)) $updir=$d;
				if (preg_match("/^couleur_/",$name)) $updir=$dcoul;
				$icoul=preg_replace("/^.*icoul([0-9]*)[^0-9]*$/","$1",$name);
				$f=preg_replace("/^(upload|couleur)_/","",$name);
				foreach(array("prd_ref","prd_rubrique","prdro_ligne","icoul".$icoul) as $freq)
					$f=str_replace($freq,stripslashes($_REQUEST[$freq]),$f);
				$f.=".jpg";
			}
			if ($updir)
			{
				print("move_uploaded_file(".$file['tmp_name'].",". $updir."/".$f.")");
				if (move_uploaded_file($file['tmp_name'], $updir."/".$f))
				{
					if ($_REQUEST[str_replace("upload_","genere_",$name)])
						mkbrmrhr($updir,$f);
					$msg.="OK  ".$file[name]." => ".$f."<br>\n";
				}
				else
					$msg.="ERR ".$file[name]." non chargé <br>\n";
			}
			else //updir
				$msg.="ERR ".$file[name]." non chargé <br>\n";
		}//name,size
	}//for
}//action

$sqlselect=" select * from $table ";
$sqlwhere="	where prd_id=$prd_id";
$tt["prd"]=f_sql2tt0($sqlselect.$sqlwhere);

$tt["msg"]=$msg;
$tt["prd"]["refu"]=str_replace(" ","%20",$tt["prd"]["prd_ref"]);
$tt["prd"]["d"]=$d;
$tt["prd"]["dcoul"]=$dcoul;
$tt["prd"]["couleurs"] = array();


foreach(array("","br_","mr_","hr_") as $xxt)
{
	$fname=$d."/".$xxt.$tt["prd"]["prd_ref"].".jpg";
	if (file_exists($fname))
	{
		list($largeur,$hauteur)=getimagesize($fname); 
		$tt["prd"][$xxt."largeur"]=$largeur;
		$tt["prd"][$xxt."hauteur"]=$hauteur;
		$tt["prd"][$xxt."lh"]=($hauteur)?round(100*$largeur/$hauteur)."%":"";
	}
}


$icoul=0;
foreach(split(" *[/,] *",$tt["prd"]["prdro_couleurs"]) as $couleur)
{
	$couleur=trim($couleur);
	$tt["prd"]["couleurs"][$icoul] = 
		array(
		"prd_ref" => $tt["prd"]["prd_ref"],
		"refu" => str_replace(" ","%20",$tt["prd"]["prd_ref"]),
		"prd_rubrique" => $tt["prd"]["prd_rubrique"],
		"prdro_ligne" => $tt["prd"]["prdro_ligne"],
		"prd_produit" => $tt["prd"]["prd_produit"],
		"couleur" => $couleur,
		"d" => $d,
		"dcoul" => $dcoul,
		"icoul" => $icoul,
		);
	foreach(array("","br_","mr_","hr_") as $xxt)
	{
		$fname=$d."/".$xxt.$tt["prd"]["prd_ref"]."_".$couleur.".jpg";
		if (file_exists($fname))
		{
			list($largeur,$hauteur)=getimagesize($fname); 
			$tt["prd"]["couleurs"][$icoul][$xxt."largeur"]=$largeur;
			$tt["prd"]["couleurs"][$icoul][$xxt."hauteur"]=$hauteur;
			$tt["prd"]["couleurs"][$icoul][$xxt."lh"]=($hauteur)?round(100*$largeur/$hauteur)."%":"";
		}
	}
	$icoul++;
}
	

print filtpl_compile(f_getpagenamehtm(),$tt);
?>
