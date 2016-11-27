<?php include("databasesetup.php");//this gives us $conn to connect to mysql.?>
<?php include("hashtaglib.php");?>
<?php include("urllib.php");?>
<?php
	if(isset($_GET['id'])){
		$lastid = mysqli_real_escape_string($conn,$_GET['id']);
		$sortby = "recent";

		//setting apart for later use
		$sofar=10;

		if(isset($_GET['sort'])){
			$sortby = $_GET['sort'];
		}
    
		/* PREPARE SEARCH STRING */
		$searchstring = "";
		$search = "";
		if(isset($_GET['search'])){
			$search = $_GET['search'];
		}
		$htmlsearch = urlencode(trim($search));

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


    //ASSUMES "?sort=recent"
	if($sortby=="popular"){
		//For 'popular' lastid just counts how many posts have been displayed
		$sofar = $lastid;
		$until = 10;
		$query = "SELECT id,post,timestamp FROM posts WHERE parent=0 {$searchstring} ORDER BY score DESC LIMIT {$sofar},{$until};";
	}elseif($sortby=="random"){
		//Search for 'ORDER BY random' or equivalent
		$query = "SELECT id,post,timestamp FROM posts WHERE parent=0 {$searchstring} ORDER BY RAND() LIMIT 10;";
	}else{
		//Sort by Recent. timestamp desc
		//use "lastid" as timestamp
		$query = "SELECT id,post,timestamp FROM posts WHERE parent=0 AND timestamp<{$lastid} {$searchstring} ORDER BY timestamp DESC LIMIT 10;";
	}
	$results = mysqli_query($conn,$query);
	$num_results = 0;//set just below
	while($row = mysqli_fetch_array($results)){
		$num_results++;
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

	?>
				<a href="comments.php?id=<?php echo $id;?>" class="colored"><?php echo $commenttext; ?></a>
				<form style="display: inline;" action="index.php" method="post">
					<input type="hidden" name="share" value="<?php echo $id;?>" />
					<input class="colored buttonlink" type="submit" value="share" />
				</form>
				<!--a href="index.php?id=<?php echo $id;?>&do=share" class="colored">share</a-->
			</div>
			<div style="clear: both;"></div>
		</div>
	</div>

      <!-- Then comes the bottom button to display more. -->
	<?php

		$lastid = $id;
		if($sortby!="popular" && $sortby!="random"){
			$lastid = $timestamp;
		}
	}

    /* Next pass $lastid to tenmore.php to get a list of ten more posts to display */
    if($sortby=="popular"){
      $lastid=$sofar+10;
    }

    if($num_results>0){//"load ten more" because there's still more left
?>

    <div id="load<?php echo $lastid;?>">
   
      <!-- Status Update -->
      <div class="container-fluid">
		<a id="loadmorebutton" onclick="SetInnerHTMLFromAjaxResponse('tenmore.php?id=<?php echo $lastid;?>&sort=<?php echo $sortby?>&search=<?php echo $htmlsearch;?>','load<?php echo $lastid;?>')" href="#load<?php echo $lastid;?>">
          <div style="font-weight: bold;" class="row-fluid text-center post">
            load ten more...
            <div style="clear: both;"></div>
          </div>
        </a>
      </div>

    </div><!-- /load -->
<?php
  }else{
?>

    <div id="load<?php echo $lastid;?>">
   
      <!-- Status Update -->
      <div class="container-fluid">
         <div style="font-weight: bold;" class="row-fluid text-center post">
           no more...
           <div style="clear: both;"></div>
         </div>
      </div>

    </div><!-- /load -->
<?php
	}
  }
?>
