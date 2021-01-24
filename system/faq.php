<?php
$GLOBAL_BASE = "..";
require_once($GLOBAL_BASE."/system/system.php");
session_set_cookie_params($SYSTEM["session_duration"]);
session_start();
require_once($GLOBAL_BASE."/system/include/locale.php");
require_once($GLOBAL_BASE."/system/include/helpers.php");

locale_changeAction(html_getParameter("lang"));

?>
<DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Grooms FAQ</title>
    <link rel="stylesheet" type="text/css" href="go.css" />
    <style>
.faq_q {
 margin-top: 1em;
 font-size: 1.1em;
 font-weight: bold;
}
.faq_a {
 margin-left: 1em;
 margin-top: 0.3em;
}
    </style>
<?php locale_head($GLOBAL_BASE, $_SERVER["HTTP_ACCEPT_LANGUAGE"]); ?>
</head>
<body>

<header>
    <?php locale_bar($GLOBAL_BASE, "faq.php") ; ?>
    <h1>Grooms FAQ</h1>
</header>

<section id="content">

    <div id="faq">
        <ul>

        <li><div class="faq_q"><?php echo js_w("_(\"How do I cancel a move ?\")"); ?></div>
        <div class="faq_a"><?php echo js_w("_(\"Clic on the last played stone to cancel it.\")"); ?></div></li>

        <li><div class="faq_q"><?php echo js_w("_(\"When I clic on 'Count points', dead stones are not marked. How does it work ?\")"); ?></div>
        <div class="faq_a"><?php echo js_w("_(\"Determine the living/dead stones to count points is not always possible. When you clic on 'Count points' you have to clic on dead stones groups to mark them as dead otherwise, they are considered as living. If you clic on a dead group, it becomes living again.\")"); ?></div></li>

        <li><div class="faq_q"><?php echo js_w("_(\"How can I be alerted when it's my turn to play ?\")"); ?></div>
        <div class="faq_a"><?php echo js_w("_(\"In the directory http://&lt;server&gt;/&lt;room name&gt;/rss, you can find 3 files. go.rss is updated at each turn. go_black.rss is updated when something is interesting for blacks. go_white.rss is updated when something is interesing for whites. Rss softwares check these files regularly and inform you when something happend. For example, if you install \\\"My News Alerts\\\" on your Android phone and ask it to check for the file go_black.rss of your room, your phone will ring each time it's black's turn.\")"); ?></div></li>

        <li><div class="faq_q"><?php echo js_w("_(\"How do I translate the game ?\")"); ?></div>
        <div class="faq_a"><?php echo js_w("_(\"Just fill the file <a href=\\\"LC_MESSAGES/grooms.pot\\\">grooms.pot</a> and send it by email to nicolas dot adenis dot lamarre at gmail dot com. You can take examples on <a href=\\\"LC_MESSAGES/fr/grooms.po\\\">the french grooms.po file</a>. If you have any question to fill this file, don't hesitate to email too.\")"); ?></div></li>

        <li><div class="faq_q"><?php echo js_w("_(\"How do I create my own Grooms server ?\")"); ?></div>
        <div class="faq_a"><?php echo js_w("_(\"Prerequisites are just apache+php. Download the grooms zip file, and unzip it anywhere you apache server is configured for. The link http://&lt;server&gt;/system/check.php?room=&lt;room name&gt; can help you to fix some permission issues. You should configure apache to prevent .db files to be downloaded (the .htaccess delivered will do that automatically while it's configured to be used on most provider systems)\")"); ?></div></li>

       </ul>
    </div>

</section>

</body>
</html>
