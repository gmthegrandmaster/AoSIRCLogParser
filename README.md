AoSIRCLogParser
===============
Program created by "Grandy" TheGrandmaster for Ace of Spades Beta
http://www.buildandshoot.com

Uses current IRC output of the match plugin for the game server and converts it into detailed statistics.
Debug parsing lines are enabled by default but can be toggled.

Written in PHP, no database connection needed.
Feel free to contact through github.



Technical Details
=========
The program is split into two parts (currently), the main part (logparser.php) and the player part (player.php).

player.php:
-------------
This holds the Player class and all of its storage and functions; most of these functions just alter the storage, adding a kill or a death, incrementing counters for intel touches/caps etc. There is also a method to print out the object in a pretty format, which is used later on when we have finished parsing.

logparser.php:
--------------
This is the backbone of the program. It holds two main parts - the overall storage ($players, which is an array of Player objects) and the parser itself.
There are a few functions to aid the overall player tracking - namely hasPlayer and getPlayer, so that a player's existence can be checked and/or retrieved when wanting to add kills etc.

The parser uses regex to loop through and decipher the meaning of each line.
The regex definitions can be found near the top of the file as $PATTERN_x, and each line of the input is checked against by these regex in order to get the meaning of a line. The only exception to this is the pattern for the bot name, since it is passed in by the form and is not a constant.
Once the meaning of the line is found, it then calls the appropriate processing method for that action and passes in the data (e.g. processKill($line)). These methods then create or call the relevant Player objects and alter the appropriate values in the Player's storage.

Once the parser has finished looping over the file, all of the found Players in the $players array are looped through, and their display method is called, which produces the end output.



Running the program
===========
To develop and run the program myself, I have been using http://www.wampserver.com/en/

1. Download and install the relevant WAMP server: http://www.wampserver.com/en/#download-wrapper
2. Navigate to the insallation directory (usually c:/wamp), find the /www/ folder and extract the downloaded project into a subfolder (e.g. /www/IRCLogParser/)
3. Load up WAMP - its icon should cycle through red, amber then green (Skype can interfere with it, so if it doesn't go green, try closing skype - you can re-open it after the server has loaded).
4. Go to your web browser and navigate to http://localhost/FOLDERNAME/logparser.php
5. ???
6. Profit
