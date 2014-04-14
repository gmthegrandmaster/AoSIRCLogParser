<?php

	/*
	 * Ace of Spades Beta - IRC log file parser
	 * Made by "Grandy" TheGrandmaster
	 * Last updated 13/04/2014 04:09
	 *
	 * player.php - container and methods for a player object
	 */

	class Player {
		
		private $player_name = "UNDEFINED";

		private $player_kill_list = array();
		private $player_total_kills = 0;

		private $player_death_list = array();
		private $player_total_deaths = 0;

		private $player_touches = 0;
		private $player_caps = 0;
		private $player_suicides = 0;

		// Constructor, passes in the name
		function __construct($name) {
	       	$this->player_name = $name;
	   	}

	   	// Getter for the player's name
	   	function getName(){
	   		return $this->player_name;
	   	}

	   	// Function to add a specified kill
		function addKill($victim){

			if(!array_key_exists($victim, $this->player_kill_list)){
				// Initialise if not exist
				$this->player_kill_list[$victim] = 1;
			}else{
				// Increment current counter
				$this->player_kill_list[$victim] += 1;
			}

			$this->player_total_kills += 1;
		}

		// Function to add a specific death
		function addDeath($killer){

			if(!array_key_exists($killer, $this->player_death_list)){
				// Initialise if not exist
				$this->player_death_list[$killer] = 1;
			}else{
				// Increment current counter
				$this->player_death_list[$killer] += 1;
			}

			$this->player_total_deaths += 1;
		}

		// Function to add to the Intel touch tally
		function addTouch(){
			$this->player_touches += 1;
		}

		// Function to add to the Intel capture tally
		function addCapture(){
			$this->player_caps += 1;
		}

		// Function to add to the player's suicide tally
		function addSuicide(){
			$this->player_suicides += 1;
		}

		// Function to print the player's statistics in a pretty (boxed) format
		function printPretty(){
			
			?>
			<div class="box">
				<h4>Player:</h4> <?php echo $this->player_name; ?><br />

				<table>
					<tr>
						<td class="t_header">vs. Name</td>
						<td class="t_header">Killed</td>
						<td class="t_header">Died</td>
					</tr>
				<?php
				// Get a list of all player names
				$name_list = array();
				$name_list = $this->player_kill_list;

				// Add values from the death list into the central name list
				if($this->player_death_list != null){
					foreach($this->player_death_list as $player => $value){
						if(!in_array($player, $name_list)){
							$name_list[$player] = $value;
						}
					}
				}

				if($name_list != null){
					foreach($name_list as $player => $value){

						// Set kills for this player to be 0 if it doesn't exist
						if(!isset($this->player_kill_list[$player])){
							$this->player_kill_list[$player] = 0;
						}
						// Set deaths for this player to be 0 if it doesn't exist
						if(!isset($this->player_death_list[$player])){
							$this->player_death_list[$player] = 0;
						}
						// (these prevent null pointers)
						?>
						<tr>
							<td class="t_main"><?php echo $player; ?></td>
							<td class="t_main"><?php echo $this->player_kill_list[$player]; ?></td>
							<td class="t_main"><?php echo $this->player_death_list[$player]; ?></td>
						</tr>
						<?php
					}
				}else{
					?>
						<tr>
							<td class="t_main">-</td>
							<td class="t_main">-</td>
							<td class="t_main">-</td>
						</tr>
					<?php
				}

				$ratio = 0.0;
				// Calculate player ratio
				if($this->player_total_deaths != 0){
					$ratio = $this->player_total_kills / $this->player_total_deaths;
				}else{
					$ratio = $this->player_total_kills;
				}

				?>
					<tr>
						<td class="t_header">Totals</td>
						<td class="t_total"><?php echo $this->player_total_kills; ?></td>
						<td class="t_total"><?php echo $this->player_total_deaths; ?></td>
					</tr>
					<tr>
						<td class="t_header">Ratio</td>
						<td class="t_total" colspan="2"><b><?php echo $this->twodp($ratio); ?></b></td>
					</tr>
					<tr>
						<td class="t_header">Flag Takes</td>
						<td class="t_total" colspan="2"><?php echo $this->player_touches; ?></td>
					</tr>
					<tr>
						<td class="t_header">Flag Caps</td>
						<td class="t_total" colspan="2"><?php echo $this->player_caps; ?></td>
					</tr>
					<tr>
						<td class="t_header">Suicides</td>
						<td class="t_total" colspan="2"><?php echo $this->player_suicides; ?></td>
					</tr>
					</table>
					<br /><br />
				</div>
		<?php

		}

		// Function (helper) to print numbers in two decimal format
		function twodp($number){
			return sprintf("%.2f", $number);
		}

	}

?>