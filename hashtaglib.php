<?php
	/** Hashtags Library **/

	/* Get a printable array of hashtags from a string. */
	function get_hashtags($string, $str = 1) {
		preg_match_all('/(^|\s)#(\w*[a-zA-Z_]+\w*)/',$string,$matches);
		$i = 0;
		$keywords = [];
		if($str){
			foreach ($matches[2] as $match){
				$count = count($matches[2]);
				$keywords .= "$match";
				$i++;
				if ($count > $i) $keywords .= ", ";
			}
		}else{
			foreach ($matches[2] as $match){
				$keyword[] = $match;
				$keywords = $keyword;
			}
		}
		return $keywords;
	}

	/* Convert a string so that all hashtags are turned into pre-formatted links (as defined in the function above).*/
	function hashtag_links($string) {
		return preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', '\1<a href="search.php?search=%23\2">#\2</a>', $string);
		//Returns the newly formatted link-filled string, as ordered.
	}
?>
