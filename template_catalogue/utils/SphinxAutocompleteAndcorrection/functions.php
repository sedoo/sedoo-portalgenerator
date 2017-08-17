<?php

function BuildTrigrams($keyword) 
{
	$t = "__" . $keyword . "__";
	$trigrams = "";
	for ($i = 0; $i < strlen($t) - 2; $i++)
		$trigrams .= mb_substr($t, $i, 3 ,'UTF-8'). " ";
	return mb_convert_encoding($trigrams, "UTF-8", mb_detect_encoding($trigrams));
}

function MakeSuggestion($keyword,$ln) 
{
	$trigrams = BuildTrigrams($keyword);
	$query = "\"$trigrams\"/1";
	$len = strlen($keyword);

	$delta = LENGTH_THRESHOLD;
	$weight = 'weight()';
	if(SPHINX_20 == true) {
	    $weight ='@weight';
	}
	$stmt = $ln->prepare("SELECT *, $weight as w, w+:delta-ABS(len-:len) as myrank FROM suggest_#MainProject WHERE MATCH(:match) AND len BETWEEN :lowlen AND :highlen
			ORDER BY myrank DESC, freq DESC
			LIMIT 0,:topcount OPTION ranker=wordcount");

	$stmt->bindValue(':match', mb_convert_encoding(str_ireplace("'","''",$query), "UTF-8", mb_detect_encoding($query)), PDO::PARAM_STR);
	$stmt->bindValue(':len', $len, PDO::PARAM_INT);
	$stmt->bindValue(':delta', $delta, PDO::PARAM_INT);
	$stmt->bindValue(':lowlen', $len - $delta, PDO::PARAM_INT);
	$stmt->bindValue(':highlen', $len + $delta, PDO::PARAM_INT);
	$stmt->bindValue(':topcount',TOP_COUNT, PDO::PARAM_INT);
	$stmt->execute();


	if (!$rows = $stmt->fetchAll())
		return false;
	// further restrict trigram matches with a sane Levenshtein distance limit
	foreach ($rows as $match) {
		$suggested = mb_convert_encoding($match["keyword"], "UTF-8", mb_detect_encoding($match["keyword"]));
		if (levenshtein(mb_convert_encoding($keyword, "UTF-8", mb_detect_encoding($keyword)), $suggested) <= LEVENSHTEIN_THRESHOLD)
			return $suggested;
	}

	return $keyword;
}

function MakePhaseSuggestion($words,$query,$ln_sph) 
{
	$suggested = array();
	$llimf = 0;
	$i = 0;
	foreach ($words  as $key => $word) {
		if ($word['docs'] != 0)
			$llimf +=$word['docs'];$i++;
	}
	$llimf = $llimf / ($i * $i);
	foreach ($words  as $key => $word) {
		if ($word['docs'] == 0 || $word['docs'] < $llimf) {
			$mis[] = $word['keyword'];
		}
	}
	
	if (count($mis) > 0) {
		foreach ($mis as $m) {
			$re = MakeSuggestion($m, $ln_sph);
			if ($re) {
				if($m!=$re){
					$suggested[$m] = $re;
					$wrd = $m;
				}
			}
		}
		if(count($words) ==1 && empty($suggested)) {
			return false;
		}
		$phrase = explode ( ' ', $query );
		foreach ( $phrase as $k => $word ) {
			if (isset ( $suggested [strtolower ( $wrd )] )){
				$phrase [$k] = $suggested [strtolower ( $wrd )];
			}
		}
		$phrase = implode ( ' ', $phrase );
		return $phrase;
	} else {
		return false;
	}
}

?>
