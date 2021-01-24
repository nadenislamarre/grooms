<?php
$GLOBAL_BASE = "..";
require_once($GLOBAL_BASE."/system/system.php");
session_set_cookie_params($SYSTEM["session_duration"]);
session_start();

require_once($GLOBAL_BASE."/system/include/helpers.php");
setGlobalData($GLOBAL_BASE);

require_once($GLOBAL_BASE."/system/config.php");
require_once($GLOBAL_BASE."/system/include/db.php");

$dbfile = choose_db_file(html_getParameter("file"),
			 $CONFIG["db"]["db_path"], $CONFIG["backups"]["db_backup_directory"],
			 $CONFIG["backups"]["db_backup_prefix"]);
$game = db_read($dbfile, $CONFIG["game"]["handicapInit"], $CONFIG["game"]["komiInit"], $CONFIG["game"]["stepsInit"], -1, $all_moves);

header('Content-type: application/sgf');
header('Content-Disposition: attachment; filename="'.$game["partykey"].'.sgf"');

echo "(\n";
echo ";FF[4]CA[UTF-8]SZ[".($game["steps"]+1)."]";
if($game["handicap"] > 0) {
  echo "HA[".($game["handicap"]+1)."]";
}
echo "KM[".$game["komi"]."]GN[".$game["partykey"]."]\n";

function html2sgfcomment($str) {
  return
    str_replace("]", "\]",
		str_replace("\\", "\\\\",
			    str_replace("<br/>", "\n",
					str_replace("&amp;", "&",
						    str_replace("&nbsp;", " ",
								str_replace("&lt;", "<",
									    str_replace("&gt;", ">",
											str_replace("&quot;", "\"",
												    $str))))))));
}

function comment2sgf($ctab) {
  $str = "";
  foreach($ctab AS $n => $comment) {
    if($str != "") {
      $str .= "\n";
    }
    $str .= html2sgfcomment($comment["author"]).": ".html2sgfcomment($comment["comment"]);
  }
  return $str;
}

// first comment is all comments until the handicap
$first_comment = "";
$first_comment_end = $game["handicap"] == 0 ? 0 : $game["handicap"]+1;
for($i=0; $i<=$first_comment_end; $i++) {
  if(isset($game["comments"][$i])) {
    if($first_comment != "") {
      $first_comment .= "\n";
    }
    $first_comment .= comment2sgf($game["comments"][$i]);
  }
}
echo "C[".$first_comment."]";

$color = "B";
$endwithline = true;
foreach($all_moves AS $n => $actions) {
  $move = $actions["move"];

  $move_az = ""; // passing move
  if(count($move) > 1) {
    $move_az = chr(ord('a')+$move[1]).chr(ord('a')+$move[2]);
  }

  if($n <= $game["handicap"] && $game["handicap"] > 0 /* not considered as an handicap move */ ) {
    // setup
    if($n == 0) {
      echo "AB";
    }
    if($move_az != "") {
      echo "[".$move_az."]";
    }
    if($n == $game["handicap"]) {
      echo "\n";
      $color = "W";
    }
  } else {
    // game
    echo ";".$color."[".$move_az."]";
    if(isset($game["comments"][$n+1])) {
      echo "C[".comment2sgf($game["comments"][$n+1])."]";
    }
    if(($n+1 - $game["handicap"]) %15 == 0) {
      $endwithline = true;
      echo "\n";
    } else {
      $endwithline = false;
    }

    // change the color
    $color = $color == "B" ? "W" : "B";
  }
}

if($endwithline == false) {
  echo "\n";
}
echo ")\n";

?>
