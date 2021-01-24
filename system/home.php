<?php
function home($base, $data) {
  global $GLOBAL_BASE;
  global $GLOBAL_DATA;

  $GLOBAL_BASE = $base;
  $GLOBAL_DATA = $data;

  require_once($GLOBAL_BASE."/system/system.php");
  session_set_cookie_params($SYSTEM["session_duration"]);
  session_start();

  require_once($GLOBAL_BASE."/system/config.php");
  require_once($GLOBAL_BASE."/system/include/session.php");
  require_once($GLOBAL_BASE."/system/include/helpers.php");
  require_once($GLOBAL_BASE."/system/include/db.php");
  require_once($GLOBAL_BASE."/system/include/locale.php");

  nocacheHeader();

  locale_changeAction(html_getParameter("lang"));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Grooms</title>
<?php locale_head($GLOBAL_BASE, $_SERVER["HTTP_ACCEPT_LANGUAGE"]); ?>
<?php
echo "<script type=\"text/javascript\" src=\"".$GLOBAL_BASE."/system/jquery-1.7.2.min.js\"></script>\n";
echo "<script type=\"text/javascript\" src=\"".$GLOBAL_BASE."/system/go_html.js\"></script>\n";
echo "<script type=\"text/javascript\" src=\"".$GLOBAL_BASE."/system/go_actions.js\"></script>\n";
echo "<script type=\"text/javascript\" src=\"".$GLOBAL_BASE."/system/go_game.js\"></script>\n";
echo "<script type=\"text/javascript\" src=\"".$GLOBAL_BASE."/system/go.js\"></script>\n";
echo "<script type=\"text/javascript\" src=\"".$GLOBAL_BASE."/system/helpers.js\"></script>\n";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$GLOBAL_BASE."/system/go.css\" />\n";

echo "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"RSS - Moves\" href=\"".$CONFIG["rss"]["file_all"]."\" />\n";
echo "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"RSS - Alerts for blacks\" href=\"".$CONFIG["rss"]["file_black"]."\" />\n";
echo "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"RSS - Alerts for whites\" href=\"".$CONFIG["rss"]["file_white"]."\" />\n";
?>
</head>

<body>
<div id="error_bar"></div>
<div id="board_login" class="action_bar login_form"></div>
<div id="board" class="board"></div>
<div id="board_scores" class="scores_bar"></div>
<div id="board_infos" class="infos_bar"></div>
<div id="board_history" class="history_bar"></div>
<div id="board_controls" class="action_bar"></div>
<div id="board_areas" class="board_areas"></div>
<div id="board_comments" class="board_comments"></div>
<audio id="board_audio_player" ></audio>
<script type="text/javascript">
$(document).ready(function() {
<?php
 echo "go_init(\"board\", \"board_loginbar\", \"board_login\", \"board_infos\", \"board_controls\", \"board_areas\", \"board_comments\", \"board_scores\", \"board_history\", \"board_audio_player\", \"".$GLOBAL_BASE."/system/sounds\", \"".$GLOBAL_DATA."\", \"".$GLOBAL_BASE."/system/board.php\", \"".$GLOBAL_BASE."/system/status.php\", \"".$GLOBAL_BASE."/system/oldgames.php\", \"".$GLOBAL_BASE."/system/faq.php\", \"".$GLOBAL_BASE."/system/admin.php\")\n";
?>
});
</script>
<?php locale_bar($GLOBAL_BASE, "index.php") ; ?>
</body>
</html>
<?php
}
?>
