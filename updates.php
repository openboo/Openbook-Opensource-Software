<?php include("header.php");?>
<?php include("urllib.php")?>
<?php include("hashtaglib.php");?>
<?php include("databasesetup.php");//this gives us $conn to connect to mysql.?>
<?php include("watchlist.php");?>
	<div class="container-narrow">
  
		<!-- Header -->
		<div class="container-fluid">
			<div class="row-fluid">
  
				<!-- Nav Bar -->
				<ul id="navbar" class="nav nav-pills pull-left">

<?php

	/** Updates mail icon to alert user of how many posts in the users 'watch list' have had comments added. **/
	$updates = 0;
	if(count($watching)>0){
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
		//DOESNT check if updates>0, because mail icon should show even if with ZERO new messages, since it's the updates page.
	}

	?>
					<li><a href="index.php">Newsfeed</a></li>
					<li><a href="search.php">Search</a></li>
					<li><a href="about.php">About</a></li>
					<li class="active">
						<a href="updates.php"><span style="display: inline;"><img src="mailicon.png" /></span>&nbsp;&nbsp;<?php echo $updates; ?></a>
					</li>
				</ul>
				<div style="clear:both;"></div>
			</div>
		</div>

	<?php

	/** Select and display updates **/
	$updates = 0;
	if(count($watching)>0){
		//check for updates for each postid,commentid pair in $watching
		foreach($watching as $postidkey=>$lastactivitytimestamp){
		     //last activity timestamp doesn't change w more comments.
		     //so it's more like the first activity timestamp that hasn't been viewed
		     //so all timestamps greater than it are all new
			$query = "SELECT timestamp FROM posts WHERE parent='{$postidkey}' ORDER BY timestamp DESC LIMIT 1;";
			$result = mysqli_query($conn,$query);
			$row = mysqli_fetch_array($result);
			$latesttimestamp = $row[0];
			if($lastactivitytimestamp<$latesttimestamp){//theres newer comments since your last comment
				//Since this is a genuine update, it must be displayed
				$updates+=1;
	
				/** SELECT and display parent post **/
	
				$query = "SELECT id,post,timestamp FROM posts WHERE id='{$postidkey}';";
				$result = mysqli_query($conn,$query);
				$row = mysqli_fetch_array($result);

				$id = $row['id'];
				$post = $row['post'];
				$timestamp = $row['timestamp'];

				//ALSO needed, $numcomments = ???
				$query = "SELECT id FROM posts WHERE parent='{$postidkey}' AND  timestamp>{$lastactivitytimestamp}";
				$results = mysqli_query($conn,$query);
				$numcomments = mysqli_num_rows($results);

		/** display the status box for this parent post **/
?>

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

				$minutesago = ceil((time()-((int)($timestamp)))/60);
				if($minutesago>0){
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
					<span class="colored">
					</span>
				</div>
     
				<div style="clear: both;"></div>

					<div id='newcomments'>
						<a style="color: white; padding: 10px; display: block; text-decoration: none;" href="comments.php?id=<?php echo $id; ?>#replybox">
							<span style="font-size: 20px; font-family:'Courier New, Courier, monospace';">
								<?php echo $numcomments; ?> new comments
							</span>
						</a>
						</span>
					</div>

					<div style="clear: both;"></div>
		
				</div>
			</div>
	<?php
			}
		}
	}
	
	if($updates<1){
	?>
		<!-- Status Update -->
		<div class="container-fluid">
			<div class="row-fluid post">
				<center>No updates.</center>
			</div>
		</div>
	<?php
	}


	?>

    </div><!-- /container -->

  </body>
</html>
    
