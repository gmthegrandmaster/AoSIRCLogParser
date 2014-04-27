<?php	

	/*
	 * 	Ace of Spades Beta - IRC log file parser
	 * 	Made by "Grandy" TheGrandmaster
	 * 	Last updated 27/04/2014 19:57
	 *
	 * 	logparser.php - centre for parsing the log and tracking players
	 *
	 */

	// PHP file containing the player class
	include_once('player.php');

	// Storage
	$players = array();
	$num_players = 0;

	$verbose = false;	// Sets whether debug output is enabled

	// Delimeters, used for substringing and patterns
	$TEXT_KILL_MSG = "killed";
	$TEXT_NOTIF_DELIM = "*";
	$TEXT_TOUCH = "took the";
	$TEXT_CAPTURE = "captured the";

	// Global vars
	$PATTERN_TIMESTAMP = "/\[[0-9]{2}(:)[0-9]{2}\].*$/";		// [hh:mm] 		OR 		07[hh:mm]
	$PATTERN_KILL = "/^.{0,15}( ".$TEXT_KILL_MSG." ).{0,15}(!)$/";	// [13:58] <BotName> Player1 killed Player2!
	$PATTERN_NOTIFICATION = "/^(\*).*$/";							// [14:18] <BotName> * 18 minutes, 59 seconds remaining.
	$PATTERN_TOUCH = "/^.{0,15}( ".$TEXT_TOUCH." ).{0,15}( flag in ).{2}(!)$/";	// [13:58] <BotName> Player3 took the TeamName flag in A1!
	$PATTERN_CAPTURE = "/^.{0,15}( ".$TEXT_CAPTURE." ).{0,15}( flag!)$/";		// [13:58] <BotName> Player4 captured the TeamName flag!

?>

<html>
	<title>Ace of Spades Beta - Log File Parser</title>
	<head>
		<link rel="StyleSheet" href="style.css" type="text/css" />
		<script type="text/Javascript">
			function updateBotGen(){
				var bot_channel_status_elem = document.getElementById("BotStatus");
				var bot_channel_status = bot_channel_status_elem.options[bot_channel_status_elem.selectedIndex].value;
				var bot_channel_name = document.getElementById("BotName").value;
				document.getElementById("bot_name_gen").innerHTML = "&lt;"+bot_channel_status+bot_channel_name+"&gt;";
			}
		</script>
	</head>
	<body>
		<h1>Ace of Spades - Log Parser</h1>
		This is a log parsing tool, created by TheGrandmaster. <br />
		Simply set the bot name (and channel level in the dropdown) and copy your IRC log into the textarea below.<br />
		The program detects whether you have a [hh:mm] timestamp and adjusts accordingly.<br /><br />
		<form action="" method="post">
			<b>Bot name:</b> 
				<?php 
				 	// Set which BotStatus is selected on page load
					$none = $voice = $halfop = $op = "";
					 	if(isset($_POST['BotStatus'])){
				 		if($_POST['BotStatus'] == ''){
				 			$none = " SELECTED";
				 		}else if($_POST['BotStatus'] == '+'){
				 			$voice = " SELECTED";
				 		}else if($_POST['BotStatus'] == '%'){
				 			$halfop = " SELECTED";
				 		}else if($_POST['BotStatus'] == '@'){
				 			$op = " SELECTED";
				 		}
				 	}else{
				 		// Default the status to being none
				 		$none = " SELECTED";
				 	}
				?>
				<select name="BotStatus" id="BotStatus" onchange="updateBotGen()">
					<option name="BotStatus" value=""<?php echo $none; ?> /></option> 
					<option type="radio" value="+"<?php echo $voice; ?>>+</option> 
					<option type="radio" value="%"<?php echo $halfop; ?>>%</option>
					<option type="radio" value="@"<?php echo $op; ?>>@</option>
				</select>
			<input type="text" name="BotName" id="BotName" onchange="updateBotGen()" <?php if(isset($_POST['BotName'])) echo "value=\"".$_POST['BotName']."\""; ?>/>

			<span class="bot_name_gen" id="bot_name_gen"></span><br />

			Enable verbose output: <input type="checkbox" name="Verbose" value="on" <?php if(isset($_POST['Verbose'])) echo " checked"; ?>/><br />
			
			<h2>Log:</h2>
				<textarea name="Log" cols="100" rows="20"><?php 
					if(isset($_POST['Log'])) echo htmlspecialchars($_POST['Log']); 
				?></textarea><br />
			<input type="submit" value="Parse" name="Submit"/>
		</form>

<?php
// Handle form submission
if(isset($_POST['Submit'])){
	global $verbose;

	// Assumed input variables
	$log_file = $_POST['Log'];
	$bot_name_token = "<".$_POST['BotStatus'].$_POST['BotName'].">";
	if(isset($_POST['Verbose'])){
		$verbose = true;
	}else{
		$verbose = false;
	}

	// If verbose is enabled, create an area for the debug output
	if($verbose){
		?>
		<h4>Verbose Log:
		<div name="verbose" class="verbose_box">
		<?php
	}
	// Players array searching
	function hasPlayer($name){
		global $players;
		foreach($players as $num => $player){
			if($player->getName() == $name){
				return true;
			}
		}
		return false;
	}
	// Players array getting
	function getPlayer($name){
		global $players;
		foreach($players as $num => $player){
			if($player->getName() == $name){
				return $player;
			}
		}
		return false;
	}


	// Processing functions
	function processKill($line){
		global $TEXT_KILL_MSG;
		global $TEXT_KILL_END_DELIM;
		global $players;
		global $num_players;
		global $verbose;

		// The killer is the section prior to the occurance of the kill message delim
		// Use an untrimmed version while we substr the line
		$killer = strstr($line, $TEXT_KILL_MSG, true);

		// The victim is the part of the message beyond the kill message delim
		$victim = trim(
					substr(	$line, 										// Source String
							strlen($killer)+strlen($TEXT_KILL_MSG), 	// Begin at index [len of killer name (and space) + len of delim]
							strlen($line)								// End 
							)	
						);
		$victim = substr($victim, 0, strlen($victim)-1);

		// Trim the killer name after getting the other information for proper output
		$killer = trim($killer);
		
		// Check if the killer is being tracked
		if(!hasPlayer($killer)){
			// Not being tracked
			$killer_obj = new Player($killer);
			$players[$num_players] = $killer_obj;
			$num_players += 1;
		}
		if($killer == $victim){
			// Suicide!
			getPlayer($killer)->addSuicide();
		}else{
			// Legitimate kill
			getPlayer($killer)->addKill($victim);
		}

		// Check if the victim is being tracked
		if(!hasPlayer($victim)){
			$victim_obj = new Player($victim);
			$players[$num_players] = $victim_obj;
			$num_players += 1;
		}
		if($killer == $victim){
			// Suicide!
			// Do nothing to the score tallies (suicides tracked above in 'kills')
		}else{
			// Legitimate death
			getPlayer($victim)->addDeath($killer);
		}

		if($verbose){
			echo "<span class='line_kill'>KILL:</span> <b>Killer:</b> ".htmlspecialchars($killer).", <b>Victim:</b> ".htmlspecialchars($victim)."<br />";
		}
	}

	function processTouch($line){
		global $players;
		global $num_players;
		global $TEXT_TOUCH;
		global $TEXT_KILL_END_DELIM;
		global $verbose;

		$taker = trim(strstr($line, $TEXT_TOUCH, true));

		if(!hasPlayer($taker)){
			// Not being tracked
			$taker_obj = new Player($taker);
			$players[$num_players] = $taker_obj;
			$num_players += 1;
		}

		getPlayer($taker)->addTouch();

		if($verbose){
			echo "<span class='line_touch'>INTEL TOUCH:</span> ".htmlspecialchars($taker)." touched the opposing team's flag<br />";
		}

	}

	function processCapture($line){
		global $players;
		global $num_players;
		global $TEXT_CAPTURE;
		global $TEXT_KILL_END_DELIM;
		global $verbose;

		$capper = trim(strstr($line, $TEXT_CAPTURE, true));

		if(!hasPlayer($capper)){
			// Not being tracked
			$capper_obj = new Player($capper);
			$players[$num_players] = $capper_obj;
			$num_players += 1;
		}

		getPlayer($capper)->addCapture();

		if($verbose){
			echo "<span class='line_capture'>INTEL CAPTURE:</span> ".htmlspecialchars($capper)." CAPTURED the opposing team's flag!<br />";
		}
	}


	// Parse the input
	foreach(preg_split("/(\r?\n)/", $log_file) as $rawline){

		// Calculate the number of splits needed
		$num_splits = 2;
		if(preg_match($PATTERN_TIMESTAMP, $rawline)){
			// Line has a timestamp, one more split needed
			$num_splits = 3;
		}
		$split = explode(" ", $rawline, $num_splits);

		// Prevent indexing errors by catching it if the split didn't produce enough sections
		if(count($split) < $num_splits){
			if($verbose){
				echo "<span class='line_unknown'>UNKNOWN LINE SPLIT:</span> ".htmlspecialchars($rawline)."<br />";
			}
			continue;
		}

		// Set the line
		$line = $split[count($split)-1];

		// Figure out what the line is
		$pattern_botname = "/^".$bot_name_token.".*$/";
		if(!preg_match($pattern_botname, $split[$num_splits-2])){
			// The message is not from the server bot, must be another IRC user
			if($verbose){
				echo "<span class='line_irc'>IRC MESSAGE:</span> ".htmlspecialchars($rawline)."<br />";
			}
			continue;

		}else if(preg_match($PATTERN_NOTIFICATION, $line)){
			// Line is a notification (player join/exit, timer)
			if($verbose){
				echo "<span class='line_bot'>BOT NOTIFICATON:</span> ".htmlspecialchars($rawline)."<br />";
			}
			continue;

		}else if(preg_match($PATTERN_KILL, $line)){
			// Pattern match for an kill message
			processKill($line);
			continue;

		}else if(preg_match($PATTERN_TOUCH, $line)){
			// Pattern match for an Intel touch message
			processTouch($line);
			continue;
		
		}else if(preg_match($PATTERN_CAPTURE, $line)){
			// Pattern match for an Intel capture message
			processCapture($line);
			continue;

		}else{
			// Still don't know what this line is
			if($verbose){
				echo "<span class='line_unknown'>UNKNOWN LINE:</span> ".htmlspecialchars($rawline)."<br />";
			}
			continue;
		}

	}

	// Close off debug text area if verbose
	if($verbose){
		?>
		</div><br /><br />
		<?php
	}


	// Finished processing, PRINT!
	foreach($players as $num => $player){
		$player->printPretty();
	}


}



?>
	</body>
</html>