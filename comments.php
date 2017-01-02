<?php include("header.php");?>
<?php include("urllib.php");?>
<?php include("hashtaglib.php");?>
<?php include("databasesetup.php");//this gives us $conn to connect to mysql.?>
<?php include("watchlist.php");//set up watch list?>
<?php
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


		/** Update Watch List, since you're here viewing it. **/
		if(isset($watching[$postid])){
			if($watching[$postid]<time()){
				$watching[$postid]=time();
			}
			setcookie("watching",json_encode($watching),time()+(60*60));//reexamine
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
						<!-- Nav Bar - Comments -->
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
							<img src="anons/anon<?php echo(rand(1,$settings['numimages']).".".$settings['imagetype']);?>" />
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

		echo hashtag_links(url_links(stripslashes($post)));

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
	  
	<?php

	//Select all relevant comments
	$query = "SELECT id,post,timestamp FROM posts WHERE parent='{$postid}' ORDER BY timestamp ASC;";
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
						<img src="anons/anon<?php echo(rand(1,$settings['numimages']).".".$settings['imagetype']);?>" />
						Anonymous
					</div>
  
					<!-- Comment Content -->
					<div class="col-sm-10 col-xs-9">
						<span class="colored"><?php echo $minutesago;?></span>
						<br/>
						<span>
							<?php echo hashtag_links(url_links(stripslashes($post))); ?>
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

		</div><!-- /container -->

	</body>

</html>
