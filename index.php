<?php
// super ko
// links : export sgf/db link / backup ; faq : How do I change the password of my room ? ; faq : shared nfs ?
// rename/delete an old game
// simulations of history / stop simulate : history back to the same point / simulate old games / prisonniers / last stone / auto eat
// view in a different color recent comments / lanscape mode
// disable alert via options
// conditionnal move
?>
<?php
$GROOMS_VERSION="1.0.9";

# Contributors
# Nicolas Adenis-Lamarre (Game author)
# Mushi (theme)
# Stones and bord (jGoBoard team)
# Joshua I. Miller (Gettext)

$GLOBAL_BASE = ".";

require_once($GLOBAL_BASE."/system/system.php");
session_set_cookie_params($SYSTEM["session_duration"]);
session_start();
require_once($GLOBAL_BASE."/system/include/admin.php");
require_once($GLOBAL_BASE."/system/include/helpers.php");
require_once($GLOBAL_BASE."/system/include/locale.php");

nocacheHeader();

locale_changeAction(html_getParameter("lang"));

function loadDummyRoomConfig() {
  global $GLOBAL_BASE;
  global $GLOBAL_DATA;

  $current_GLOBAL_BASE = $GLOBAL_BASE; // backup

  $GLOBAL_BASE = ".";
  $GLOBAL_DATA = "dummy";
  require_once("system/config.php");
  $DUMMY_CONFIG=$CONFIG;
  unset($CONFIG);
  unset($GLOBAL_BASE);
  unset($GLOBAL_DATA);

  $GLOBAL_BASE = $current_GLOBAL_BASE; // restore

  return $DUMMY_CONFIG;
}
$DUMMY_CONFIG = loadDummyRoomConfig();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Grooms</title>
    <link rel="stylesheet" href="system/go.css" />
<?php locale_head(".", $_SERVER["HTTP_ACCEPT_LANGUAGE"]); ?>
</head>
<body>

<header>
<?php locale_bar(".", "index.php", "", true, $_SERVER["HTTP_ACCEPT_LANGUAGE"]) ; ?>
    <h1>Grooms</h1>
</header>

<section id="content">

<?php
// actions

$display_all_rooms = false;
$action = html_getParameter("action");

switch($action) {
 case "create_room":
   try {
    if(html_getParameter("password") != html_getParameter("password2")) {
      throw new Exception("Password confirmation mismatches");
    }
    $room = html_getParameter("room");
    admin_create_room(".", $room, html_getParameter("password"));
    $room_js = str_replace("\"", "\\\"", $room); // i know it's not possible, but rules could change
    echo "<div id=\"topMessage\">".js_w("Gettext.strargs(_(\"Join the room at <a href=\\\"%1\\\">%2</a>\"), [\"".$room_js."\", \"".$room_js."\"])")."</div>\n";
   } catch(Exception $e) {
     echo "<div id=\"topMessage\">".js_w("_(\"Error :\") + \" \" + _(\"".str_replace("\"", "\\\"", $e->getMessage())."\")")."</div>\n";
   }
   break;

 case "all_rooms":
   $display_all_rooms = true;
   break;
}
?>

<div id="information">
    <div id="picture"><img src="system/homepage/home.jpg"></div>

    <div id="rooms">
  <h2><?php echo js_w("_(\"Last updated rooms\")"); ?></h2>
<?php
$rooms = room_lastUpdated(".", basename($DUMMY_CONFIG["db"]["db_path"]));
$nmax = 10;
$nmaxinit = $nmax;
$gmtime = gmmktime();
echo "        <ul>\n";
foreach($rooms AS $n => $room_info) {
  if($nmax > 0 || $display_all_rooms) {
    echo "            <li><a href=\"".$room_info["room"]."\">".$room_info["room"]."</a> (".delta_time_html($room_info["stamp"], $gmtime, true).")</li>\n";
  }
  $nmax--;
}
echo "        </ul>\n";
if(count($rooms) > $nmaxinit) {
  if($display_all_rooms == false) {
    echo "<div id=\"show_all_rooms\"><a href=\"index.php?action=all_rooms\">".js_w("_(\"more\")")."</a></div>\n";
  } else {
    echo "<div id=\"show_all_rooms\"><a href=\"index.php\">".js_w("_(\"less\")")."</a></div>\n";
  }
}
?>
    </div>


</div>

<div id="description">
    <div id="create">
        <h2><?php echo js_w("_(\"Create your own room\")"); ?></h2>
        <form method="post" action="index.php">
            <input type="hidden" name="action" value="create_room" />
            <label><?php echo js_w("_(\"Room name:\")"); ?> </label><input type="text" name="room" maxlength="16"/>
            <label><?php echo js_w("_(\"Password:\")"); ?> </label><input type="password" name="password" />
            <label><?php echo js_w("_(\"Confirmation:\")"); ?> </label><input type="password" name="password2" />
            <?php echo js_w("\"<input type=\\\"submit\\\" value=\\\"\" + _(\"Create\") + \"\\\">\""); ?>
        </form>
    </div>

    <div id="project">
        <h2><?php echo js_w("_(\"Project\")"); ?></h2>
        <p><?php echo js_w("_(\"Grooms (Go Rooms) is a <a href=\\\"system/COPYING\\\">GPL</a> project to allow you to play the go game over internet with your friends in a simple way in real time or over severals days with your computer, your phone or your tablet.\")"); ?></p>
    </div>

    <div id="features">
        <h2><?php echo js_w("_(\"Features\")"); ?></h2>
        <ul>
            <li><?php echo js_w("_(\"Web game (no software to install)\")"); ?></li>
            <li><?php echo js_w("_(\"Computers, phones, tablets compatible\")"); ?></li>
            <li><?php echo js_w("_(\"Real time or over several days games\")"); ?></li>
            <li><?php echo js_w("_(\"Rss feed (alerts when it's your turn to play)\")"); ?></li>
            <li><?php echo js_w("_(\"Watch other people playing\")"); ?></li>
            <li><?php echo js_w("_(\"Comments of serevals people\")"); ?></li>
            <li><?php echo js_w("_(\"Simulation mode\")"); ?></li>
            <li><?php echo js_w("_(\"Old games consultation\")"); ?></li>
        </ul>
    </div>

    <div id="faq">
        <h2><a href="system/faq.php" target="faq"><?php echo js_w("_(\"FAQ\")"); ?></a></h2>
    </div>

</div>

</section>


<div class="clear"></div>

<footer>
    <p>Grooms <?php echo $GROOMS_VERSION; ?>
<?php
$PKG_FILE="grooms-".$GROOMS_VERSION.".zip";

if(file_exists($PKG_FILE)) {
  echo "&nbsp;-&nbsp;<a href=\"grooms-".$GROOMS_VERSION.".zip\">grooms-".$GROOMS_VERSION.".zip</a>&nbsp;-&nbsp;\n";
} else {
  echo "&nbsp;-&nbsp;<a href=\"http://grooms.tuxfamily.org/\">";
  echo js_w("_(\"Grooms website\")");
  echo "</a>&nbsp;-&nbsp;\n";
}
?>
    <a href="http://svn.tuxfamily.org/viewvc.cgi/grooms_grooms"><?php echo js_w("_(\"Browse sources\")"); ?></a></p>
    <p><?php echo js_w("_(\"Graphics are under <a href=\\\"http://fr.wikipedia.org/wiki/Licence_Creative_Commons\\\">Creative Commons Licence</a> and come from the <a href=\\\"http://jgoboard.com\\\">jGoBoard</a> project.\")"); ?></p>
    <p><?php echo js_w("_(\"For any question, remark or contribution : <u>nicolas dot adenis dot lamarre at gmail dot com</u>\")"); ?></p>
</footer>

</body>
</html>
