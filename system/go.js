function g_loadBoard(f_complete) {
 game_loadBoard(g_loadSuccessfull, f_complete, document.go.global_go_url, document.go.global_go_room, document.go.global_go_file, document.go.global_mode)
}

function html_getId_oldGameFiles(old_games_area_id) {
 return old_games_area_id+'_old_games_files'
}

function html_getSpanId_oldGameFiles(old_games_area_id) {
 return "span_"+html_getId_oldGameFiles(old_games_area_id)
}

function g_resetLastActionTime(isRealAction) {
 document.go.global_last_action_played = document.go.game["action_num"]
 document.go.global_last_action_time = getCurrentTime()
 if(isRealAction) { // reset sound only on real action
  document.go.global_last_action_sound_played = false // to be able to play the sound only one time for spectators
 }
}

function g_getLastActionDuration() {
 return getCurrentTime() - document.go.global_last_action_time
}

function g_loadBoard_actions(access, mode, last_move) {
 html_showBar(document.go.global_board_loginbar_id, access != "guest")
 html_loadControlsBar(document.go.global_go_room, document.go.global_go_file, access, mode, last_move, document.go.global_board_controls_id, document.go.global_board_areas_id, document.go.global_areas, document.go.global_board_comments_id, document.go.global_oldgames_url)
 html_showAreas(document.go.global_areas, access, mode)
}

function g_map_points(objimg_id, x, y) {
 g_board_action_standard(document.go.game["action_url"]+"?room="+document.go.global_go_room+"&action=count&x="+x+"&y="+y)
}

function g_next_color() {
 return document.go.game["next_color"]
}

function g_map_play(objimg_id, x, y) {
 objimg = document.getElementById(objimg_id)

 document.go.global_boardStones[x][y] = 'U' // on phones, the onMouseOut doesn't operate, so reset this key on invalid moves
 g_board_action_standard(document.go.game["play_url"]+"?room="+document.go.global_go_room+"&n="+document.go.game["action_num"]+"&x="+x+"&y="+y)
}

function getCurrentTime() {
 var current_date = new Date()
 return current_date.getTime() / 1000 // number of seconds
}

function go_init(board_id, board_loginbar_id, board_login_id, board_infos_id, board_controls_id, board_areas_id, board_comments_id, board_scores_id, board_history_id, board_audio_player_id, sounds_directory, go_room, go_url, status_url, oldgames_url, faq_url, admin_url) {
 board_obj = document.getElementById(board_id)
 board_obj.innerHTML = "<div class=\"loading\">" + _("loading...") + "</div>"

 $.ajaxSetup({
     timeout: 15000,
     cache: false,
     ifModified: false
 });

 document.go      = new Object()
 document.go.game = new Object()
 document.go.global_board_id          = board_id
 document.go.global_stones_id_prefix  = board_id + "_stones_"
 document.go.global_board_login_id    = board_login_id
 document.go.global_board_infos_id    = board_infos_id
 document.go.global_board_controls_id = board_controls_id
 document.go.global_board_areas_id    = board_areas_id
 document.go.global_board_comments_id = board_comments_id
 document.go.global_board_scores_id   = board_scores_id
 document.go.global_board_history_id  = board_history_id
 document.go.global_board_audio_player_id = board_audio_player_id
 document.go.global_sounds_directory  = sounds_directory
 document.go.global_go_room           = go_room
 document.go.global_go_url            = go_url
 document.go.global_faq_url           = faq_url
 document.go.global_admin_url         = admin_url
 document.go.global_status_url        = status_url
 document.go.global_oldgames_url      = oldgames_url+"?room="+go_room
 document.go.global_mode              = "play"
 document.go.last_loaded_mode         = "none"
 document.go.ghistory = new Object()
 document.go.ghistory.status  = -1
 document.go.global_go_file = ""
 document.go.global_go_ntimeout = 0
 document.go.global_timeouts = [ [3000, 7], [5000, 30], [10000, 60], [15000, 120], [30000, 200], [120000, 400], [-1, -1] ] // end with -1, meaning that at end, steps infinitly ; 3 seconds, 7 times, 5 secondes, 23 times (from 7 to 30, ...
 document.go.global_watching = false
 document.go.global_zoom = 1.0
 g_resetLastActionTime(true)
 document.go.global_last_action_time_timeout = 120 // play a sound after x seconds of inactivity in case of new action

 // areas
 document.go.global_areas = new Object()
 document.go.global_areas.comment   = { id:"comment_area",   visible:false }
 document.go.global_areas.reset     = { id:"reset_area",     visible:false }
 document.go.global_areas.old_games = { id:"old_games_area", visible:false }
 html_loadAreas(document.go.global_board_areas_id, document.go.global_areas, document.go.global_go_room, "g_board_action_comment", "g_board_action_reset", "g_board_action_old_game")

 // load bars
 document.go.global_board_loginbar_id = board_loginbar_id
 html_loadLoginBar(document.go.global_board_loginbar_id, document.go.global_board_login_id, "g_board_action_login", document.go.global_faq_url)

 $(window).resize(function() {
  g_resetLastActionTime(false)
  g_loadBoard(do_nothing)
 });

 g_setWatchBoard(true)
}

function g_playSound(soundFile) {
 player = document.getElementById(document.go.global_board_audio_player_id)
 if(player) {
  if(player.setAttribute) { // not supported by every browser
   player.setAttribute('src', document.go.global_sounds_directory+'/'+soundFile)
   if(player.play) {
    player.play()
   }
  }
 }
}

function g_initZoom(graphics) {
 pre_zoom  = document.go.global_zoom
 post_zoom = getRequiredZoom(graphics, 0.95, 0.6, 1.1)
 min_zoom_change = 0.02

 // don't change zoom if it doesn't really change (because some browser run resize on focussing and change 1 pixel (phones)
 if(Math.abs(pre_zoom - post_zoom) < min_zoom_change) {
  return false
 }

 // return true if zoom changed
 document.go.global_zoom = post_zoom
 return pre_zoom != post_zoom
}

function g_startOrStopWatchBoard() {
 if(document.go.global_go_file == "" && document.go.ghistory.status == -1 && document.go.global_mode != "simulate") {
  g_setWatchBoard(false)
 } else {
  g_stopWatchBoard()
 }
}

function g_markPlayAndStartOrStopWatchBoard() {
 g_startOrStopWatchBoard()
 g_resetLastActionTime(true)
}

function g_stopWatchBoard() {
 document.go.global_watching = false
}

function g_setWatchBoard(isARealAction) {
 if(document.go.global_watching) {
  return // already watching
 }
 if(document.go.global_go_file != "") {
  return // never watch on this condition
 }

 document.go.global_watching = true

 g_loadBoard(function() {
               if(isARealAction) {
                 g_resetLastActionTime(true)
               }
               setTimeout("g_watchBoard()", document.go.global_timeouts[0][0])
            })
}

function g_loadSuccessfull(access, game, stones, points, scores, comments, graphics) {
 stones_id_prefix = document.go.global_stones_id_prefix

 force_reload_zoom = false
 if(g_initZoom(graphics)) {
  force_reload_zoom = true
 }
 graphics["width"]          *= document.go.global_zoom
 graphics["letters_offset"] *= document.go.global_zoom
 graphics["shadow_offsetX"] *= document.go.global_zoom
 graphics["shadow_offsetY"] *= document.go.global_zoom
 graphics["shadow_width"]   *= document.go.global_zoom

 // delta mode ?
 delta_mode = (document.go.access           == access &&
               document.go.game["key"]      == game["key"] &&
               document.go.last_loaded_mode == document.go.global_mode &&
               document.go.ghistory.status  == -1 &&
               force_reload_zoom            == false)

 // reset history
 if(document.go.ghistory.status != -1) {
  document.go.ghistory.status = -1
  html_updateBoard_play_events(game["steps"], false, stones, stones, access, game["last_move"], game["last_move"], stones_id_prefix, graphics, "g_map_play", "g_next_color")
 }

 // reload the board
 switch(document.go.global_mode) {
  case 'play':
   if(delta_mode == false) {
    html_makeEmptyBoard(graphics, document.go.global_board_id, game["steps"], stones_id_prefix)
   }
   html_hideScoresBar(document.getElementById(document.go.global_board_scores_id))
   html_updateBoard_play(game["steps"], delta_mode, document.go.global_boardStones, stones, access, document.go.game["last_move"], game["last_move"], stones_id_prefix, graphics, "g_map_play", "g_next_color")
   html_loadHistoryBar(document.getElementById(document.go.global_board_history_id),
                       document.go.global_go_file,
                       game["nb_played_stones"]+game["nb_passed_stones"],
                       "g_history_action_backwardall", "g_history_action_backward",
                       "g_history_action_forwardall", "g_history_action_forward")
    html_loadInfosBar_play(document.getElementById(document.go.global_board_infos_id), game)
   break;
  case 'count':
   html_hideHistoryBar(document.getElementById(document.go.global_board_history_id))
   html_hideInfosBar(document.getElementById(document.go.global_board_infos_id))
   html_makeScoresBoard(game["steps"], stones_id_prefix, stones, points, access, graphics, "g_map_points")
   html_loadScoresBar(document.getElementById(document.go.global_board_scores_id), scores, graphics)
   break;
 }

 // reload actions
 g_loadBoard_actions(access, document.go.global_mode, game["last_move"])

 // load comments
 html_loadComments(document.getElementById(document.go.global_board_comments_id),
                   game["action_num"], comments)

 // erase saved data
 document.go.access   = access
 document.go.game     = game
 document.go.global_boardStones = stones
 document.go.graphics = graphics

 document.go.last_loaded_mode = document.go.global_mode
}

function g_checkAndPlaySound() {
 // play a sound if needed
 if(document.go.global_mode == 'play' &&
  g_getLastActionDuration() > document.go.global_last_action_time_timeout /* more than x secondes happend */ &&
  document.go.global_last_action_sound_played == false &&
  document.go.global_last_action_played != document.go.game["action_num"]
 )  {
  document.go.global_last_action_sound_played = true
  g_playSound("event.ogg")
 }
}

function g_watchBoard() {
 if(document.go.global_watching) {
  $.get(document.go.global_status_url+"?room="+document.go.global_go_room+"&mode="+document.go.global_mode,
   function(data) {
    if(data != document.go.game["status"]) {
     document.go.global_go_ntimeout = 0
     g_loadBoard(function() { g_checkAndPlaySound(); setTimeout("g_watchBoard()", document.go.global_timeouts[0][0])})
    } else {
     // no change
     document.go.global_go_ntimeout++
     an = 0
     while(document.go.global_go_ntimeout > document.go.global_timeouts[an][1] && document.go.global_timeouts[an][1] > 0 && an < document.go.global_timeouts.length-1) {
      an++
     }
     if(document.go.global_timeouts[an][0] > 0) {
      setTimeout("g_watchBoard()", document.go.global_timeouts[an][0])
     } else {
      // no more checks
     }
    }
   }
  ).error(function() { setTimeout("g_watchBoard()", document.go.global_timeouts[0][0]) })
   .complete(g_checkAndPlaySound)
 }
}

// actions
function g_board_action_login(password) {
 g_clear_error() // hide error when login
 g_board_action_standard(document.go.game["action_url"]+"?room="+document.go.global_go_room+"&action=login&password="+password)
}

function g_board_action_logout() {
 g_board_action_standard(document.go.game["action_url"]+"?room="+document.go.global_go_room+"&action=logout")
}

function g_board_action_reset(boardsize, handicap, komi) {
 g_board_action_standard(document.go.game["action_url"]+"?room="+document.go.global_go_room+"&action=reset&board_size="+boardsize+"&handicap="+handicap+"&komi="+komi)
 rst_elt = document.getElementById("showHide_reset")
 if(rst_elt != null) {
  g_showHideResetArea(rst_elt)
 }
}

function g_board_action_comment() {
 board_action_comment(document.go.game["action_url"], document.go.global_board_comments_id, document.go.global_areas)
}

function g_board_action_pass() {
 g_board_action_standard(document.go.game["action_url"]+"?room="+document.go.global_go_room+"&action=pass&n="+document.go.game["action_num"])
}

function g_board_action_cancelpass() {
 g_board_action_standard(document.go.game["action_url"]+"?room="+document.go.global_go_room+"&action=cancelpass&n="+document.go.game["action_num"])
}

function g_board_action_count() {
 g_resetLastActionTime(false)
 document.go.global_mode = "count"
 g_loadBoard(g_startOrStopWatchBoard)
}

function g_board_action_resetcount() {
 g_board_action_standard(document.go.game["action_url"]+"?room="+document.go.global_go_room+"&action=resetcount")
}

function g_board_action_continue(go_file) {
 g_resetLastActionTime(false)
 document.go.global_mode = "play"
 document.go.global_go_file = go_file
 g_loadBoard(g_startOrStopWatchBoard)
}

function g_board_action_standard(action_url) {
 $.get(action_url,
 function(data) {
  if(data == "success") {
   g_loadBoard(g_markPlayAndStartOrStopWatchBoard)
  } else {
   g_board_on_error(_(data))
  }
 })
 .error(function() {
  g_board_on_error(_("Connexion failed"))
 })
}

function g_board_action_postStandard(action_url, form_id) {
 $.post(action_url, $("#"+form_id).serialize(),
 function(data) {
  if(data == "success") {
   g_loadBoard(g_markPlayAndStartOrStopWatchBoard)
  } else {
   g_board_on_error(_(data))
  }
 })
 .error(function() {
  g_board_on_error(_("Connexion failed"))
 })
}

function g_board_on_error(message) {
 g_board_action_logout() // logout on error
 g_display_error(message)
}

function g_clear_error() {
 msg_id = document.getElementById("error_bar")
 if(msg_id != null) {
  msg_id.innerHTML = ""
  msg_id.style.display = "none"
 }
}

function g_display_error(message) {
 msg_id = document.getElementById("error_bar")
 if(msg_id != null) {
  msg_id.style.display = "block"
  msg_id.innerHTML = "" // because of a bug on android (2.3.x, fixed in 4.x) browser, it must be done in 2 times
  msg_id.innerHTML = _("Error :") + " " + message
 }
}

function g_showHideCommentArea(obj_input) {
 showHideId(obj_input, document.go.global_areas.comment.id, _('Comment'), _('Hide commenting'))
 layer = document.getElementById(document.go.global_areas.comment.id)
 document.go.global_areas.comment.visible = layer.style.display == 'block'
}

function g_showHideResetArea(obj_input) {
 showHideId(obj_input, document.go.global_areas.reset.id, _('Reset game'), _('Hide game resetting'))
 layer = document.getElementById(document.go.global_areas.reset.id)
 document.go.global_areas.reset.visible = layer.style.display == 'block'
}

function g_showHideOldGamesArea(obj_input) {
 if(showHideId(obj_input, document.go.global_areas.old_games.id, _('Old games'), _('Hide old games'))) {
  old_games_file_spanid = html_getSpanId_oldGameFiles(document.go.global_areas.old_games.id)
  old_games_file_id     = html_getId_oldGameFiles(document.go.global_areas.old_games.id)
  updateOldGames(document.go.global_oldgames_url, document.getElementById(old_games_file_spanid), old_games_file_id)
 }
 layer = document.getElementById(document.go.global_areas.old_games.id)
 document.go.global_areas.old_games.visible = layer.style.display == 'block'
}

function html_loadControlsBar(go_room, go_file, access, mode, last_move, board_controls_id, board_areas_id, areas, board_comment_id, oldgames_url) {
 txt = ""

 // // buttons
 txt += "<div class=\"game_controls\">"

 // comment
 if(access == "rw" && mode == "play") {
  txt += "<form class=\"actiongame_item\" onsubmit='return false'>"
  txt += "<input id=\"showHide_comment\" type=\"button\" value=\"" + (areas.comment.visible ? _('Hide commenting') : _("Comment")) + "\" onclick=\"g_showHideCommentArea(this)\" />";
  txt += "</form>\n";
  txt += " "
 }

 // pass
 if(access == "rw" && mode == "play") {
  txt += "<form class=\"actiongame_item\" onsubmit='return false'>";
  txt += "<input type=\"button\" value=\"" + _("Pass your turn") + "\" onclick=\"g_board_action_pass()\" />";
  txt += "</form>\n";
  txt += " "
 }

 // rollback pass
 if(access == "rw" && mode == "play" && last_move[0] == -1 && last_move[1] == -1) {
  txt += "<form class=\"actiongame_item\" onsubmit='return false'>";
  txt += "<input type=\"button\" value=\"" + _("Cancel pass") + "\" onclick=\"g_board_action_cancelpass()\" />";
  txt += "</form>\n";
  txt += " "
 }

 // simulate
 if(mode == "play" && go_file == "") {
  txt += "<form class=\"actiongame_item\" onsubmit='return false'>";
     txt += "<input type=\"button\" value=\"" + _("Simulate") + "\" onclick=\"g_board_action_simulate_on()\" />";
  txt += "</form>\n";
  txt += " "
 }
 if(mode == "simulate") {
  txt += "<form class=\"actiongame_item\" onsubmit='return false'>";
  txt += "<input type=\"button\" value=\"" + _("Reset the simulation") + "\" onclick=\"g_board_action_simulate_reset()\" />";
  txt += "</form>\n";
  txt += " "

  txt += "<form class=\"actiongame_item\" onsubmit='return false'>";
  txt += "<input type=\"button\" value=\"" + _("Stop the simulation") + "\" onclick=\"g_board_action_simulate_off()\" />";
  txt += "</form>\n";
  txt += " "
 }

 // count point
 switch(mode) {
  case "play":
   txt += "<form class=\"actiongame_item\" onsubmit='return false'>";
   txt += "<input type=\"button\" value=\"" + _("Count points") + "\" onclick=\"g_board_action_count()\" />";
   txt += "</form>\n";
   txt += " "
  break;
  case "count":
   // back to the game
   txt += "<form class=\"actiongame_item\" onsubmit='return false'>";
   txt += "<input type=\"button\" value=\"" + _("Back to the game") + "\" onclick=\"g_board_action_continue('"+go_file+"')\" />";
   txt += "</form>\n";
   txt += " "
   if(access == "rw") {
    // reset counting
    txt += "<form class=\"actiongame_item\" onsubmit='return false'>";
    txt += "<input type=\"button\" value=\"" + _("Reset counting") + "\" onclick=\"g_board_action_resetcount()\" />";
    txt += "</form>\n";
    txt += " "    
   }
  break;
 }

 txt += "</div>"
 txt += "<div class=\"system_controls\">"

 // access to current game
 if(go_file != "") {
   txt += "<form class=\"action_item\" onsubmit='return false'>";
   txt += "<input type=\"button\" value=\"" + _("Current game") + "\" onclick=\"g_board_action_continue('')\" />";
   txt += "</form>\n";
   txt += " "
 }

 // reset
 if(access == "rw" && mode == "play") {
  txt += "<form class=\"action_item\" onsubmit='return false'>"
  txt += "<input id=\"showHide_reset\" type=\"button\" value=\"" + (areas.reset.visible ? _('Hide game resetting') : _("Reset game")) + "\" onclick=\"g_showHideResetArea(this)\" />";
  txt += "</form>"
  txt += " "
 }

 // old games
 if(mode != "simulate") {
  txt += "<form class=\"action_item\" onsubmit='return false'>"
  txt += "<input type=\"button\" value=\"" + (areas.old_games.visible ? _('Hide old games') : _('Old games')) + "\" onclick=\"g_showHideOldGamesArea(this)\" />";
  txt += "</form>"
  txt += " "
 }

 // configuration
 if(access != "guest") {
  txt += "<form class=\"action_item\" target=\"grooms_configuration\" action=\""+document.go.global_admin_url+"\" method=\"get\">"
  txt += "<input type=\"hidden\" name=\"room\" value=\""+document.go.global_go_room+"\" />";
  txt += "<input type=\"submit\" value=\""+_("Configuration")+"\" />";
  txt += "</form>"
  txt += " "
 }

 // logout
 if(access != "guest") {
  txt += "<form class=\"action_item\" onsubmit='return false'>"
  txt += "<input type=\"button\" value=\"" + _("Logout") + "\" onclick=\"g_board_action_logout()\" />"
  txt += "</form>"
  txt += " "
 }

 txt += "</div>"

 document.getElementById(board_controls_id).innerHTML = txt
}

function g_history_action_backwardall() {
 g_resetLastActionTime(false)
 history_action_backwardall(document.go.global_go_room,
                            document.go.global_go_file,
                            document.go.game, document.go.ghistory,
			    document.go.global_board_infos_id, document.go.graphics,
			    document.go.global_stones_id_prefix,
                            g_startOrStopWatchBoard /* stop when going throw history */)
}

function g_history_action_backward(n) {
 g_resetLastActionTime(false)
 history_action_backward(document.go.global_go_room,
                         document.go.global_go_file,
                         document.go.game, document.go.ghistory,
			 document.go.global_board_infos_id, document.go.graphics,
			 document.go.global_stones_id_prefix, n,
                         g_startOrStopWatchBoard /* stop when going throw history */)
}

function g_history_action_forwardall() {
 g_resetLastActionTime(false)
 history_action_forwardall(document.go.global_go_room,
                           document.go.global_go_file,
                           document.go.game, document.go.ghistory,
			   document.go.global_boardStones, document.go.access,
                           document.go.global_board_infos_id, document.go.graphics,
		           document.go.global_stones_id_prefix,
			   "g_map_play", "g_next_color", g_startOrStopWatchBoard)
}

function g_history_action_forward(n) {
 g_resetLastActionTime(false)
 history_action_forward(document.go.global_go_room,
                        document.go.global_go_file,
                        document.go.game, document.go.ghistory,
		        document.go.global_boardStones, document.go.access,
                        document.go.global_board_infos_id, document.go.graphics,
		        document.go.global_stones_id_prefix,
    			"g_map_play", "g_next_color", n, g_startOrStopWatchBoard)
}

function updateOldGames(oldgames_url, old_games_area_obj, old_games_select_obj) {
  $.getJSON(oldgames_url, function(data) {
  txt = "<select id=\""+old_games_select_obj+"\">"
  txt += "<option value=\"\">" + _("Current game") + "</option>"
  $.each(data, function(n, filename) {
   txt += "<option value=\""+filename+"\">"+filename+"</option>"
  })
  txt += "</select>\n"
  old_games_area_obj.innerHTML = txt
 })
}

function g_board_action_old_game(file) {
 g_resetLastActionTime(false)
 document.go.global_go_file = file
 document.go.global_mode = "play"
 g_loadBoard(g_startOrStopWatchBoard)
}

function g_board_action_simulate_on() {
 g_resetLastActionTime(false)
 // remove history
 if(document.go.ghistory.status != -1) {
  g_history_action_forwardall()
 }
 document.go.global_mode = "simulate"
 g_stopWatchBoard()
 document.go.last_loaded_mode = document.go.global_mode
 document.go.simulation = new Object()
 document.go.simulation.next_color = document.go.game["next_color"]
 document.go.simulation.boardStones = $.extend(true, [], document.go.global_boardStones)

 html_hideHistoryBar(document.getElementById(document.go.global_board_history_id))
 html_loadInfosBar_simulate(document.getElementById(document.go.global_board_infos_id), document.go.simulation.next_color)
 html_loadControlsBar(document.go.global_go_room, document.go.global_go_file, document.go.access, document.go.global_mode, document.go.game["last_move"], document.go.global_board_controls_id, document.go.global_areas_controls_id, document.go.global_areas, document.go.global_board_comments_id, document.go.global_oldgames_url)
 html_showAreas(document.go.global_areas, document.go.access, document.go.global_mode)
 html_updateBoard_play_set_events(document.go.game["steps"], "g_map_simulate", graphics,
     "g_simulate_next_color")
}

function g_board_action_simulate_reset() {
 g_resetLastActionTime(false)
 document.go.simulation = new Object()
 document.go.simulation.next_color = document.go.game["next_color"]
 document.go.simulation.boardStones = $.extend(true, [], document.go.global_boardStones)

 html_updateBoard_play_stones(document.go.game["steps"],
                              false, document.go.simulation.boardStones, document.go.simulation.boardStones,
                              document.go.game["last_move"], document.go.game["last_move"], document.go.global_stones_id_prefix, document.go.graphics)
 html_loadInfosBar_simulate(document.getElementById(document.go.global_board_infos_id), document.go.simulation.next_color)
}

function g_board_action_simulate_off() {
 g_resetLastActionTime(false)
 document.go.global_mode = "play"
 g_loadBoard(g_startOrStopWatchBoard)
}

function g_map_simulate(objimg_id, x, y) {
 g_resetLastActionTime(false)
 objimg = document.getElementById(objimg_id)
 switch(document.go.simulation.boardStones[x][y]) {
  case 'B':
   document.go.simulation.boardStones[x][y] = 'X'
  break;
  case 'W':
   document.go.simulation.boardStones[x][y] = 'X'
  break;
  case 'X':
   if(document.go.simulation.next_color == 'B') {
    document.go.simulation.boardStones[x][y] = 'B'
    document.go.simulation.next_color = 'W'
   } else {
    document.go.simulation.boardStones[x][y] = 'W'
    document.go.simulation.next_color = 'B'
   }
  break;
 }

 last_move = new Array(x, y)
 html_updateBoard_play_stones(document.go.game["steps"],
                              false, document.go.simulation.boardStones, document.go.simulation.boardStones,
                              last_move, last_move, document.go.global_stones_id_prefix, document.go.graphics)
 html_loadInfosBar_simulate(document.getElementById(document.go.global_board_infos_id), document.go.simulation.next_color)
}

function g_simulate_next_color(seton, x, y) {
 if(seton && document.go.simulation.boardStones[x][y] == 'X') {
  return document.go.simulation.next_color
 } else {
  return document.go.simulation.boardStones[x][y]
 }
}
