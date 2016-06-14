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

		<!-- The first blocks of code to set up a website are always so boring... -->

	</head>

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



	/* Show the rest of the page only if $_GET['id'] is set, otherwise the page was reached improperly. */
	if(isset($_GET['id'])){



		/** Get post id **/
		//to update "watch" list right away, since you're "viewing" it right now.
		$postid = "";
		if(isset($_GET['id'])){
			$postid = htmlentities($_GET['id']);
		}



		/** Select main post details. **/
		$query = "SELECT id,post,timestamp FROM posts WHERE parent=0 AND id='".mysqli_real_escape_string($conn,$postid)."';";
		$post = mysqli_query($conn,$query);
		$timestamp = 0;//for later
		$row = mysqli_fetch_array($post);
		if($row){
			//get main post
			$post = mysqli_real_escape_string($conn,$row['post']);
			//get main post timestamp
			$timestamp = $row['timestamp'];
		}else{
			$post = "Error";
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
			//update watch list since you're here "viewing" it right now.
			if(isset($watching[$postid])){
				if($watching[$postid]<time()){
					$watching[$postid]=time();
				}
				setcookie("watching",json_encode($watching),time()+(60*60));//reexamine
			}
		}



		/** Submit new comment **/ //(includes adding to the main post any hashtags in the comment).
		if(isset($_POST['comment'])){
			$comment = mysqli_real_escape_string($conn,htmlentities($_POST['comment']));
			$currtimestamp = time();
			$query="INSERT INTO posts (parent,post,timestamp) VALUES ({$postid},'{$comment}',{$currtimestamp});";
			mysqli_query($conn,$query);
			mysqli_commit($conn);

			//set cookie to remember this comment id and post time.
			$watching[$postid]=$currtimestamp;
			setcookie("watching",json_encode($watching),time()+(60*60));
			
			/* Keep count of hashtags */
			$tags  = get_hashtags($comment, $str=0);
			$num_tags = count($tags);
			$tagstring = "";//specific for comments page to append tags in comments to the main post. (So everyone helps categorize.)
			for($i=0;$i<$num_tags;$i++){
				$tag = $tags[$i];
				//append each tag to tagstring - to later append tagstring to parent post.
				$tagstring .="#".$tag." ";
				$query = "SELECT * FROM hashtags WHERE hashtag='{$tag}';";
				$results = mysqli_query($conn,$query);
				$row = mysqli_fetch_assoc($results);
				$uses = $row['uses'];
				if(count($uses)<1){
					$query = "INSERT INTO hashtags (hashtag,uses) VALUES ('".mysqli_real_escape_string($conn,$tag)."',1);";
					mysqli_query($conn,$query);
					mysqli_commit($conn);
				}else{
					$new_uses = $uses + 1;
					$query = "UPDATE hashtags SET uses={$new_uses} WHERE hashtag='{$tag}';";
					mysqli_query($conn,$query);
					mysqli_commit($conn);
				}
			}
			/* Append any hashtags from comment to main post */
			//This lets anyone tag any post with any hashtag.
			//Since only main posts are searcheable, this allows everyone to help make the main posts easier to find.
			$query = "UPDATE posts SET post=\"{$post} {$tagstring}\" WHERE id=\"{$postid}\";";
			mysqli_query($conn,$query);
			mysqli_commit($conn);
		}

?>
	<body>
<?php
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
							<li><a href="index.php">Newsfeed</a></li>
							<li><a href="search.php">Search</a></li>
							<li><a href="about.php">About</a></li>
							<li class="active"><a href="?id=<?php echo $postid; ?>">Post <?php echo $postid; ?></a></li>
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
		//Format timestamps
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

		echo hashtag_links(stripslashes($post));

	?>
							</span>
							<br/>
							<a href="index.php?id=<?php echo $postid;?>&vote=up" class="colored"><img style="border:0px" src="uparrow.png"></span></a>
							<a href="index.php?id=<?php echo $postid;?>&vote=down" class="colored"><img style="border:0px" src="downarrow.png"></span></a>
	<?php

		//Calculate vote score
		$query = "SELECT ups,downs FROM votes WHERE id={$postid};";
		$votes = mysqli_query($conn,$query);
		$votes = mysqli_fetch_array($votes);
		$score = ((int)($votes['ups']))-((int)($votes['downs']));

	?>
							<span class="colored">
	<?php

		//Format vote score
		if($score>-1){
			echo "+";
		}
		echo $score;

	?>
							</span>
							<form style="display: inline;" action="index.php" method="post">
								<input type="hidden" name="share" value="<?php echo $postid;?>" />
								<input class="colored buttonlink" type="submit" value="share" />
							</form>
						</div>
						<div style="clear: both;"></div>
					</div>
				</div>
	  
				<!-- Comment Form -->
				<div class="container-fluid">
					<div class="row-fluid">
						<div id="replybox" class="formbox">
		
							<form id="commentform" method="post" action="?id=<?php echo $postid;?>">
								<textarea id="comment" name="comment" class="formtext" type="text" value="We are "></textarea>
								<br/>
								<input id="replybutton" class="formbutton pull-right" type="submit" value="Reply"></input>
							</form>
	  
							<div style="clear: both;"></div>
  
						</div>
					</div>
				</div>

	<?php

	//Select all relevant comments
	$query = "SELECT id,post,timestamp FROM posts WHERE parent='{$postid}' ORDER BY timestamp DESC;";
	$posts = mysqli_query($conn,$query);
	$latestid = 0;//store latest comment id for later use in cookies checking for updates on commented posts
	while($row = mysqli_fetch_array($posts)){
		$commentid = $row['id'];
		$post = $row['post'];
		$timestamp = $row['timestamp'];
      
		if($latestid==0){
			$latestid = $commentid;
		}
      
		//Format timestamps
		$minutesago = ceil((time()-((int)($timestamp)))/60);
		if(($minutesago/(60*24*7*4))>1){
			$minutesago = ceil($minutesago/(60*24*7*4))." months ago";
		}elseif(($minutesago/(60*24*7))>1){
			$minutesago = ceil($minutesago/(60*24*7))." weeks ago";
		}elseif(($minutesago/(60*24))>1){
			$minutesago = ceil($minutesago/(60*24))." days ago";
		}elseif(($minutesago/60)>1){
			$minutesago = ceil($minutesago/60)." hours ago";
		}elseif($minutesago>0){
			$minutesago = $minutesago." minutes ago";
		}else{
			$minutesago = "just now";
		}

	?>
				<!-- Comment -->
				<div class="container-fluid">
					<div class="row-fluid post">
            
					<!-- User Identity -->
					<div class="col-sm-2 col-xs-3" style="1px solid black; margin: 0px; padding: 0px;">
						<img src="anons/anon<?php echo(rand(1,47)); ?>.jpg" />
						Anonymous
					</div>
  
					<!-- Comment Content -->
					<div class="col-sm-10 col-xs-9">
						<span class="colored"><?php echo $minutesago;?></span>
						<br/>
						<span>
							<?php echo hashtag_links(stripslashes($post)); ?>
						</span>
						<br/>
						<form style="display: inline;" action="index.php" method="post">
							<input type="hidden" name="status" value="<?php echo stripslashes($post);?>" />
							<input class="colored buttonlink" type="submit" value="share" />
						</form>
					</div>
					<div style="clear: both;"></div>
				</div>

			</div>
	<?php

		}// </while>
	}else{// </if $id>
		echo "Error: ?id= must be set.";
	}



	?>
		</div><!-- /container -->

	</body>

</html>
