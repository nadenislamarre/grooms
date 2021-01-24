<?php
require_once($GLOBAL_BASE."/system/include/helpers.php");

$CONFIG["password"] = "";
$CONFIG["show_times_if_not_connected"]    = true;
$CONFIG["show_comments_if_not_connected"] = true;

$CONFIG["db"]["db_path"]                  = $GLOBAL_BASE."/".$GLOBAL_DATA."/go.db";
$CONFIG["db"]["dbcount_path"]             = $GLOBAL_BASE."/".$GLOBAL_DATA."/gocount.db";
$CONFIG["game"]["stepsInit"]              = 18;    	    // default board size
$CONFIG["game"]["handicapInit"]           =  0;    	    // default handicap
$CONFIG["game"]["komiInit"]               =  6.5;  	    // default komi
$CONFIG["graphics"]["offset"]             =  1;    	    // larger on picture around the grid
$CONFIG["graphics"]["letters_offset"]     =  -5;    	    // offset of letters on board
$CONFIG["graphics"]["max_image_width"]    = 700;   	    // maximum larger of the image
$CONFIG["graphics"]["max_step_width"]     =  40;   	    // maximum larger of the ste
$CONFIG["backups"]["db_backup_directory"] = $GLOBAL_BASE."/".$GLOBAL_DATA."/backups";
$CONFIG["backups"]["db_backup_prefix"]    = "game";
$CONFIG["backups"]["dbcount_backup_prefix"] = "count";

$CONFIG["graphics"]["board_prefix"]   = $GLOBAL_BASE."/system/images/board_";
$CONFIG["graphics"]["board_suffix"]   = ".jpg";
$CONFIG["graphics"]["sblack_prefix"]  = $GLOBAL_BASE."/system/images/sblack_";
$CONFIG["graphics"]["sblack_suffix"]  = ".png";
$CONFIG["graphics"]["swhite_prefix"]  = $GLOBAL_BASE."/system/images/swhite_";
$CONFIG["graphics"]["swhite_suffix"]  = ".png";
$CONFIG["graphics"]["shadow_prefix"]  = $GLOBAL_BASE."/system/images/shadow_";
$CONFIG["graphics"]["shadow_suffix"]  = ".png";
$CONFIG["graphics"]["spblack_prefix"] = $GLOBAL_BASE."/system/images/spblack_";
$CONFIG["graphics"]["spblack_suffix"] = ".png";
$CONFIG["graphics"]["spwhite_prefix"] = $GLOBAL_BASE."/system/images/spwhite_";
$CONFIG["graphics"]["spwhite_suffix"] = ".png";
$CONFIG["graphics"]["slblack_prefix"] = $GLOBAL_BASE."/system/images/slblack_";
$CONFIG["graphics"]["slblack_suffix"] = ".png";
$CONFIG["graphics"]["slwhite_prefix"] = $GLOBAL_BASE."/system/images/slwhite_";
$CONFIG["graphics"]["slwhite_suffix"] = ".png";
$CONFIG["graphics"]["sdblack_prefix"] = $GLOBAL_BASE."/system/images/sdblack_";
$CONFIG["graphics"]["sdblack_suffix"] = ".png";
$CONFIG["graphics"]["sdwhite_prefix"] = $GLOBAL_BASE."/system/images/sdwhite_";
$CONFIG["graphics"]["sdwhite_suffix"] = ".png";
$CONFIG["graphics"]["snone_prefix"]   = $GLOBAL_BASE."/system/images/snone_";
$CONFIG["graphics"]["snone_suffix"]   = ".png";
$CONFIG["graphics"]["snopoint_prefix"] = $GLOBAL_BASE."/system/images/snopoint_";
$CONFIG["graphics"]["snopoint_suffix"] = ".png";
$CONFIG["graphics"]["sptblack_prefix"] = $GLOBAL_BASE."/system/images/sptblack_";
$CONFIG["graphics"]["sptblack_suffix"] = ".png";
$CONFIG["graphics"]["sptwhite_prefix"] = $GLOBAL_BASE."/system/images/sptwhite_";
$CONFIG["graphics"]["sptwhite_suffix"] = ".png";
$CONFIG["graphics"]["stone_reduction"] = 1; // stone reduction size

$CONFIG["graphics"]["shadow_width"][9]    = 64; // shadow offset
$CONFIG["graphics"]["shadow_offsetX"][9]  = -7; // shadow offset
$CONFIG["graphics"]["shadow_offsetY"][9]  =  2; // shadow offset

$CONFIG["graphics"]["shadow_width"][13]   = 64; // shadow offset
$CONFIG["graphics"]["shadow_offsetX"][13] = -7; // shadow offset
$CONFIG["graphics"]["shadow_offsetY"][13] =  2; // shadow offset

$CONFIG["graphics"]["shadow_width"][19]   = 58; // shadow offset
$CONFIG["graphics"]["shadow_offsetX"][19] = -5; // shadow offset
$CONFIG["graphics"]["shadow_offsetY"][19] =  1; // shadow offset

// graphics for ie
$CONFIG["graphics2"]["offset"]         = $CONFIG["graphics"]["offset"];
$CONFIG["graphics2"]["letters_offset"] = $CONFIG["graphics"]["offset"];
$CONFIG["graphics2"]["max_image_width"]= $CONFIG["graphics"]["max_image_width"];
$CONFIG["graphics2"]["max_step_width"] = $CONFIG["graphics"]["max_step_width"];
$CONFIG["graphics2"]["board_prefix"]   = $GLOBAL_BASE."/system/images/board_";
$CONFIG["graphics2"]["board_suffix"]   = ".jpg";
$CONFIG["graphics2"]["sblack_prefix"]  = $GLOBAL_BASE."/system/images/sblack_";
$CONFIG["graphics2"]["sblack_suffix"]  = ".gif";
$CONFIG["graphics2"]["swhite_prefix"]  = $GLOBAL_BASE."/system/images/swhite_";
$CONFIG["graphics2"]["swhite_suffix"]  = ".gif";
$CONFIG["graphics2"]["shadow_prefix"]  = $GLOBAL_BASE."/system/images/snone_";
$CONFIG["graphics2"]["shadow_suffix"]  = ".gif";
$CONFIG["graphics2"]["spblack_prefix"] = $GLOBAL_BASE."/system/images/spblack_";
$CONFIG["graphics2"]["spblack_suffix"] = ".gif";
$CONFIG["graphics2"]["spwhite_prefix"] = $GLOBAL_BASE."/system/images/spwhite_";
$CONFIG["graphics2"]["spwhite_suffix"] = ".gif";
$CONFIG["graphics2"]["slblack_prefix"] = $GLOBAL_BASE."/system/images/slblack_";
$CONFIG["graphics2"]["slblack_suffix"] = ".gif";
$CONFIG["graphics2"]["slwhite_prefix"] = $GLOBAL_BASE."/system/images/slwhite_";
$CONFIG["graphics2"]["slwhite_suffix"] = ".gif";
$CONFIG["graphics2"]["sdblack_prefix"] = $GLOBAL_BASE."/system/images/sdblack_";
$CONFIG["graphics2"]["sdblack_suffix"] = ".gif";
$CONFIG["graphics2"]["sdwhite_prefix"] = $GLOBAL_BASE."/system/images/sdwhite_";
$CONFIG["graphics2"]["sdwhite_suffix"] = ".gif";
$CONFIG["graphics2"]["snone_prefix"]   = $GLOBAL_BASE."/system/images/snone_";
$CONFIG["graphics2"]["snone_suffix"]   = ".gif";
$CONFIG["graphics2"]["snopoint_prefix"] = $GLOBAL_BASE."/system/images/snopoint_";
$CONFIG["graphics2"]["snopoint_suffix"] = ".gif";
$CONFIG["graphics2"]["sptblack_prefix"] = $GLOBAL_BASE."/system/images/sptblack_";
$CONFIG["graphics2"]["sptblack_suffix"] = ".gif";
$CONFIG["graphics2"]["sptwhite_prefix"] = $GLOBAL_BASE."/system/images/sptwhite_";
$CONFIG["graphics2"]["sptwhite_suffix"] = ".gif";
$CONFIG["graphics2"]["stone_reduction"] = $CONFIG["graphics"]["stone_reduction"];

$CONFIG["graphics2"]["shadow_width"][9]    = 0; // shadow offset
$CONFIG["graphics2"]["shadow_offsetX"][9]  = 0; // shadow offset
$CONFIG["graphics2"]["shadow_offsetY"][9]  = 0; // shadow offset
$CONFIG["graphics2"]["shadow_width"][13]   = 0; // shadow offset
$CONFIG["graphics2"]["shadow_offsetX"][13] = 0; // shadow offset
$CONFIG["graphics2"]["shadow_offsetY"][13] = 0; // shadow offset
$CONFIG["graphics2"]["shadow_width"][19]   = 0; // shadow offset
$CONFIG["graphics2"]["shadow_offsetX"][19] = 0; // shadow offset
$CONFIG["graphics2"]["shadow_offsetY"][19] = 0; // shadow offset

$CONFIG["rss"]["file_all"] = $GLOBAL_BASE."/".$GLOBAL_DATA."/rss/go.rss";
$CONFIG["rss"]["file_black"] = $GLOBAL_BASE."/".$GLOBAL_DATA."/rss/go_black.rss";
$CONFIG["rss"]["file_white"] = $GLOBAL_BASE."/".$GLOBAL_DATA."/rss/go_white.rss";
$CONFIG["rss"]["url"]  = "http://".$_SERVER["HTTP_HOST"].dirname(dirname($_SERVER["SCRIPT_NAME"]))."/".$GLOBAL_DATA; // not nice

// override with room config
if(file_exists($GLOBAL_BASE."/".$GLOBAL_DATA."/config.php")) {
  // include in case of reread
  include($GLOBAL_BASE."/".$GLOBAL_DATA."/config.php");
}

?>
