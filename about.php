<?php include("header.php");?>
<?php include("databasesetup.php");//this gives us $conn to connect to mysql.?>
<?php include("watchlist.php");?>
	  
					<!-- Nav Bar - About -->
					<ul id="navbar" class="nav nav-pills pull-left">
						<li><a href="index.php">Newsfeed</a></li>
						<li><a href="search.php">Search</a></li>
						<li class="active"><a href="about.php">About</a></li>
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
  
			<!-- Introduction Box -->
			<div class="container-fluid">
				<div class="row-fluid post">
            
					<!-- Left Column Spacing -->
					<div class="col-sm-1 col-xs-2">
					</div>
  
					<!-- Content -->
					<div class="col-sm-10 col-xs-9">
						<h2>Introduction</h2>
						We are Anonymous.<br/>
						We are Legion.<br/>
						We do (not) Forgive.<br/>
						We do not Forget.<br/>
						United as One.<br/>
						Divided by Zero.<br/>
						<b>We are uniting humanity.</b><br/>
						Expect Us.
						<br/><br/>
						The time for transformation is <b>now</b>.
						<br/><br/>
						Anonymous is a movement of personal empowerment.<br/>It is not a group that is coming to save you.<br/>It <i>is</i> you.
						<br/><br/>
						We are the people we have been waiting for.
						<br/><br/>
						Anonymous is the voice of truth in your heart, finally ringing out into the world without fear of consequences.
						<br/><br/>
						Anonymous is the voice that has no reputation to protect.
						<br/><br/>
						Anonymous is the voice without identity, so that the message can be judged for what's being said and not for who is saying it.
						<br/><br/>
						No one can speak for all of Anonymous, yet anyone can speak as Anonymous. For we speak as one.
						<br/><br/>
						Anonymous is the collective mouthpiece of humanity, schizophrenic and dissonant at first, yet slowly coming to an agreement with itself of how we can finally build a world that works for everyone.
						<br/><br/>
						And this website is simply to help speed up that conversation.
						<br/><br/>
						<div class="quote">
						Click the image below to watch<br/>
						Video: "The Lie We Live"
						</div>
						<br/>
						<div style="margin-left: auto; margin-right: auto;">
							<center>
							<a id="liewelive" href="https://www.youtube.com/watch?v=ipe6CMvW0Dg">
								<img src="theliewelive.jpg" />
							</a>
							</center>
						</div>
						<br/>
						<div class="quote">
							"Living in a world full of deceit, <br/>speaking the truth is revolutionary."
						</div>

						<!-- Right Column Spacing -->
						<div class="col-sm-1 col-xs-2">
						</div>

						<br/>
					</div>
					<div style="clear: both;"></div>
				</div>
			</div>
  
			<!-- How-To-Use-Openbook Box -->
			<div class="container-fluid">
				<div class="row-fluid post">
            
					<!-- Left Column Spacing -->
					<div class="col-sm-1 col-xs-2">
					</div>
	  
					<!-- Content -->
					<div class="col-sm-10 col-xs-9">
						<h2>How to Use Openbook</h2>
						<br/>
						Openbook allows anyone to post a status update as Anonymous. You can also comment
						on other status updates, and even hold ongoing conversations, all as Anonymous.
						The reason there is no need for a username or password is because everyone is essentially
						logged on as the same user: the unified, yet decentralized, collective-yet-singular entity of Anonymous.
						Which is why all the status updates begin with "We". This gives everyone a platform for making
						epic and profound statements from a <a href="https://en.wikipedia.org/wiki/Universalism#Non-religious_Universalism">universalist perspective</a>: i.e. "We the People of Earth".
						<br/><br/>
						If you "share" a status update in the main newsfeed, it just gets bumped to the top of "recent" as if it were just
						posted, and any comments that were on it will still be there as well. If you "share" a comment
						on a status update, it is reposted as its own status update. And when the site is especially active,
						any page refresh will reveal if anyone has replied to anything you posted or commented on with a little mail icon.
						That way you can get your social media fix by wormholing into conversations as long as you want,
						without the need of an account or login. We do this by tracking your history in your own cookies,
						which is erased after an hour of not using the website. Or by simply clearing your cookies.
						<br/><br/>
						Anonymous is a beautiful idea. An idea that can realistically bring about global unity of all of humanity.
						But some people don't like the way the robot voice sounds. And some people have trouble editing together sound and video
						to produce their own Anonymous video. But with Openbook, anyone and everyone can easily participate. Of course, this is just
						reinventing the wheel, as Anonymous was originally text and images posted anonymously on forums and imageboards.
						But with this cute layout that
						mimmicks social media, as well as the messaging system that lets people dive into pointless conversation for days on end if
						they want, we have hopefully delivered to people a convenient way to ween themselves off the identity-driven culture that most
						social media is based on.
						<br/><br/>
						We are Anonymous.
						<br/><br/>
						We are Legion.
						<br/><br/>
						We <i>may</i> forgive..
						<br/><br/>
						..but..
						<br/><br/>
						We do not forget.
						<br/><br/>
						Expect Us!
						<br/><br/>
					</div>

					<!-- Right Column Spacing -->
					<div class="col-sm-1 col-xs-2">
					</div>

					<div style="clear: both;"></div>
				</div>
			</div>

			<!-- What-is-Openbook? Box -->
			<div class="container-fluid">
				<div class="row-fluid post">
				
					<!-- Left Column Spacing -->
					<div class="col-sm-1 col-xs-2">
					</div>
	  
					<!-- Content -->
					<div class="col-sm-10 col-xs-9">
						<span>
							<h2>What is Openbook?</h2>
							<br/>
							Openbook is Anonymous' answer to Facebook.
							<br/><br/>
							On Facebook, all our actions are tied to our face and name.
							This makes us think twice before we post something, essentially censoring ourselves.
							Since we know that everything is documented and tied to
							our identity, we tailor our actions to be socially acceptable to everyone on our "friends" list.
							<br/><br/>
							As time goes on, you realize you're forever tethered to everyone
							from your past. The same argument with the guy from high school will play out every time you
							try to discuss religion, so you eventually tire of the conversation and stop mentioning it.
							Your uncle always steers the comments under your political posts toward a liberals versus conservatives
							rhetoric so you can never seem to get across your point about global unity.
							You become caught in a feedback loop of conversation that goes nowhere.
							<br/><br/>
							Social media seems to hold the promise of waking up the masses, but because everything is attached
							to our old world identities, our relative perception of each other and the assumptions and illusions
							we have defined our relationships on are held in place by our constant presence in each other's lives.
							This is how groupthink works. It's how TV has been used to keep us stuck in a collective illusion, and
							it's how our own relationships to each other are being used against us through Facebook to hold that
							TV reality in place.
							<br/><br/>
							And now that anyone can take pictures with their camera phones, and anyone can tag anyone else in any Facebook picture, the
							1984 Big Brother surveillance era is finally upon us. Anyone with a Facebook account is catalogued. Their actions documented. Their interests noted.
							Networks of their relationships mapped out. And then the first time someone posts a photo of you doing something embarassing or illegal,
							and carelessly tags you in it anyways, you begin to see the mess we're in.
							If you get mad at them, they get mad at you. You can remove the tag but you know it's already documented somewhere.
							It would seem the only escape is to get away from people with cameraphones, and stop using Facebook altogether.
							<br/><br/>
							But you just can't seem to unplug.
							You can sort of fake "delete" your account,
							but you know it isn't really deleted because Facebook invites you to reactivate your account at any time.
							Finally, people have become so used to Facebook that they'll wonder why you haven't responded, or someone
							you just met will try to connect on Facebook as if it's the new phone number, even being offended if you
							refuse the information, and pretty soon you find yourself feeling pressured into logging back in.
							<br/><br/>
							As time goes on, and the migration off of Facebook never seems to reach critical mass, people start
							to psychologically adjust to the prison we're being funneled into. They'll say you're being "negative" when
							you point out the problems with Facebook, and they in turn point out what they like about Facebook.
							This is because, in a state of cognitive dissonance, when our actions are out of line with our thoughts and beliefs,
							we seek to change either one or the other to bring our thoughts and actions into harmony once again.
							So since we have so much trouble taking action and getting off Facebook, we eventually fool ourselves into thinking
							Facebook isn't that bad. It's easier than finding or creating a decent alternative, after all.
							<br/><br/>
							Meanwhile, Facebook has openly admitted to running psychological experiments on unknowing users. Who knows which of us were chosen.
							They randomly selected a couple thousand people, and filtered their newsfeeds to filter out positive emotional words like "happy".
							They then observed their predicted result: that the unwitting participants would themselves post fewer positive words in their own
							status update. And because we are so sensitive, and our emotions are so contageous, it could be argued that such an experiment
							affected not only the participants, but everyone on Facebook, and in turn everyone in the whole world.
							<br/><br/>
							We cannot stand idly by as our minds are experimented on by those in power.
							<br/><br/>
							We cannot continue to voluntarily feed all of our information to those that would seek to use it against us.
							<br/><br/>
							Solidarity through anonymity.
							<br/><br/>
							Death of the ego through loss of identity.
							<br/><br/>
							United as One.
							<br/><br/>
							Divided by Zero.
							<br/><br/>
							We are Anonymous.
							<br/><br/>
						</div>

						<!-- Right Column Spacing -->
						<div class="col-sm-1 col-xs-2">
						</div>

					<div style="clear: both;"></div>

				</div>
			</div>



			<!-- Contact Box -->
			<div class="container-fluid">
				<div class="row-fluid post">
				
				<!-- Left Column Spacing -->
				<div class="col-sm-1 col-xs-2">
				</div>
	  
				<!-- Content -->
				<div class="col-sm-10 col-xs-9">
					<h2>Contact Us</h2>
					<span>
						If you have comments, questions, or suggestions for the programmers of this website, please email openbook@sigaint.org
						<br/><br/>
						The source-code of the website is open-source, and is hosted at <a href="https://github.com/openboo/Openbook-Opensource-Software">github</a>.
						Copycat websites are welcome, as diversity is in nature itself and Anonymous
						is only powerful with multiplicity and decentralization.
						<br/><br/>
						Openbook Opensource Software is copy-lefted under the
						GNU Affero General Public License Version 3.
						We have a copy you can read for yourself, <a href="LICENSE">right here</a>.
						It is essentially a modified version of the ordinary GNU GPL version 3,
						with one added requirement: if you run a modified program on a server
						and let other users communicate with it there, your server must also allow them
						to download the source code corresponding to the modified version running there.
						This is just in the interests of making it easier for anyone to start their own
						Openbook p0rtal, even duplicating or modeling it after a modified one if they wish.
						<br/><br/>
						This website was created in the spirit of <a href="http://distribution.neocities.org">distribution</a>.
					</span>
				</div>

				<!-- Right Column Spacing -->
				<div class="col-sm-1 col-xs-2">
				</div>
		 
				<div style="clear: both;"></div>
			   
				</div>
			</div>
 
		</div><!-- /container -->

	</body>
</html>
