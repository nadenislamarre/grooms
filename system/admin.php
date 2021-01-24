<?php
$GLOBAL_BASE = "..";
require_once($GLOBAL_BASE."/system/system.php");
session_set_cookie_params($SYSTEM["session_duration"]);
session_start();

require_once($GLOBAL_BASE."/system/include/helpers.php");
setGlobalData($GLOBAL_BASE);

require_once($GLOBAL_BASE."/system/config.php");
require_once($GLOBAL_BASE."/system/include/session.php");
require_once($GLOBAL_BASE."/system/include/admin.php");
require_once($GLOBAL_BASE."/system/include/locale.php");

nocacheHeader();

locale_changeAction(html_getParameter("lang"));

if(session_isLogged($GLOBAL_DATA) == false) {throw new Exception("Right error");}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Grooms - configuration</title>
<?php
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$GLOBAL_BASE."/system/go.css\" />\n";
?>
<?php locale_head($GLOBAL_BASE, $_SERVER["HTTP_ACCEPT_LANGUAGE"]); ?>
</head>
<body>
<?php locale_bar($GLOBAL_BASE, "admin.php", "room=".html_getParameter("room")) ; ?>

<h1>Configuration</h1>

<?php
$action = html_getParameter("action");
switch($action) {
 case "configure":
   $password = html_getParameter("password");
   if($password != html_getParameter("password2")) {
     echo js_w("_(\"Password confirmation mismatches. Password not saved.\")")."\n";
     $password = "";
   }

   try {
     admin_configure_room($GLOBAL_BASE, html_getParameter("room"),
			  html_getParameter("password"), $CONFIG["password"],
			  html_getParameter("show_times_if_not_connected") == 1 ? 1 : 0,
                          html_getParameter("show_comments_if_not_connected") == 1 ? 1 : 0);
     echo js_w("_(\"Configuration saved.\")");
     // reread configuration
     include($GLOBAL_BASE."/system/config.php");
   } catch(Exception $e) {
     echo "<div class=\"warning\">".js_w("_(\"Unable to save the configuration.\") + \" (\" + _(\"".str_replace("\"", "\\\"", $e->getMessage())."\") + \")\"")."</div>";
   }
   break;
}
?>

<div id="configure">
    <h2><?php echo $GLOBAL_DATA; ?></h2>
    <form method="post" action="admin.php" id="configure_options">
        <?php echo "<input type=\"hidden\" name=\"room\" value=\"".$GLOBAL_DATA."\" />"; ?>
        <input type="hidden" name="action" value="configure" />

        <div class="form_left">
<?php
  if($CONFIG["show_times_if_not_connected"]) {
    echo "<input type=\"checkbox\" value=\"1\" name=\"show_times_if_not_connected\" checked=\"checked\" />\n";
  } else {
    echo "<input type=\"checkbox\" value=\"1\" name=\"show_times_if_not_connected\" />\n";
  }
?>
        </div><div class="form_right"><label><?php echo js_w("_(\"Show times in history for unconnected people\")"); ?></label></div>

        <div class="form_left">
<?php
  if($CONFIG["show_comments_if_not_connected"]) {
    echo "<input type=\"checkbox\" value=\"1\" name=\"show_comments_if_not_connected\" checked=\"checked\" />\n";
  } else {
    echo "<input type=\"checkbox\" value=\"1\" name=\"show_comments_if_not_connected\" />\n";
  }
?>
        </div><div class="form_right"><label><?php echo js_w("_(\"Show comments for unconnected people\")"); ?></label></div>

        <div class="form_left"><label><?php echo js_w("_(\"Password:\")"); ?> </label></div><div class="form_right"><input type="password" name="password" /></div>
        <div class="form_left"><label><?php echo js_w("_(\"Confirmation:\")"); ?> </label></div><div class="form_right"><input type="password" name="password2" /></div>
        <br />
        <?php echo js_w("_(\"Keep password empty to not update it.\")")."\n"; ?>
	<br />
	<?php echo js_w("\"<input type=\\\"submit\\\" value=\\\"\" + _(\"Save\")   + \"\\\"> \"")."\n"; ?>
    </form>
</div>
</body>
</html>
