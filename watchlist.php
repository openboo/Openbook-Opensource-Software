<?php
	/** Set up Watch List **/
	/* The watch list keeps track of which posts we posted or commented on, and the last time we viewed them.
	 * This list is stored in our cookies, as pairings of a post id, and the timestamp we last viewed that post.
	 * This list is then used to alert the user if there are any new comments on any posts in their watch list.
	 * It does this by comparing the last time you viewed a post with the timestamp on it's most recent comment. */
	//This is the variable used to interact with the watch list, representing the watch list in our cookies.
	$watching = array();
	//Check if the watch list cookie is set.
	if(isset($_COOKIE['watching'])){
		//If so, set our watching variable to match the actual watch list in the cookies.
		$cookie = stripslashes($_COOKIE['watching']);
		//update watching var
		$watching = json_decode($cookie,true);//decode cookie
	}
?>
