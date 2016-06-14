<!DOCTYPE html>
<html lang="en">

	<head>
		<!--You've uncovered another stop on the underground railroad!-->
		<!--Perhaps you're looking for: p0rtalurl/IMPORTANT.txt -->

		<!-- Metadata -->
		<meta charset="utf-8">
		<title>openbook</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="An Anonymous hive mind social media network website. One p0rtal among #thep0rtals.">
		<meta name="author" content="Anonymous">

		<!-- Styles for Bootstrap -->
		<link href="css/bootstrap.css" rel="stylesheet">

		<!-- Extra Stylin' -->
		<link href="css/style.css" rel="stylesheet">

		<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
			<script src="../assets/js/html5shiv.js"></script>
		<![endif]-->

		<!-- Simple Ajax Library (SAL) -->
		<script language="javascript" src="js/ajax.js"></script>

	</head>

	<body>

<?php

	/** Hashtags Library **/

	/* Get a printable array of hashtags from a string. */
	function get_hashtags($string, $str = 1) {
		preg_match_all('/#(\w+)/',$string,$matches);
		$i = 0;
		$keywords = [];
		if($str){
			foreach ($matches[1] as $match){
				$count = count($matches[1]);
				$keywords .= "$match";
				$i++;
				if ($count > $i) $keywords .= ", ";
			}
		}else{
			foreach ($matches[1] as $match){
				$keyword[] = $match;
				$keywords = $keyword;
			}
		}
		return $keywords;
	}

	/* Convert a string so that all hashtags are turned into pre-formatted links (as defined in the function above).*/
	function hashtag_links($string) {
		//Find all matches to character strings that start with '#'.
		preg_match_all('/#(\w+)/',$string,$matches);
		//Convert each one into a pre-formatted link by surrounding it with the <a> tags.
		foreach ($matches[1] as $match) {
			//The way we use it here is just to link to the search page, where the search is for the hashtag.
			$string = str_replace("#$match", "<a href='search.php?search=%23$match'>#$match</a>", "$string");
		}
		return $string;//Returns the newly formatted link-filled string, as ordered.
	}



	/** Set up Database **/
	//connect or die with error message.
	$conn = mysqli_connect("serveraddress","SQLuser","databasepassword","databasename");
	if (!$conn) {
		die("Connection failed: " . mysqli_connect_error());
	}



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



	/** Update votes **/ //only in index.php
	if(isset($_GET['id']) && isset($_GET['vote'])){//Was the vote up or down? What id was the vote for? Prerequisites.
		//get vars
		$vote = htmlentities($_GET['vote']);
		$id = htmlentities($_GET['id']);
		//check id exists in votes table (every existing post has a corresponding row in the votes table to remember vote score).
		$query = "SELECT ups,downs FROM votes WHERE id='".mysqli_real_escape_string($conn,$id)."';";
		$votes = mysqli_query($conn,$query);
		//if so...
		if($votes){
			//format votes from database
			$row = mysqli_fetch_array($votes);
			$ups = (int)(mysqli_real_escape_string($conn,$row['ups']));
			$downs = (int)(mysqli_real_escape_string($conn,$row['downs']));
			//increase up or down votes in database depending which button was clicked
			if($vote=="up"){
				$ups += 1;
				$score = ($ups-$downs);
				$query = "UPDATE votes SET ups='{$ups}',score='{$score}' WHERE id='{$id}';";
				mysqli_query($conn,$query);
				mysqli_commit($conn);
				$query = "UPDATE posts SET score='{$score}' WHERE id='{$id}';";
				mysqli_query($conn,$query);
				mysqli_commit($conn);
			}else if($vote=="down"){
				$downs += 1;
				$score = ($ups-$downs);
				$query = "UPDATE votes SET downs='{$downs}',score='{$score}' WHERE id='{$id}';";
				mysqli_query($conn,$query);
				mysqli_commit($conn);
				$query = "UPDATE posts SET score='{$score}' WHERE id='{$id}';";
				mysqli_query($conn,$query);
				mysqli_commit($conn);
			}
		}
	}


	/** Bumps the post to be the most recent status update, as if just reposted, but with previous comments still attached.
	 * It does this by simply resetting the timestamp to "now" on the main post.
	 * Then, it resets the timestamps of all child posts, so the comments look like they were posted "right after" their parent.
	 * They are separated by one second intervals to keep them in order, and to make them look slightly less automated.
	 * 
	 * (To make it more like 'repost', remove this bit of code and switch the hidden element that's coupled with the share button
	 * to 'name=status' and 'value={$post} rather than 'name=share' and 'value={$id}') */
	if(isset($_POST['share'])){
		//get parent post id
		$shareid = $_POST['share'];	

		//get an array of child post ids (comment ids)
		$query = "SELECT id FROM posts WHERE parent='{$shareid}' ORDER BY timestamp ASC;";
		$children = mysqli_query($conn,$query);

		//set the new time to "now", except also subtract a second for each child post, to maintain chronological order.
		//  (As it turns out, posting a few seconds back in time is less glitchy than posting a few seconds into the future.)
		$newtime = time()-count($children)-1;
		
		//update database
		$query = "UPDATE posts SET timestamp='{$newtime}' WHERE id='{$shareid}';";
		mysqli_query($conn,$query);
		mysqli_commit($conn);

		//set each child post's timestamp to their new timestamp.
		//  (Essentially they're all posted "now" except each one is incremented by one second to maintain chronological order.)
		while($row = mysqli_fetch_array($children)){
			$child_id = $row['id'];
			$newtime++;//increment each comment's timestamp by one second to maintain original chronological order.
			$query = "UPDATE posts SET timestamp='{$newtime}' WHERE id='{$child_id}';";
			mysqli_query($conn,$query);
			mysqli_commit($conn);
		}
		//Update watch list to match the comment with the latest timestamp.
		$watching[$shareid]=$newtime;//
		setcookie("watching",json_encode($watching),time()+(60*60));//reexamine
	}



	/** Insert status **/
	$status = "We are ";//default value for the form element
	//check if status is posted...
	if(isset($_POST['status'])){
		//get the status update that was entered.
		$status = htmlentities($_POST["status"]);//receive post
		//character limit
		if(strlen($status)<=9000){//character limit says it can't go over 9000!!!1

			/* Display images linked to in a post. */
			//Use REGEX to turn square brackets into image tags
			$finalstatus = preg_replace("/\[(.*?)\]/","<br/><img style='max-width:100%' src=\"$1\" />", $status);
			$finalstatus .= "<br/><br/>";//this helps hashtags getting appended not look as weird.

			/* Prevent doubles */
			// Get the status entered just before. Use it to check that this isn't an accidental exact duplicate.
			$query = "SELECT post FROM posts WHERE parent=0 ORDER BY id DESC LIMIT 1;";//Select just one.
			$results = mysqli_query($conn,$query);
			$row = mysqli_fetch_assoc($results);
			$lastpost = $row['post'];
			//Prevents doubles AND blank, or relatively 'blank', status updates.
			if($lastpost!=$finalstatus && trim($finalstatus)!="We are" && trim($finalstatus)!="We" && trim($finalstatus)!=""){

				/* INSERT status update */
				$query = "INSERT INTO posts (post,timestamp) VALUES ('".mysqli_real_escape_string($conn,$finalstatus)."',".time().");";
				mysqli_query($conn,$query);
				$lastid = mysqli_insert_id($conn);//Get post id of inserted post for later use (the watch list, etc...)
				mysqli_commit($conn);
				//Initialize corresponding slot in 'votes', setting up the foundation to keep track of future up and down votes.
				$query = "INSERT INTO votes (id,ups,downs,score) VALUES ({$lastid},0,0,0);";
				mysqli_query($conn,$query);
				mysqli_commit($conn);

				/* Add to Watch List */
				//Remember in your cookies.
				$watching[$lastid]=""+time()+"";//add to array
				setcookie("watching",json_encode($watching),time()+(60*60));//reexamine  

				/* Keep count of hashtags */
				$tags  = get_hashtags($status, $str=0);
				$num_tags = count($tags);
				for($i=0;$i<$num_tags;$i++){
					$tag = $tags[$i];
					//check if that hashtag exists
					$query = "SELECT * FROM hashtags WHERE hashtag='{$tag}';";
					$results = mysqli_query($conn,$query);
					$row = mysqli_fetch_assoc($results);
					$uses = $row['uses'];
					//if not...
					if(count($uses)<1){
						//insert the new hashtag with the default value of "1" uses.
						$query = "INSERT INTO hashtags (hashtag,uses) VALUES ('".mysqli_real_escape_string($conn,$tag)."',1);";
						mysqli_query($conn,$query);
						mysqli_commit($conn);
					}else{
						//if the hashtag already exists, just increment how many times it's been used.s
						$new_uses = $uses + 1;
						$query = "UPDATE hashtags SET uses={$new_uses} WHERE hashtag='{$tag}';";
						mysqli_query($conn,$query);
						mysqli_commit($conn);
					}
				}
			}
		}else{


/** TL;DR Alert **/
?>
		<div id="alert">
			<a href="?off=bigbrotheralert" style="float: right;"><img id="closebutton" style="border: 0px;" src="closebutton.png"></a>
			Browser activity and IP addresses are being logged by government surveillance.
			<br/>
			Anonymity is won only by understanding and using multiple tools like <a href="http://lifehacker.com/what-is-tor-and-should-i-use-it-1527891029">Tor-Browser</a>.
		</div>
<?php

		}
	}

	/** Big Brother Alert **/
	$off = "";
	if(isset($_GET['off'])){
		$off = $_GET['off'];
		if($off=="bigbrotheralert"){
			setcookie("bigbrotheralert","off");
		}
	}elseif(!isset($_COOKIE['bigbrotheralert'])){


?>
		<div id="alert">
			<a href="?off=bigbrotheralert" style="float: right;"><img id="closebutton" style="border: 0px;" src="closebutton.png"></a>
			Browser activity and IP addresses are being logged by government surveillance.
			<br/>
			Anonymity is won only by understanding and using multiple tools like <a href="http://lifehacker.com/what-is-tor-and-should-i-use-it-1527891029">Tor-Browser</a>.
		</div>
<?php

	}

?>
		<!-- Page Container -->
		<div class="container-narrow">
  
			<!-- Header -->
			<div class="container-fluid">
				<div class="row-fluid">
	  
					<!-- Title Bar -->
					<div id="header">
						<a href="index.php">
							<img style="width: 80%" src="openbook.png" />
						</a>
						<p id="slogan">
							"Man is least himself when he talks in his own person.<br/>
							Give him a mask, and he will tell you the truth."
						</p>
						<div style="clear:both;"></div>
					</div>
	  
					<!-- Nav Bar -->
					<ul id="navbar" class="nav nav-pills pull-left">
						<li class="active"><a href="index.php">Newsfeed</a></li>
						<li><a href="search.php">Search</a></li>
						<li><a href="about.php">About</a></li>
<?php

	/** Updates mail icon to alert user of how many posts in the users 'watch list' have had comments added. **/
	if(count($watching)>0){
		$updates = 0;
		//check for updates for each postid,timestamp pair in $watching
		foreach($watching as $postidkey=>$lastactivitytimestamp){
			//DEBUG: echo "{$postidkey}:{$lastactivitytimestamp};";
			$query = "SELECT timestamp FROM posts WHERE parent='{$postidkey}' ORDER BY timestamp DESC LIMIT 1;";
			$result = mysqli_query($conn,$query);
			$row = mysqli_fetch_array($result);
			//get timestamp for most recent comment
			$latesttimestamp = $row[0];
			//compare it with the last time we viewed or interacted with the page.
			if($lastactivitytimestamp<$latesttimestamp){//if theres newer comments since your last comment
				$updates+=1;
			}
		}
		//if there are updates, show the mail icon, and how many updates there are.
		if($updates>0){

?>
						<li>
							<a href="updates.php"><span style="display: inline;"><img src="mailicon.png" /></span>&nbsp;&nbsp;<?php echo $updates; ?></a>
						</li>
<?php

		}
	}

?>
					</ul>

					<div style="clear:both;"></div>
				</div>
			</div>
  
			<!-- Status Update Form -->
			<div class="container-fluid">
				<div class="row-fluid">
					<div class="formbox">
						Status update:
		
						<form id="statusform" action="index.php" method="post">
							<input id="status" onkeyup="alert(collectivize());" name="status" class="formtext" type="text" value="<?php echo stripslashes($status); ?>"></input>
							<input id="updatebutton" class="formbutton" type="submit" value="Update"></input>
						</form>
	  
					</div>
				</div>
			</div>
	  
			<!-- Filter Status Updates -->
			<div class="container-fluid">
				<div class="row-fluid">
					<div class="filter">
						Sort by:
<?php

	//get user's 'sort by' setting - defaults to 'recent'
	$sortby = "";
	if(isset($_GET['sort'])){
		$sortby=$_GET['sort'];
	}

?>
						<a <?php if($sortby=='recent' || $sortby==''){echo "class='selected'";} ?> href="index.php?sort=recent">recent</a>
						<a <?php if($sortby=='popular'){echo "class='selected'";} ?> href="index.php?sort=popular">trending</a>
						<a <?php if($sortby=='random'){echo "class='selected'";} ?> href="index.php?sort=random">random</a>
					</div>
				</div>
			</div>

<?php
	$lastid=0;
	
    if(isset($_GET['id'])){
		$lastid = mysqli_real_escape_string($conn,$_GET['id']);
	}
	//set up query for which posts to grab and in what order based on the user's 'sort by' selection.
	if($sortby=="popular"){
		$query = "SELECT id,post,timestamp FROM posts WHERE parent=0 ORDER BY score DESC LIMIT ";//open-ended
		//this makes it so it works with or without js
		if($lastid>0){
			$sofar = $lastid;
			$until = 10;
			$query .= "{$sofar},{$until};";
		}else{
			//they have js
			$query .= "10;";//default
		}
	}elseif($sortby=="random"){
		//Search for 'ORDER BY random' or equivalent
		$query = "SELECT id,post,timestamp FROM posts WHERE parent=0 ORDER BY RAND() LIMIT 10;";
	}else{
		//this makes it so it works with or without js
		$forNONjs="";
		if($lastid>0){
			if(!isset($_GET['vote'])){
				$forNONjs = "AND timestamp<$lastid ";
			}
		}else{
			//they have js
			$forNONjs = "";
		}
		//Sort by Recent. timestamp desc
		$query = "SELECT id,post,timestamp FROM posts WHERE parent=0 ".$forNONjs."ORDER BY timestamp DESC LIMIT 10;";
		//For 'recent': 'lastid' actually represents 'lasttimestamp'
	}
	$results = mysqli_query($conn,$query);
	//set vars to capture the stats of the 10th post, when it comes around.
	$lastid = 0;
	$lasttimestamp = 0;
	//print all 10 posts, one at a time.
	while($row = mysqli_fetch_array($results)){
		$id = $row['id'];
		$post = $row['post'];
		$timestamp = $row['timestamp'];
		//include how many votes.
		$query = "SELECT ups,downs FROM votes WHERE id={$id};";
		$votes = mysqli_query($conn,$query);
		$votes = mysqli_fetch_array($votes);
		$score = ((int)($votes['ups']))-((int)($votes['downs']));

?>

			<!-- Status Update -->
			<div class="container-fluid">
				<div class="row-fluid post">
					
					<!-- User Identity -->
					<div class="col-sm-2 col-xs-3" style="1px solid black; margin: 0px; padding: 0px;">
						<img src="anons/anon<?php echo(rand(1,47)); ?>.jpg" />
						Anonymous
					</div>
		  
					<!-- Status Update Content -->
					<div class="col-sm-10 col-xs-9">
						<span class="colored">

<?php
		//calculate how many minutes ago.
		$minutesago = ceil((time()-((int)($timestamp)))/60);
		if(($minutesago/(60*24*7*4))>1){
			echo ceil($minutesago/(60*24*7*4))." months ago";
		}elseif(($minutesago/(60*24*7))>1){
			echo ceil($minutesago/(60*24*7))." weeks ago";
		}elseif(($minutesago/(60*24))>1){
			echo ceil($minutesago/(60*24))." days ago";
		}elseif(($minutesago/60)>1){
			echo ceil($minutesago/60)." hours ago";
		}elseif($minutesago>0){
			echo $minutesago." minutes ago";
		}else{
			echo "just now";
		}
?>
						</span>
						<br/>
						<span class="content">

<?php

		/** The 'We said' Clause**
		 * If a post doesn't start with "We" it is fixed to start with "We said:"
		 * This maintains the spirit of singularity, or unity, and doubles as a decent platform for "sharing" comments, who don't have to start with "We". */
		 //Possible glitch: Comments that start with "we", when shared, will not be prefaced with "We said", and will just look like regular posts. Whatevs. Let's say it's on purpose.
		if(substr($post,0,3)=="We "){
			echo hashtag_links(stripslashes($post));
		}else{
			echo "<span style='font-size: 17px;'>We said:</span><br/>";
			echo "<div class='quotedtext'>";
			echo hashtag_links(stripslashes($post));
			echo "</div>";
		}
		//Since the "We said" clause is after everything, the tags added in will get inside the quote. Whatevs?

?>
						</span>
						<br/>
						<a href="index.php?id=<?php echo $id;?>&vote=up" class="colored"><img style="border:0px" src="uparrow.png" /></a>
						<a href="index.php?id=<?php echo $id;?>&vote=down" class="colored"><img style="border:0px" src="downarrow.png" /></a>
						<span class="colored">
<?php

		//Add the "+" in front if it's positive.
		//If it's negative it already automatically has the "-" in front.
		if($score>-1){
			echo "+";
		}
		echo $score;

?>

						</span>

<?php

		//Get all the comments and the number of comments.
		$query = "SELECT post FROM posts WHERE parent={$id}";
		$comments = mysqli_query($conn,$query);
		$numcomments = mysqli_num_rows($comments);
		$commenttext = ($numcomments>0)?"comments(".$numcomments.")":"comment";

		$post = stripslashes($post);

?>

						<a href="comments.php?id=<?php echo $id;?>" class="colored"><?php echo $commenttext; ?></a>
						<form style="display: inline;" action="index.php" method="post">
							<input type="hidden" name="share" value="<?php echo $id;?>" />
							<input class="colored buttonlink" type="submit" value="share" />
						</form>
					</div>
					<div style="clear: both;"></div>      
				</div>
			</div>

			<!-- Then comes the bottom button to display more. -->

<?php
		//gets id every iteration - exits loop as the lastid
		$lastid = $id;//bookmarks where you are in list of posts
		$lasttimestamp = $timestamp;//gets last timestamp
	}//</while loop>

	if($sortby=="random"){

?>

			<div id="load<?php echo $lastid;?>">
		   
				<!-- Status Update -->
				<div class="container-fluid">
					<div class="row-fluid">
						<a id="loadmorebutton" onclick="SetInnerHTMLFromAjaxResponse('tenmore.php?id=<?php echo $lastid;?>&sort=random','load<?php echo $lastid;?>')" href="?sort=random">
						<div style="font-weight: bold;" class="row-fluid text-center post">
							load ten more...
							<div style="clear: both;"></div>
						</div>
						</a>
					</div>
				</div>
			</div><!-- /load -->

			<!-- This makes it so the site works whether or not javascript is enabled -->
			<script>
				document.getElementById("loadmorebutton").href="#load<?php echo $lastid;?>";
			</script>

<?php

	}elseif($sortby=="popular"){
		//For 'popular': 'lastid' actually represents how many posts have been displayed so far
		$lastid = 0;
		if(isset($_GET['id'])){
			$lastid = htmlentities($_GET['id']);
		}
		$lastid+=10;
?>

			<div id="load10">
		   
				<!-- Status Update -->
				<div class="container-fluid">
					<div class="row-fluid">
						<a id="loadmorebutton" onclick="SetInnerHTMLFromAjaxResponse('tenmore.php?id=10&sort=popular','load10')" href="?id=<?php echo $lastid;?>&sort=popular">
							<div style="font-weight: bold;" class="row-fluid text-center post">
								load ten more...
								<div id="seewhathappens" style="clear: both;"></div>
							</div>
						</a>
					</div>
				</div>
			</div><!-- /load -->

			<!-- This makes it so the site works whether or not javascript is enabled -->
			<script>
				document.getElementById("loadmorebutton").href="#load10";
			</script>
<?php

	}else{
		/* Next pass $lastid to tenmore.php to get a list of ten more posts to display */

?>

			<div id="load<?php echo $lasttimestamp;?>">

				<!-- Status Update -->
				<div class="container-fluid">
					<div class="row-fluid">
						<a id="loadmorebutton" onclick="SetInnerHTMLFromAjaxResponse('tenmore.php?id=<?php echo $lasttimestamp;?>','load<?php echo $lasttimestamp;?>')" href="?id=<?php echo $lasttimestamp;?>">
						<div style="font-weight: bold;" class="row-fluid text-center post">
							load ten more...
							<div id="seewhathappens" style="clear: both;"></div>
						</div>
						</a>
					</div>
				</div>
			</div><!-- /load -->
			
			<!-- This makes it so the site works whether or not javascript is enabled -->
			<script>
				document.getElementById("loadmorebutton").href="#load<?php echo $lasttimestamp;?>";
			</script>

<?php

	}

?>

		</div><!-- /container -->

		<script>
			document.getElementById("status").onkeyup = function() {collectivize()};
			function collectivize() {
				var statusBox = document.getElementById("status");
				if(statusBox.value.length<3){
					statusBox.value = statusBox.value = "We ";
				}
			}
		</script>

	</body>

</html>
