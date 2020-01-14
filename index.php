<?php
$version = "0.0.2";
define('_PATH', dirname(__FILE__));

foreach( $argv as $argument ) {
        if( $argument == $argv[ 0 ] ) continue;

        $pair = explode( "=", $argument );
        $variableName = substr( $pair[ 0 ], 2 );
        if(isset($pair[ 1 ])){
        $variableValue = $pair[ 1 ];
        //echo $variableName . " = " . $variableValue . "\n";
        // Store the variable in $_REQUEST
        $_REQUEST[ $variableName ] = $variableValue;
        }
}
if(isset($_POST["word"])){
$_REQUEST[ "cmd" ] = "search";
$_REQUEST[ "word" ] = $_POST["word"];
}
if ($argc > 0) {$NL = "\n"; $RUNMODE = "cli";} else {$NL = "</br>"; $RUNMODE = "webserv";}
$start_time = time();

$alfredDB_url = "https://github.com/petitpingouin-org/alfred/blob/master/alfred2.zip?raw=true";
$alfredDB_zip = "./alfred2.zip";
$alfredDB = "./alfred2.db";

$zip = new ZipArchive;
if(file_exists($alfredDB)===false){
        if(file_exists($alfredDB_zip)===false){
        file_put_contents($alfredDB_zip, fopen($alfredDB_url, 'r'));
        }
	if ($zip->open($alfredDB_zip) === TRUE) {
	    $zip->extractTo(_PATH);
	    $zip->close();
	    echo 'ok';
	} else {
	    echo 'failed';
	}

}



$db = new SQLite3($alfredDB);
if(isset($_REQUEST[ "cmd" ])){
$CMD = $_REQUEST[ "cmd" ];
}else {
$CMD = "print";
}

switch($CMD)
	{
	case "search":
	if(isset($_REQUEST[ "word" ])){
	$WORD = $_REQUEST[ "word" ];
	$sql_query = "SELECT * FROM Morphalou WHERE word=\"" . $WORD . "\"";
	$sql_query_escaped = SQLite3::escapeString( $sql_query );
	$results = $db->query($sql_query_escaped);
	$unverified_string = "";
        while ($row = $results->fetchArray()) {
                // id, key, word, category, mood, tense, person, gender, number, definition
                $id = $row['id'];
                $key = $row['key'];
                $word = $row['word'];
                $category = $row['category'];
                $mood = $row['mood'];
                $tense = $row['tense'];
                $person = $row['person'];
                $gender = $row['gender'];
                $number = $row['number'];
                $definition = $row['definition'];
		if(base64_decode($definition) != "n/a"){
			$def_string = base64_decode($definition);
			if(strpos($def_string, "<html")){
			$def = "webpage";
			$url = "";
			} else {
			$url = base64_decode($definition);
			$def = "url";
			}
			if($def =="url")
				{
				// https://petitpingouin.org/kiwix/wiktionary_fr_all_maxi/
				//$wiktionary_url = "https://petitpingouin.org/kiwix/wiktionary_fr_all_maxi/";
				//echo str_replace("../", $wiktionary_url, @file_get_contents($url));
				header("Location: " . $url);
				//echo $id . " " . $key . " <a href='" . $url . "' target='_blank'>" . $word . "</a> " . $category . " " . $mood . " " . $tense . " " .$person . " " .$gender . " " . $number . " " . $def . $NL;
			} else {
			$wiktionary_url = "https://petitpingouin.org/kiwix/wiktionary_fr_all_maxi/A/".$word;
			// echo $def_string;
			header("Location: " . $wiktionary_url);
			}
		} else {
                	$unverified_string .= $id . "," . $key . "," . $word . "," . $category . "," . $mood . "," . $tense . "," .$person . "," .$gender . "," . $number . "," . base64_decode($definition) . $NL;
			}
                }
	if($unverified_string==""){
		header("Location: https://petitpingouin.org/kiwix/search?content=wiktionary_fr_all_maxi&pattern=".$WORD);
		} else {
		echo $unverified_string;
		}
	}
	break;
	
	case "list":
	$results = $db->query('SELECT * FROM Morphalou');
	while ($row = $results->fetchArray()) {
		// id, key, word, category, mood, tense, person, gender, number, definition
		$id = $row['id'];
		$key = $row['key'];
		$word = $row['word'];
		$category = $row['category'];
		$mood = $row['mood'];
		$tense = $row['tense'];
		$person = $row['person'];
		$gender = $row['gender'];
		$number = $row['number'];
		$definition = $row['definition'];
		//echo $id . " " . $key . " " . $word . " " . $category . " " . $mood . " " . $tense . " " .$person . " " .$gender . " " . $number . " " . $NL;
		if(base64_decode($definition) != "n/a"){
                        if(strpos(base64_decode($definition), "<html")){
                        $def = "webpage";
                        $url = "https://petitpingouin.org/KaT/alfred/?cmd=search&word=" . $word;
			} else {
                        $url = base64_decode($definition);
                        $def = "url";
                        }
                        echo $id . " " . $key . " <a href='" . $url . "' target='_blank'>" . $word . "</a> " . $category . " " . $mood . " " . $tense . " " .$person . " " .$gender . " " . $number . " " . $url . $NL;
                } else {
                        echo $id . " " . $key . " " . $word . " " . $category . " " . $mood . " " . $tense . " " .$person . " " .$gender . " " . $number . " " . $NL;
                        }
		}
	break;


	default:
	echo "<html><head><title>Alfred " . $version . "</title></head><body>";
	echo "<div align='center'><h1>A.L.F.R.E.D: Assistant Libre de Recherche et Extraction de Données</h1></div>";
	echo "<div align='center'><form action='' method='POST'><input id='searchbox' type='text' name='word'><input type='submit' value='Rechercher'></form></div>";
	echo "<div align='center'><table><tr><td align='center'><h2>Fonctionnement et Usages</h2></td></tr><tr><td align='left'><li>1) La liste initiale(553159 mots) a été construite à partir du registre Morphalou-2.0 du CNRTL(Extraction/conversion des données XML originales)</li><li>2) Chaque mots ont été recherchés(contre-vérification) dans le wiktionnaire pour acquérir la définition.</li><li>3) Après les vérifications croisés, il apparaît que plus de 93% des mots du Morphalou-2.0 sont défini 'tel quel' dans le wiktionnaire.</li><li>4) Lorsque le mot entré figure au Morphalou-2.0 mais aucune définition n'a été trouvé a l'heure actuel pour le mot, alors un résultat CSV/TEXTE est retourné.(ex: ôs)</li><li>5) Lorsque le mot entré ne figure pas au Morphalou-2.0, alors le mot recherché est aiguillé vers le wiktionnaire pour un traitement supplémentaire.(ex: xyz, les mots dérrivés de d'autres langues, les erreurs)</li></td></tr><tr><td align='center'></td></tr></table></div>";
	echo "</body></html>";
	break;
	}

//$end_time = time();<
//$runtime = $end_time - $start_time;

//echo $NL . "Run time: " . $runtime .  " seconds" . $NL;

?>
