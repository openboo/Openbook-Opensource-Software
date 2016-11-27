<?php include("header.php");?>
<?php include("urllib.php");?>
<?php include("hashtaglib.php");?>
<?php include("databasesetup.php");//this gives us $conn to connect to mysql.?>
<?php include("watchlist.php");?>
					<!-- Nav Bar - Search -->
					<ul id="navbar" class="nav nav-pills pull-left">
						<li><a href="index.php">Newsfeed</a></li>
						<li class="active"><a href="search.php">Search</a></li>
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
					
<?php

	/** The Search page lists all hashtags that have been used more than once.
	 * If you click on them, they just add themselves to the text box for your search. **/
	//Find the hashtag with the MOST uses.       
	$query = "SELECT uses FROM hashtags WHERE uses>1 ORDER BY uses DESC;";
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	$most_uses = (int)$row['uses'];

	$query = "SELECT id,hashtag,uses FROM hashtags WHERE uses>1 ORDER BY RAND();";
	$result = mysqli_query($conn,$query);
	if($result){
		$num_rows = mysqli_num_rows($result);
		for($i=0;$i<$num_rows;$i++){
			$row = mysqli_fetch_assoc($result);
			$tag = $row['hashtag'];
			$uses = (int)$row['uses'];
			$maxfontsize = 25;
			$fontsize = ceil(($uses/$most_uses)*$maxfontsize)+5;
			echo "<span style='font-size:{$fontsize}px'><a style='color: white;' href='?search=%23{$tag}'>#{$tag}</a></span> ";
		}
	}


	/* search variable */
	$search = "";
	if(isset($_GET['search'])){
		$search=$_GET['search']; 
	}

	/* sort variable */
	$sortby = "";
	if(isset($_GET['sort'])){
		$sortby=$_GET['sort'];
	}
?>

<?php
	$append = "";
	if($sortby=="popular"){
		$append="?sort=popular";
	}elseif($sortby=="random"){
		$append="?sort=random";
	}else{//$sortby=='recent' || $sortby==''
		$append="?sort=recent";
	}
?>
						<form id="statusform" action="search.php" method="GET">
							<input id="sort" name="sort" class="formtext" type="hidden" value="<?php echo stripslashes($sortby); ?>"></input>
							<input id="status" name="search" class="formtext" type="text" value="<?php echo stripslashes($search); ?>"></input>
							<input id="updatebutton" class="formbutton" type="submit" value="Search"></input>
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

	$htmlsearch = urlencode(trim($search));

?>
						<a <?php if($sortby=='recent' || $sortby==''){echo "class='selected'";} ?> href="search.php?sort=recent&search=<?php echo $htmlsearch; ?>">recent</a>
						<a <?php if($sortby=='popular'){echo "class='selected'";} ?> href="search.php?sort=popular&search=<?php echo $htmlsearch; ?>">trending</a>
						<a <?php if($sortby=='random'){echo "class='selected'";} ?> href="search.php?sort=random&search=<?php echo $htmlsearch;?>">random</a>
					</div>
				</div>
			</div>

<?php

	/* PREPARE SEARCH STRING */
	$searchstring = "";

	$num_words = 0;
	$searchterms = explode(" ",$search);
	$num_words = count($searchterms);
      
	if($num_words>0){
		$searchstring = " AND (";
	}
	//format searchstring. separate by spaces. turn into 'col' like '%word%' OR
	for($i=0;$i<$num_words;$i++){
		$searchstring .= "post LIKE '%".$searchterms[$i]."%' ";
		if($i<($num_words-1)){
			$searchstring .= " AND ";
		}
	}
	if($num_words>0){
		$searchstring .= ") ";
	}

	$lastid=0;
	
    if(isset($_GET['id'])){
		$lastid = mysqli_real_escape_string($conn,$_GET['id']);
	}
	//set up query for which posts to grab and in what order based on the user's 'sort by' selection.
	if($sortby=="popular"){////WHERE parent=0 AND (post LIKE '%blah%' AND post LIKE '%blah%'...) for each search term
		$query = "SELECT id,post,timestamp FROM posts WHERE parent=0 {$searchstring} ORDER BY score DESC LIMIT ";//open-ended
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
		$query = "SELECT id,post,timestamp FROM posts WHERE parent=0 {$searchstring} ORDER BY RAND() LIMIT 10;";
	}else{
		//this makes it so it works with or without js
		if($lastid>0){
			$forNONjs = "AND timestamp<$lastid ";
		}else{
			//they have js
			$forNONjs = "";
		}
		//Sort by Recent. timestamp desc
		$query = "SELECT id,post,timestamp FROM posts WHERE parent=0 {$searchstring} ".$forNONjs."ORDER BY timestamp DESC LIMIT 10;";
		//For 'recent': 'lastid' actually represents 'lasttimestamp'
	}
	$results = mysqli_query($conn,$query);
	$lastid = 0;
	$lasttimestamp = 0;
	while($row = mysqli_fetch_array($results)){
		$id = $row['id'];
		$post = $row['post'];
		$timestamp = $row['timestamp'];
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
						<img src="anons/anon<?php echo(rand(1,$settings['numimages']).".".$settings['imagetype']);?>" />
						Anonymous
					</div>
	  
					<!-- Status Update Content -->
					<div class="col-sm-10 col-xs-9">
						<span class="colored">
<?php
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
						<a href="index.php?id=<?php echo $id;?>&vote=up" class="colored"><img style="border:0px" src="uparrow.png" /></a>
						<a href="index.php?id=<?php echo $id;?>&vote=down" class="colored"><img style="border:0px" src="downarrow.png" /></a>
						<span class="colored">
<?php
		if($score>-1){
			echo "+";
		}
		echo $score;

?>

						</span>

<?php
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
						<a id="loadmorebutton" onclick="SetInnerHTMLFromAjaxResponse('tenmore.php?id=<?php echo $lastid;?>&sort=random&search=<?php echo $htmlsearch;?>','load<?php echo $lastid;?>')" href="?sort=random&search=<?php echo $htmlsearch;?>">
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
						<a id="loadmorebutton" onclick="SetInnerHTMLFromAjaxResponse('tenmore.php?id=10&sort=popular&search=<?php echo $htmlsearch;?>','load10')" href="?id=<?php echo $lastid;?>&sort=popular&search=<?php echo $htmlsearch;?>">
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

	/* Next pass $lasttimestamp to tenmore.php to get a list of ten more posts to display */
?>

			<div id="load<?php echo $lasttimestamp;?>">
   
				<!-- Status Update -->
				<div class="container-fluid">
					<div class="row-fluid">
						<a id="loadmorebutton" onclick="SetInnerHTMLFromAjaxResponse('tenmore.php?id=<?php echo $lasttimestamp;?>&search=<?php echo $htmlsearch;?>','load<?php echo $lasttimestamp;?>')" href="?id=<?php echo $lasttimestamp;?>&search=<?php echo $htmlsearch;?>">
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
	</body>
</html>
