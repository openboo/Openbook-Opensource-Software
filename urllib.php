<?php
	/** URL Library **/
	/* Convert a string so that all urls are turned into pre-formatted links.*/
	function url_links($string){
		$url = '/(^| )((https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/i';
		$string = preg_replace($url, '$1<a href="$2" target="_blank" title="$2">$2</a>', $string);
		return $string;
	}
?>
