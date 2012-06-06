<?php

/**
* suppression des caractères accentués d'une string
* @param $str string
* @return string sans les caractères accentués
*/
function removeDiacritics($str) {
	return str_replace(array (
		"à",
		"â",
		"ä",
		"å",
		"ã",
		"á",
		"Â",
		"Ä",
		"À",
		"Å",
		"Ã",
		"Á",
		"æ",
		"Æ",
		"ç",
		"Ç",
		"é",
		"è",
		"ê",
		"ë",
		"É",
		"Ê",
		"Ë",
		"È",
		"ï",
		"î",
		"ì",
		"í",
		"Ï",
		"Î",
		"Ì",
		"Í",
		"ñ",
		"Ñ",
		"ö",
		"ô",
		"ó",
		"ò",
		"õ",
		"Ó",
		"Ô",
		"Ö",
		"Ò",
		"Õ",
		"ù",
		"û",
		"ü",
		"ú",
		"Ü",
		"Û",
		"Ù",
		"Ú",
		"ý",
		"ÿ",
		"Ÿ"
	), array (
		"a",
		"a",
		"a",
		"a",
		"a",
		"a",
		"A",
		"A",
		"A",
		"A",
		"A",
		"A",
		"a",
		"A",
		"c",
		"C",
		"e",
		"e",
		"e",
		"e",
		"E",
		"E",
		"E",
		"E",
		"i",
		"i",
		"i",
		"i",
		"I",
		"I",
		"I",
		"I",
		"n",
		"N",
		"o",
		"o",
		"o",
		"o",
		"o",
		"O",
		"O",
		"O",
		"O",
		"O",
		"u",
		"u",
		"u",
		"u",
		"U",
		"U",
		"U",
		"U",
		"y",
		"y",
		"Y"
	), $str);
}

/**
* nettoyage du nom français de l'oiseau pour créer le répertoire correspondant.
* le répertoire de l'oiseau est le nom francais sans les caractères accentués, sans apostrophe et sans espace, remplacés par des _
*/
function nettoie_nom($string){
	return strtolower(str_replace(array (
		" ",
		"'"),array("_","_"),removeDiacritics($string)));

}
/*
* nettoie les string pour les insert sql. Suppression des ' et remplacement par \'
*/
function escapeSql($string){
	return str_replace(array("'"),array("\'"),$string);
}
/*
* renvoie une string en minuscule avec la premier lettre en maj
*/
function normalizeString($string){
	return ucfirst(strtolower($string));
}

/*
* Genere la commande insert dans la table bird
* insert into bird (id,scientific_name,directory_name,scientific_order_fk,scientific_family_fk) values (1,'morus bassanus','morus_bassanus',1,1);
*/
function genereInsertTableBird($array_scientific_orders,$array_scientific_family,$id,$csvLine){

        $directory_name = nettoie_nom($csvLine[0]);
	$scientific_name = $csvLine[2];
	$data = explode(": ",$csvLine[4]);
	//id = index dans le tableau +1 car les id commencent à 1 et pas 0
	$ordre= array_search(normalizeString($data[0]),$array_scientific_orders)+1;
	//id = index dans le tableau +1 car les id commencent à 1 et pas 0
	$famille=array_search($data[1],$array_scientific_family)+1;
	return "insert into bird (id,scientific_name,directory_name,scientific_order_fk,scientific_family_fk) values (".$id.",'".$scientific_name."','".$directory_name."',".$ordre.",".$famille.");\n";
}

/*
* Genere la commande insert dans la table taxonomy
* //INSERT INTO taxonomy(lang,taxon,bird_fk) VALUES('fr','fou de bassan',1);
*/
function genereInsertTableTaxonomy($id,$csvLine){
	//nom francais
	$insertTaxons="INSERT INTO taxonomy(lang,taxon,searched_taxon, bird_fk) VALUES('fr',\"".$csvLine[0]."\",\"".removeDiacritics($csvLine[0])."\",".$id.");\n";
	if ($csvLine[1]!=''){
		//deuxieme nom francais si existe
		$insertTaxons=$insertTaxons."INSERT INTO taxonomy(lang,taxon,searched_taxon,bird_fk) VALUES('fr',\"".$csvLine[1]."\",\"".removeDiacritics($csvLine[1])."\",".$id.");\n";
	}
	//nom anglais
	$insertTaxons=$insertTaxons."INSERT INTO taxonomy(lang,taxon,searched_taxon,bird_fk) VALUES('en',\"".$csvLine[3]."\",\"".removeDiacritics($csvLine[3])."\",".$id.");\n";
	return $insertTaxons;
}

/*
* Genere la commande insert dans la table description
* //INSERT INTO bird_description(lang,description,bird_fk) VALUES('en','English description',1);
*/
function genereInsertTableDescription($id,$csvLine){
	if ($csvLine[16]!=''){
		return "INSERT INTO bird_description(lang,description,bird_fk) VALUES('fr',\"".$csvLine[16]."\",".$id.");\n";
	}
	else{
		return "";
	}
}

/*
* Genere la commande insert dans la table des ordres scientifiques et des familles
* //INSERT INTO scientific_order(id,name,lang) VALUES(1,'Passeriformes','fr');
* //5eme colonne
*/
function genereInsertTableScientificOrderAndFamily(&$array_scientific_orders,&$array_scientific_family,$csvLine){
	if ($csvLine[4]!=''){
		$data = explode(": ",$csvLine[4]);
		$ordre= normalizeString($data[0]);
		$famille=$data[1];
		$sqlquery="";
		if (!in_array($ordre,$array_scientific_orders)){
			$id=array_push($array_scientific_orders,$ordre);
			$sqlquery .= "INSERT INTO scientific_order(id,name,lang) VALUES(".$id.",\"".$ordre."\",'fr');\n";
		}
		if (!in_array($famille,$array_scientific_family)){
			$id =array_push($array_scientific_family,$famille);

			$sqlquery .= "INSERT INTO scientific_family(id,name,lang) VALUES(".$id.",\"".$famille."\",'fr');\n";
		}
		return $sqlquery;
	}
	else{
		return "";
	}
}
/*
* MAIN
*/

$handlerTableBird = fopen('insert_data_table_bird.sql', 'w');

$handlerTableTaxonomy = fopen('insert_data_table_taxonomy.sql', 'w');

$handlerTableDescription = fopen('insert_data_table_bird_description.sql', 'w');

$handlerTableScientificOrderAndFamily = fopen('insert_data_table_scientific_order_and_family.sql', 'w');

$array_scientific_orders = array();
$array_scientific_family = array();

$idBird=0;
if (($handle = fopen("oiseaux_europe_avibase_ss_rares.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, "|")) !== FALSE) {
	if ($idBird>0) {
		$insertTableScientificOrderAndFamily=genereInsertTableScientificOrderAndFamily($array_scientific_orders,$array_scientific_family,$data);
		$insertTableBird=genereInsertTableBird($array_scientific_orders,$array_scientific_family,$idBird,$data);
		$insertTableTaxonomy=genereInsertTableTaxonomy($idBird,$data);
		$insertTableDescription=genereInsertTableDescription($idBird,$data);
		fwrite($handlerTableBird, $insertTableBird);
		fwrite($handlerTableTaxonomy, $insertTableTaxonomy);
		fwrite($handlerTableDescription, $insertTableDescription);
		fwrite($handlerTableScientificOrderAndFamily, $insertTableScientificOrderAndFamily);
	}
	$idBird++;
    }
    fclose($handle);
}
fclose($handlerTableBird);
fclose($handlerTableTaxonomy);
fclose($handlerTableDescription);
fclose($handlerTableScientificOrderAndFamily);


?>
