<?php 
session_start();
//base chercher
$type="movie";
$user=$_SESSION["id"];
$pseudo=$_SESSION["pseudo"];
$debug=false;
$gl=array();
$nb=20;
$base_rate=5;
$base_min=0.4;
$min_u=1.0;
	$d = array( // critères de pref 
		"reals",
		"genres",
		"cast"
		);
$list_name=array();
include "config.php";
$base_num=get_num_up($user);
// print_r($base_num);

// $f=array(
// 			"interstellar",
// 			"il était une fois en amérique",
// 			"barry lyndon",
// 			"le nom des gens",
// 			"le parrain 2",
// 			"inception",
// 			"les nouveaux sauvages",
// 			"the game"

// 		);
// save_begin($f);

//renvoi un array de pref (real, genres,cast) (id->[frequence,rate])
function getb($t){
	global $user;
	global $gl;
	$user=$_SESSION["id"];
	// print_r($_SESSION);
	$tmdb=get_tmdb3($user);

	foreach ($t as $key => $value) {
		$gl[$value]=$value($user,$tmdb);

	}
}

//calcul la note par pref (frequence et rate) renvoi (id -> note)
function getcinq($t){
	global $gl;
	foreach ($t as $key => $value) {
		$gl[$value]=cinq($gl[$value]);

	}

}
// tri les pref par note desc
function getnorm($t){
	global $gl;
	foreach ($t as $key => $value) {
		arsort($gl[$value]);
		}

}
function pt_real($movie){
	$pt=$value;
	$inf=$GLOBALS["TMDB"]->info($type,$movie);
	$info=$GLOBALS["TMDB"]->info_genres($inf,-1);
	// $genres=
}
function genre_pt($id,$note,$gl){
	global $type;
	$pt=0;
		$inf=$GLOBALS["TMDB"]->info_genres($type,$id);
		// print_r($inf);
		foreach ($inf as $key => $value) {
			// print_r($value);

			$pt+=(array_key_exists($value->id, $gl))?$gl[$value->id]:0;
		}
return $note+$pt;
}
function cast_pt($id,$note,$gl){
global $type;
	$pt=0;
		$inf=$GLOBALS["TMDB"]->info_cast($type,$id);
		// print_r($inf);
		foreach ($inf as $key => $value) {
			// print_r($value);

			$pt+=(array_key_exists($value->id, $gl))?$gl[$value->id]:0;
		}
return $note+$pt;
}
function real_pt($id,$note,$gl){
global $type;
	$pt=0;
		$inf=reals_list(array($id));
		// print_r($inf);
		foreach ($inf as $key => $value) {
			// print_r($value);

			$pt+=(array_key_exists($value, $gl))?$gl[$value]:0;
		}
return $note+$pt;
}
$pt_film=array();

//return un array d'id depuis un array d'tmdb_id
function dod($a,$b){
	// print_r($a);
	// print_r($b);
	$result = array();
foreach($a as $arr){
   if(!in_array($arr["tmdb_id"], $b)){
      $result[] = $arr;
   }
}

return $result;
}

//En se basant sur les films noté par le user
function pref($us){

	global $gl; //tableau pref
	global $base_min; //min cast note
	global $user;  //id user
	global $list_name; //liste pref id -> name
	global $d;
	getb($d); //renvoi un array de pref (real, genres,cast) (id->[frequence,rate])

	getcinq($d); //calcul la note par pref (frequence et rate) renvoi (id -> note)
	getnorm($d); // tri les pref par note desc


	$user=$us;
	$dg=[];		

$i=get_tmdb2($user);
	foreach ($gl["reals"] as $key => $value) { //parcours les pref REALS -> pour trouver des films

		$dgs=real_film($key); // renvois liste d'id de film du 
		$dgs=dod($dgs,$i); // les filtre avec les films deja proposé

		foreach ($dgs as $key2 => $value2) { //parcours les films du real, calcul la note affilié
			if (!(array_key_exists($value2["tmdb_id"], $dg))){
					$dg[$value2["tmdb_id"]]=$value; // note real
					$dg[$value2["tmdb_id"]]=genre_pt($value2,$dg[$value2["tmdb_id"]],$gl["genres"]); // note genre
					$dg[$value2["tmdb_id"]]=cast_pt($value2,$dg[$value2["tmdb_id"]],$gl["cast"]); // note cast
			}

		}

	}

	foreach ($gl["cast"] as $key => $value) {
		//parcours les pref CAST -> pour trouver des films
		if ($value >= $base_min){
			//on cherche les films de l'acteur uniquement si la note est > base_min


			$dgs=cast_film($key);// film de l'acteur
			$dgs=dod($dgs,$i); //filtre films deja connu

			foreach ($dgs as $key2 => $value2) {

				if (!(array_key_exists($value2["tmdb_id"], $dg))){

					$dg[$value2["tmdb_id"]]=$value;
					$dg[$value2["tmdb_id"]]=genre_pt($value2,$dg[$value2["tmdb_id"]],$gl["genres"]);// genre pt
					
					$dg[$value2["tmdb_id"]]=real_pt($value2,$dg[$value2["tmdb_id"]],$gl["reals"]); //real pt
				}

			}
		}

	}
foreach ($gl["genres"] as $key => $value) {
		//parcours les pref CAST -> pour trouver des films
		if ($value >= $base_min){
			//on cherche les films de l'acteur uniquement si la note est > base_min


			$dgs=genres_film($key);// film de l'acteur
			$dgs=dod($dgs,$i); //filtre films deja connu
			foreach ($dgs as $key2 => $value2) {

				if (!(array_key_exists($value2["tmdb_id"], $dg))){


					$dg[$value2["tmdb_id"]]=$value;
					$dg[$value2["tmdb_id"]]=cast_pt($value2,$dg[$value2["tmdb_id"]],$gl["cast"]);// genre pt
					
					$dg[$value2["tmdb_id"]]=real_pt($value2,$dg[$value2["tmdb_id"]],$gl["reals"]); //real pt
				}

			}
		}

	}
	// print_r($dg);
	asort($dg); //tri
	return $dg;
}

// extrait les nb premiers films
function get_array_nb($arr,$nb){
	$d=array();
	$arr=array_reverse($arr, true);
	$count=0;
	foreach ($arr as $key => $value) {
		if ($count == $nb) return $d;
		$d[$key]=$value;
		$count++;
	}
	return $d;
}

//revoit une liste de films suggeré (id,rate,title,href,reals,genres,video_url) avec une liste de films suggerés (id=>note)
function date_m($d){
	global $type;
	$num=$GLOBALS["TMDB"]->info($type,$d);

	return $num->release_date;
}
function vide($d){

	return ($d == null || !(isset($d)));
}
function p_vide($d){

	return !(vide($d));
}
function movie_pref($e){ 

	global $type; //movie
	global $nb; //nb de films suggéré max !
	global $min_u; //note minimal

// print_r($e);
	$movie=pref($e); // retourne une liste de films suggerés (id=>note)
	$res=array(); //array -> resultats
	$movie = get_array_nb($movie, $nb); // extrait les nb premiers films
	$movie = array_filter($movie, function($k,$v) { // filtres les films ayant une note > $min
		global $min_u;
	    return $k >= $min_u;
	}, ARRAY_FILTER_USE_BOTH); 

	$movie=array_reverse($movie, true); //inverse la liste 

	foreach ($movie as $key => $value) { //parcours la liste de films suggerés (id=>note) et retourne (id,rate,title,href,reals,genres,video_url)
$sd =array();
		$tmdb=getAll("SELECT `titre` as 'title',`product_id` as 'p_id',`affiche` as 'href' FROM products WHERE tmdb_id =".$key." ");	

// print_r("\n".count($tmdb)."\n");
		// si le film n'existe pas on cherche les infos sur tmdb sinon depuis la bd
		if (count($tmdb)==0){
			$val=$key;
			$tb="`products`";
			$v="tmdb_id=".$val;

			// print_r("\n".$GLOBALS["TMDB"]."\n");
			$num=$GLOBALS["TMDB"]->info($type,$val); //on cherche le film
// print_r($num);
			if (p_vide($num->title)) {
				# code...
			
			$last=eraklion($tb,$v,"tmdb_id",$val,$num); //on eraklion le nouveau film

// // 
			$sd["id"]=$key;
						$sd["tmdb_id"]=$key;

			$sd["p_id"]=$last;
			$sd["title"]=$num->title; 
						$sd["ann"]=$num->release_date; 

			if ($num->poster_path==null || !(isset($num->poster_path))  ) {
				$ns="fail";
			}else{
				$ns=$num->poster_path;
			}
			$sd["href"]=$ns;
			$sd["reals"]=reals_name($key); // on cherche les reals
			$sd["genres"]=genres_name($key); // on cherche les genres
			$sd["video_url"]=video_url($key); // on cherche le lien de la video
			$sd["rate"]=$value;
						// print_r($sd);

}else{
	continue;
}
			

		}else{
						// print_r("expression2");

			$sd=$tmdb;
			unset($sd[0]);
			$sd["tmdb_id"]=$key;
			$sd["title"]=$tmdb[0]["title"];
			$sd["p_id"]=$tmdb[0]["p_id"];
			$sd["href"]=$tmdb[0]["href"];
			$sd["rate"]=$value;
			$sd["ann"]=date_m($key); 

			$sd["reals"]=reals_name($key);
			$sd["genres"]=genres_name($key);
			$sd["video_url"]=video_url($key);
			// print_r($sd);
		}

		$res[]=$sd;

	}
	$res=up_href($res); //rewrite url img 
	return $res;
}
