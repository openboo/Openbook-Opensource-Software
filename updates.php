<!DOCTYPE html>
<html lang="en">

	<head>
		<!--You've uncovered another stop on the underground railroad!-->
		<!--Perhaps you're looking for: p0rtalurl/IMPORTANT.txt -->

		<!-- Metadata -->
		<meta charset="utf-8">
		<title>openbook</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		<!-- Styles for Bootstrap -->
		<link href="css/bootstrap.css" rel="stylesheet">
		<link href="css/bootstrap-responsive.css" rel="stylesheet">

		<!-- Extra Stylin' -->
		<link href="css/style.css" rel="stylesheet">

		<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
			<script src="../assets/js/html5shiv.js"></script>
		<![endif]-->

		<!-- Fav and touch icons -->
		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="ico/apple-touch-icon-144-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="ico/apple-touch-icon-114-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="ico/apple-touch-icon-72-precomposed.png">
		<link rel="apple-touch-icon-precomposed" href="ico/apple-touch-icon-57-precomposed.png">
		<link rel="shortcut icon" href="ico/favicon.png">

		<!-- Simple Ajax Library (SAL) -->
		<script language="javascript" src="js/ajax.js"></script>

	</head>

	<body>

<?php

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
		//DOESNT check if updates>0, because mail icon should show even if with ZERO new messages, since it's the updates page.
	}

	?>
					<li><a href="index.php">Newsfeed</a></li>
					<li><a href="search.php">Search</a></li>
					<li><a href="about.php">About</a></li>
					<li class="active">
						<a href="updates.php"><span style="display: inline;" class="glyphicon glyphicon-envelope"></span>&nbsp;&nbsp;<?php echo $updates; ?></a>
					</li>
				</ul>
				<div style="clear:both;"></div>
			</div>
		</div>

	<?php

	/** Select and display updates **/

	if(count($watching)>0){
		$updates = 0;
		//check for updates for each postid,commentid pair in $watching
		foreach($watching as $postidkey=>$lastactivitytimestamp){
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
				$query = "SELECT id FROM posts WHERE parent='{$postidkey}';";
				$results = mysqli_query($conn,$query);
				$numcomments = mysqli_num_rows($results);

		/** display the status box for this parent post **/
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

			echo stripslashes($post);

	?>
					</span>
					<br/>
					<span class="colored">
					</span>
				</div>
     
				<div style="clear: both;"></div>

					<div id='newcomments'>
						<a style="color: white; padding: 10px; display: block; text-decoration: none;" href="comments.php?id=<?php echo $id; ?>#replybox">
							<span style="font-size: 20px; font-family:‘Courier New’, Courier, monospace;">
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
	}

	?>

    </div><!-- /container -->

    <!-- javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->

    <script src="js/jquery.js"></script>
    <script src="js/bootstrap-transition.js"></script>
    <script src="js/bootstrap-alert.js"></script>
    <script src="js/bootstrap-modal.js"></script>
    <script src="js/bootstrap-dropdown.js"></script>
    <script src="js/bootstrap-scrollspy.js"></script>
    <script src="js/bootstrap-tab.js"></script>
    <script src="js/bootstrap-tooltip.js"></script>
    <script src="js/bootstrap-popover.js"></script>
    <script src="js/bootstrap-button.js"></script>
    <script src="js/bootstrap-collapse.js"></script>
    <script src="js/bootstrap-carousel.js"></script>
    <script src="js/bootstrap-typeahead.js"></script>
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
