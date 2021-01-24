
// BOARD
function html_getId_val(stones_id_prefix, i, j) {
 return "val_"+stones_id_prefix+i+"_"+j
}

function html_getId_img(stones_id_prefix, i, j, layer) {
 return stones_id_prefix+i+"_"+j+"_"+layer
}

function html_getId_map(stones_id_prefix, i, j) {
 return "area_"+stones_id_prefix+i+"_"+j
}

function html_getEmptyBoard(graphics, steps, stones_id_prefix) {
 step_width = graphics["width"] / (steps+2*graphics["offset"])

  txt = "<img style=\"position: absolute; left: 0px; top: 0px; display: block;\" class=\"board_img\" src=\""+graphics["img_board"]+"\" width=\""+graphics["width"]+"\" />"

 // numbers
 for(i=0; i<=steps; i++) {
  vleft = step_width*(i+graphics["offset"]) - step_width/2
  vtop  = step_width*((-1)+graphics["offset"]) + graphics["letters_offset"]
  txt += "<div class=\"board_letters\" style=\"text-align: center; line-height:"+step_width+"px; width: "+step_width+"px; height: "+step_width+"px; position: absolute; left: "+vleft+"px; top: "+vtop+"px;\">"+(i+1)+"</div>"
 }
 // letters
 for(i=0; i<=steps; i++) {
  vleft = step_width*((-1)+graphics["offset"]) + graphics["letters_offset"]
  vtop  = step_width*(i+graphics["offset"]) - step_width/2
  txt += "<div class=\"board_letters\" style=\"text-align: center; line-height:"+step_width+"px; width: "+step_width+"px; height: "+step_width+"px; position: absolute; left: "+vleft+"px; top: "+vtop+"px;\">"+(String.fromCharCode(65+i))+"</div>"
 }

 for(i=0; i<=steps; i++) {
  for(j=0; j<=steps; j++) {
   vleft = step_width*(i+graphics["offset"]) - step_width/2
   vtop  = step_width*(j+graphics["offset"]) - step_width/2
   map_id = html_getId_map(stones_id_prefix, i, j)
   // texte for scores or information
   txt += "<div id=\""+html_getId_val(stones_id_prefix, i, j)+"\" style=\"text-align: center; line-height:"+step_width+"px; width: "+step_width+"px; height: "+step_width+"px; position: absolute; left: "+vleft+"px; top: "+vtop+"px;\"></div>"
   // image

   txt += "<img id=\""+html_getId_img(stones_id_prefix, i, j, 2)+"\" style=\"z-index:1; position: absolute; left: "+(vleft+(graphics["shadow_offsetX"]))+"px; top: "+(vtop+(graphics["shadow_offsetY"]))+"px;\" src=\""+graphics["img_none"]+"\" border=\"0\" width=\""+graphics["shadow_width"]+"px\" />"
   txt += "<img id=\""+html_getId_img(stones_id_prefix, i, j, 1)+"\" style=\"z-index:2; position: absolute; left: "+vleft+"px; top: "+vtop+"px;\" src=\""+graphics["img_none"]+"\" usemap=\"#"+map_id+"\" border=\"0\" width=\""+step_width+"px\" />"

   // area for clicks
   txt += "<map name=\""+map_id+"\"><area id=\""+map_id+"\" shape='circle' coords=\""+parseInt(step_width/2)+", "+parseInt(step_width/2)+", "+parseInt(step_width/2-2*graphics["stone_reduction"])+"\"></map>"
  }
 }
 return txt
}

function html_makeEmptyBoard(graphics, board_id, steps, stones_id_prefix) {
 board_obj = document.getElementById(board_id)
 board_obj.innerHTML = "" // because of a bug on android (2.3.x, fixed in 4.x) browser, it must be done in 2 times
 board_obj.innerHTML = html_getEmptyBoard(graphics, steps, stones_id_prefix)
 board_obj.style.height = graphics["width"]+"px"
 board_obj.style.width = graphics["width"]+"px"
 board_obj.style.position = "relative"
 board_obj.style.margin = "0px auto"
}

function html_cleanBoard(steps, stones, stones_id_prefix, graphics) {
 for(i=0; i<=steps; i++) {
  for(j=0; j<=steps; j++) {
   objimg = document.getElementById(html_getId_img(stones_id_prefix, i, j, 1))
   objimg.src = graphics["img_none"]
   objimg = document.getElementById(html_getId_img(stones_id_prefix, i, j, 2))
   objimg.src = graphics["img_none"]
   objval = document.getElementById(html_getId_val(stones_id_prefix, i, j))
   objval.innerHTML = ""
   objarea = document.getElementById(html_getId_map(stones_id_prefix, i, j))
   objarea.onclick     = ""
   objarea.onmouseover = ""
   objarea.onmouseout  = ""
  }
 }
}

// TOOLS
function map_rollover(objimg_id, img_src) {
 objimg = document.getElementById(objimg_id)
 objimg.src = img_src
}

function move2str(move) {
 if(move[0] == -1 && move[1] == -1) {
  return _("Pass")
 }
 return (move[0]+1)+(String.fromCharCode(move[1]+65))
}

// SCORES BOARD
function html_makeScoresBoard(steps, stones_id_prefix, stones, points, access, graphics, points_function_name) {
 vcurblack = 1
 vcurwhite = 1
 for(j=0; j<=steps; j++) {
  for(i=0; i<=steps; i++) {
   objimg = document.getElementById(html_getId_img(stones_id_prefix, i, j, 1))
   objval = document.getElementById(html_getId_val(stones_id_prefix, i, j))
   objarea = document.getElementById(html_getId_map(stones_id_prefix, i, j)) 
   vval = ''

   if(access != "rw") {
    objarea.onclick = ""
    objarea.onmouseover = ""
    objarea.onmouseout  = ""
   }

   switch(points[i][j]) {
   case "X":
    if(stones[i][j] == 'X') {
     objimg.src = graphics["img_nopoint"]
     if(access == "rw") {
      objarea.onclick = ""
      objarea.onmouseover = ""
      objarea.onmouseout  = ""
     }
    } else {
     imgsrc = stones[i][j] == "B" ? graphics["img_black"] : graphics["img_white"]
     imgrolloversrc = stones[i][j] == "B" ? graphics["img_dblack"] : graphics["img_dwhite"]
     objimg.src = imgsrc
     if(access == "rw") {
      objarea.onclick = new Function(points_function_name+"(\""+objimg.id+"\", "+i+", "+j+")")
      objarea.onmouseover = new Function("map_rollover(\""+objimg.id+"\", \""+imgrolloversrc+"\")")
      objarea.onmouseout = new Function("map_rollover(\""+objimg.id+"\", \""+imgsrc+"\")")
     }
    }
    break;
    case "DB":
     imgsrc = graphics["img_dblack"]
     imgrolloversrc = stones[i][j] == "B" ? graphics["img_black"] : graphics["img_white"]
     objimg.src = imgsrc
     vval = vcurwhite++
     if(access == "rw") {
      objarea.onclick = new Function(points_function_name+"(\""+objimg.id+"\", "+i+", "+j+")")
      objarea.onmouseover = new Function("map_rollover(\""+objimg.id+"\", \""+imgrolloversrc+"\")")
      objarea.onmouseout = new Function("map_rollover(\""+objimg.id+"\", \""+imgsrc+"\")")
     }
    break;
    case "DW":
     imgsrc = graphics["img_dwhite"]
     imgrolloversrc = stones[i][j] == "B" ? graphics["img_black"] : graphics["img_white"]
     objimg.src = imgsrc
     vval = vcurblack++
     if(access == "rw") {
      objarea.onclick = new Function(points_function_name+"(\""+objimg.id+"\", "+i+", "+j+")")
      objarea.onmouseover = new Function("map_rollover(\""+objimg.id+"\", \""+imgrolloversrc+"\")")
      objarea.onmouseout = new Function("map_rollover(\""+objimg.id+"\", \""+imgsrc+"\")")
     }
    break;
    case "PB":
     objimg.src = graphics["img_ptblack"]
     objval.className = "points_black"
     vval = vcurblack++
     if(access == "rw") {
      objarea.onclick = ""
      objarea.onmouseover = ""
      objarea.onmouseout  = ""
    }
    break;
    case "PW":
     objimg.src = graphics["img_ptwhite"]
     objval.className = "points_white"
     vval = vcurwhite++
     if(access == "rw") {
      objarea.onclick = ""
      objarea.onmouseover = ""
      objarea.onmouseout  = ""
     }
    break;
   }
   objval.innerHTML = vval
  }
 }
}

// SCORES BAR
function html_cleanScores(obj_scores) {
 obj_scores.innerHTML = ""
}

function html_hideScoresBar(obj_scores) {
 obj_scores.innerHTML = ""
}

function html_loadScoresBar(obj_scores, scores, graphics) {
 txt = "<table>";
    txt += "<tr><th>&nbsp;</th><th class=\"scores_basic_title\">" + _("Territories") + "</th><th class=\"scores_basic_title\">" + _("Living stones") + "</th><th class=\"scores_basic_title\">" + _("Dead Stones") + "</th><th class=\"scores_basic_title\">" + _("Prisoners") + "</th><th class=\"scores_basic_title\">" + _("Komi") + "</th><th class=\"scores_score_title\">" + _("Japanese rules") + "</th><th class=\"scores_score_title\">" + _("Chinese rules") + "</th></tr>"
 txt += "<tr>"
 txt += "<td><img src=\""+graphics["img_black"]+"\" alt=\"Black\" /></td>"
 txt += "<td class=\"scores_basic_value\">"+scores["B"].nbareas+"</td>"
 txt += "<td class=\"scores_basic_value\">"+scores["B"].nblivingstones+"</td>"
 txt += "<td class=\"scores_basic_value\">"+scores["B"].nbdeads+"</td>"
 txt += "<td class=\"scores_basic_value\">"+scores["B"].nbprisoners+"</td>"
 txt += "<td class=\"scores_basic_value\"></td>"
 txt += "<td class=\"scores_score_value\">"+scores["B"].japanese_rule_score+"<div class=\"scores_score_value_delta\"> ("+(scores["B"].japanese_rule_score-scores["W"].japanese_rule_score)+")</div></td>"
 txt += "<td class=\"scores_score_value\">"+scores["B"].chinese_rule_score+"<div class=\"scores_score_value_delta\"> ("+(scores["B"].chinese_rule_score-scores["W"].chinese_rule_score)+")</div></td>"
 txt += "</tr>"

 txt += "<tr>"
 txt += "<td><img src=\""+graphics["img_white"]+"\" alt=\"White\" /></td>"
 txt += "<td class=\"scores_basic_value\">"+scores["W"].nbareas+"</td>"
 txt += "<td class=\"scores_basic_value\">"+scores["W"].nblivingstones+"</td>"
 txt += "<td class=\"scores_basic_value\">"+scores["W"].nbdeads+"</td>"
 txt += "<td class=\"scores_basic_value\">"+scores["W"].nbprisoners+"</td>"
 txt += "<td class=\"scores_basic_value\">"+scores["komi"]+"</td>"
 txt += "<td class=\"scores_score_value\">"+scores["W"].japanese_rule_score+"</td>"
 txt += "<td class=\"scores_score_value\">"+scores["W"].chinese_rule_score+"</td>"
 txt += "</tr>"

 txt += "</table>";
 obj_scores.innerHTML = txt
}

// COMMENTS
function txt2htmljs(str) {
 return str.replace(new RegExp("&", 'g'), "&amp;").replace(new RegExp("<", 'g'), "&lt;").replace(new RegExp(">", 'g'), "&gt;").replace(new RegExp(" ", 'g'), "&nbsp;").replace(new RegExp("\"", 'g'), "&quot;").replace(new RegExp("\n", 'g'), "<br/>")
}

function comment2html(comment_type, comment_author, comment_value, move_ago) {
 comment_txt = "<p class=\""+(comment_type == "VISIBLE" ? "comment_visible" : "comment_hidden")+"\">"
 switch(move_ago) {
  case 0:
  txt_actionsago = _("for this action")
  break;
 default:
  txt_actionsago = Gettext.strargs(gt.ngettext("%1 action ago", "%1 actions ago", move_ago), move_ago)
 }
 comment_txt += "<span class=\"comment_author\">"+comment_author+" ("+txt_actionsago+") : </span>"
 comment_txt += "<span class=\"comment_text\">"+comment_value+"</span>"
 comment_txt += "</p>"
 return comment_txt
}

function html_preloadComments(obj_comment, comment_type, comment_author, comment_value) {
 if(comment_type == 'VISIBLE') {
  txt = comment2html(comment_type, txt2htmljs(comment_author), txt2htmljs(comment_value), 0) + obj_comment.innerHTML
  obj_comment.innerHTML = "" // because of a bug on android (2.3.x, fixed in 4.x) browser, it must be done in 2 times
  obj_comment.innerHTML = txt
 }
}

function html_loadComments(obj_comment, action_num, comments) {
 txt = ""
 $.each(comments, function(nbactions, all_comments) {
  $.each(all_comments, function(n, comment) {
   txt = comment2html(comment["type"], comment["author"], comment["value"], action_num-nbactions) + txt
  })
 })
 obj_comment.innerHTML = txt
 obj_comment.style.display = txt == "" ? 'none' : 'block'
}

// HISTORY
function html_hideHistoryBar(obj_history) {
 obj_history.innerHTML = ""
}

function html_loadHistoryBar(obj_history, go_file, nb_played_actions,
                             f_history_action_backwardall, f_history_action_backward,
                             f_history_action_forwardall, f_history_action_forward) {
 txt = ""

 txt += "<form onsubmit='return false'>";

 // back to start
 txt += "<input type=\"button\" value=\"&lt;&lt;\" onclick=\""+f_history_action_backwardall+"()\" />";
 txt += " "

 // back 10
 if(nb_played_actions > 10) {
  txt += "<input type=\"button\" value=\"&lt;10\" onclick=\""+f_history_action_backward+"(10)\" />";
  txt += " "
 }

 // back
 txt += "<input type=\"button\" value=\"&lt;\" onclick=\""+f_history_action_backward+"(1)\" />";
 txt += " "

 // forward
 txt += "<input type=\"button\" value=\"&gt;\" onclick=\""+f_history_action_forward+"(1)\" />";
 txt += " "

 // forward 10
 if(nb_played_actions > 10) {
  txt += "<input type=\"button\" value=\"10&gt;\" onclick=\""+f_history_action_forward+"(10)\" />";
  txt += " "
 }

 // forward to end
 txt += "<input type=\"button\" value=\"&gt;&gt;\" onclick=\""+f_history_action_forwardall+"()\" />";
 txt += " "

 txt += "</form>\n";

 txt += "<p class=\"go_file_name\">"+go_file+"</p>"

 obj_history.innerHTML = txt
}

// INFOS
function html_hideInfosBar(obj_infos) {
 obj_infos.innerHTML = ""
}

function html_setTitle(next_color) {
 // change title
 if(next_color == 'B') {
   document.title = _("Black's turn") + " - Grooms"
 } else {
   document.title = _("White's turn") + " - Grooms"
 }
}

function html_loadInfosBar_simulate(obj_infos, next_color) {
 html_setTitle(next_color)

 txt = ""

 if(next_color == "B") {
  txt += "<div class=\"infos_turn_black\">" + _("Black's turn") + "</div>"
 } else {
  txt += "<div class=\"infos_turn_white\">" + _("White's turn") + "</div>"
 }
 txt += "<div class=\"simulation\">" + _("Simulation") + "</div>"
 obj_infos.innerHTML = txt
}

function html_loadInfosBar_play(obj_infos, game) {
 html_loadInfosBar(obj_infos, game["prison_black"], game["prison_white"], game["clock_black"], game["clock_white"], game["next_color"], game["last_move"], game["last_move_date"], game["nb_played_stones"])
}

function html_loadInfosBar_history(obj_infos, ghistory, handicap) {
 if(ghistory.current >= 0) {
  html_loadInfosBar(obj_infos, ghistory.prison_black, ghistory.prison_white, ghistory.clock_black[ghistory.current], ghistory.clock_white[ghistory.current], history_get_color(newcurrent+1, handicap), ghistory.moves[ghistory.current], ghistory.timestamps[ghistory.current], ghistory.nb_played_stones)
 } else {
  html_loadInfosBar(obj_infos, 0, 0, 0, 0, "B", new Array(-2, -2), "", 0)
 }
}

function html_loadInfosBar(obj_infos, prison_black, prison_white, clock_black, clock_white, next_color, last_move, last_move_date, nb_played_stones) {
 html_setTitle(next_color)

 txt = ""

 txt += "<div class=\"clear\"></div>"

 txt += "<div style=\"float: left; margin: 0px auto; width: 37%;\" class=\"infos_column_bar\">"
 if(last_move[0] == -2 && last_move[1] == -2) {
  // not a move
 } else {
  if(last_move[0] == -1 && last_move[1] == -1) {
   txt += Gettext.strargs(_("Stone %1"), nb_played_stones) + " - " + move2str(last_move)
  } else {
   txt += Gettext.strargs(_("Stone %1"), nb_played_stones) + " : " + move2str(last_move)
  }
  txt += "<br />"
 }
 txt += "<span class=\"highlight\">" + _("Prisoners :") + "</span> " + _("Black") + " " + prison_black + " - " + _("White") + " " + prison_white
 txt += "</div>"

 txt += "<div style=\"float: left; margin: 0px auto; width: 25%;\" class=\"infos_column_bar\">" // 25% and no 26% because of ie precision bug
 if(next_color == "B") {
  txt += "<span class=\"infos_turn_black\">" + _("Black's turn") + "</span>"
 } else {
  txt += "<span class=\"infos_turn_white\">" + _("White's turn") + "</span>"
 }
 txt +="</div>"

 txt += "<div style=\"float: left; margin: 0px auto; width: 37%;\" class=\"infos_column_bar\">"
 if (last_move_date != "") {  
  txt += "<span class=\"highlight\">"+getLocaleDateFromDB(last_move_date).toLocaleString()+"</span>"
 } else {
  txt += "&nbsp;"
 }
 if(clock_black > 0 || clock_white > 0) {
  txt += "<br />"
  txt += _("Black") + " " + seconds2str(clock_black) + " - " + _("White") + " " + seconds2str(clock_white)
 }
 txt += "</div>"

 txt += "<div class=\"clear\"></div>"

 obj_infos.innerHTML = txt
}

function html_updateBoard_play(steps, delta_mode, currentBoard, newBoard, access, previous_move, last_move, stones_id_prefix, graphics, f_play, f_next_color) {
 html_updateBoard_play_stones(steps, delta_mode, currentBoard, newBoard, previous_move, last_move, stones_id_prefix, graphics)
 html_updateBoard_play_events(steps, delta_mode, currentBoard, newBoard, access, previous_move, last_move, stones_id_prefix, graphics, f_play, f_next_color)
}

function html_updateBoard_play_set_events(steps, f_event, graphics, f_next_color) {
 for(i=0; i<=steps; i++) {
  for(j=0; j<=steps; j++) {
   objimg = document.getElementById(html_getId_img(stones_id_prefix, i, j, 1))
   objarea = document.getElementById(html_getId_map(stones_id_prefix, i, j))
   objarea.onmouseover = ""
   objarea.onmouseout  = ""
   objarea.onclick     = new Function(f_event+"(\""+objimg.id+"\", "+i+", "+j+")")
   objarea.onmouseover = new Function("map_rollover(\""+objimg.id+"\", "+f_next_color+"(true, "+i+", "+j+") == \"B\" ? \""+graphics["img_pblack"]+"\" : "+f_next_color+"(true, "+i+", "+j+") == \"W\" ? \""+graphics["img_pwhite"]+"\" : \""+graphics["img_none"]+"\")")
   objarea.onmouseout = new Function("map_rollover(\""+objimg.id+"\", "+f_next_color+"(false, "+i+", "+j+") == \"B\" ? \""+graphics["img_black"]+"\" : "+f_next_color+"(false, "+i+", "+j+") == \"W\" ? \""+graphics["img_white"]+"\" : \""+graphics["img_none"]+"\")")
  }
 }
}

function html_updateBoard_play_events(steps, delta_mode, currentBoard, newBoard, access, previous_move, last_move, stones_id_prefix, graphics, f_play, f_next_color) {

 for(i=0; i<=steps; i++) {
  for(j=0; j<=steps; j++) {
   if(delta_mode == false                              ||
      currentBoard[i][j] != newBoard[i][j]             || // obj changed
      (last_move[0] == i && last_move[1] == j)         || // last move
      (previous_move[0] == i && previous_move[1] == j)    // previous move
    ) {
    objimg = document.getElementById(html_getId_img(stones_id_prefix, i, j, 1))
    objarea = document.getElementById(html_getId_map(stones_id_prefix, i, j))

    if(newBoard[i][j] == "X") {
     if(access == "rw") {
      objarea.onmouseover = new Function("map_rollover(\""+objimg.id+"\", "+f_next_color+"() == \"B\" ? \""+graphics["img_pblack"]+"\" : \""+graphics["img_pwhite"]+"\")")
      objarea.onmouseout = new Function("map_rollover(\""+objimg.id+"\", \""+graphics["img_none"]+"\")")
      objarea.onclick     = new Function(f_play+"(\""+objimg.id+"\", "+i+", "+j+")")
     } else {
      objarea.onmouseover = ""
      objarea.onmouseout  = ""
      objarea.onclick     = ""
     }
    } else {
     objarea.onmouseover  = ""
     objarea.onmouseout   = ""

     if(access == "rw" && last_move[0] == i && last_move[1] == j) { // last move
      objarea.onclick = new Function(f_play+"(\""+objimg.id+"\", "+i+", "+j+")")
     } else {
      objarea.onclick = ""
     }
    }
   }
  }
 }
}

function html_resetBoard_play_events(steps, stones_id_prefix) {
 for(i=0; i<=steps; i++) {
  for(j=0; j<=steps; j++) {
   objarea = document.getElementById(html_getId_map(stones_id_prefix, i, j))
   objarea.onmouseover = ""
   objarea.onmouseout  = ""
   objarea.onclick     = ""
  }
 }
}

function html_updateBoard_play_stones(steps, delta_mode, currentBoard, newBoard, previous_move, last_move, stones_id_prefix, graphics) {
 for(i=0; i<=steps; i++) {
  for(j=0; j<=steps; j++) {
   if(delta_mode == false                              ||
      currentBoard[i][j] != newBoard[i][j]             || // obj changed
      (last_move[0] == i && last_move[1] == j)         || // last move
      (previous_move[0] == i && previous_move[1] == j)    // previous move
    ) {
    objimg1 = document.getElementById(html_getId_img(stones_id_prefix, i, j, 1))
    objimg2 = document.getElementById(html_getId_img(stones_id_prefix, i, j, 2))

    if(newBoard[i][j] == "X") {
     objimg1.src = graphics["img_none"]
     objimg2.src = graphics["img_none"]
    } else {
     if(last_move[0] == i && last_move[1] == j) {
      objimg1.src = newBoard[i][j] == "B" ? graphics["img_lblack"] : graphics["img_lwhite"]
     } else {
      objimg1.src = newBoard[i][j] == "B" ? graphics["img_black"] : graphics["img_white"]
     }
     objimg2.src = graphics["img_shadow"]
    }
   }
  }
 }
}

function getDateFromDB(str) {
 return new Date(parseInt(str.substring( 0, 4), 10),
                 parseInt(str.substring( 4, 6), 10)-1,
                 parseInt(str.substring( 6, 8), 10),
                 parseInt(str.substring( 9,11), 10),
                 parseInt(str.substring(12,14), 10),
                 parseInt(str.substring(15,17), 10))
}

function getLocaleDateFromDB(gmtDbDate) {
 current_date = new Date()
 return new Date(getDateFromDB(gmtDbDate).getTime() - current_date.getTimezoneOffset()*1000*60)
}

function getRequiredZoom(graphics, per_marge, vmin, vmax) {
 w = $(window).innerWidth()
 h = $(window).innerHeight()
 min_s = w > h ? h : w

 res = (min_s * per_marge) / graphics["width"]

 // zoom between 0.6 and 1.2 is ok
 if(res < vmin) {
  res = vmin
 }
 if(res > vmax) {
  res = vmax
 }

 return res
}

// LOGIN

function html_loadLoginBar(loginbar_id, login_id, f_login, faq_url) {
 txt = ""

 login_password_id = login_id+'_password'
 txt += "<div id=\""+loginbar_id+"\" class=\"hidden_area\">"
 txt += "<span>"
 txt += "<form class=\"action_item\" onsubmit=\""+f_login+"(document.getElementById('"+login_password_id+"').value) ; return false\">"
 txt += "<input type=\"password\" name=\"password\" size=\"5\" id=\""+login_password_id+"\" />"
 txt += "<input type=\"button\" value=\"" + _("Login") + "\" onclick=\""+f_login+"(document.getElementById('"+login_password_id+"').value)\" />"
 txt += "</form>"
 txt += "</span>"
 txt += "<span class=\"faq_section\"><a href=\""+faq_url+"\" target=\"faq\">" + _("Help") + "</a></span>"
 txt += "</div>"

 document.getElementById(login_id).innerHTML = txt
}

function html_showBar(bar_id, isVisible) {
 layer = document.getElementById(bar_id)
 if(layer != null) {
  layer.style.display = isVisible ? "none" : "block"
 }
}

function html_showAreas(go_areas, access, mode) {
 // comment
 obj_id = document.getElementById(go_areas.comment.id)
 obj_id.style.display = (access == "rw" && mode == "play" && go_areas.comment.visible == true) ? 'block' : 'none'

 // reset
 obj_id = document.getElementById(go_areas.reset.id)
 obj_id.style.display = (access == "rw" && mode == "play" && go_areas.reset.visible == true) ? 'block' : 'none'

 // old games
 obj_id = document.getElementById(go_areas.old_games.id)
 obj_id.style.display = (mode != "simulate" && go_areas.old_games.visible == true) ? 'block' : 'none'
}

function html_loadAreas(board_areas_id, go_areas, go_room, comment_f, reset_f, oldgames_f) {
 txt = ""
 txt += html_loadAreaTxt_comment(go_areas.comment.id, go_room, comment_f)
 txt += html_loadAreaTxt_reset(go_areas.reset.id, reset_f)
 txt += html_loadAreaTxt_oldGames(go_areas.old_games.id, oldgames_f)

 document.getElementById(board_areas_id).innerHTML = txt
}

function html_loadAreaTxt_comment(area_comment_id, go_room, comment_f) {
 txt = ""
 txt += "<div class=\"comment_area hidden_area\" id=\""+area_comment_id+"\">"
 txt += "<form id=\"comment_form\" class=\"form_tosend\" onsubmit='return false'>"
 txt += "<input type=\"hidden\" name=\"room\" value=\""+go_room+"\" />"
 txt += "<input type=\"hidden\" name=\"action\" value=\"comment\" />"
 txt += _("Your name :") + " <input type=\"text\" id=\"comment_sender\" name=\"sender\" />"
 txt += "<br />"
 txt += "<textarea name=\"comment\" id=\"comment_comment\" cols=\"50\" rows=\"2\"></textarea>"
 txt += "<br/>"
 txt += "<select name=\"type\" id=\"comment_type\">"
 txt += "<option value=\"VISIBLE\">" + _("Visible comment") + "</option>"
 txt += "<option value=\"HIDDEN\">" + _("Hidden comment") + "</option>"
 txt += "</select>\n"
 txt += "<input type=\"button\" value=\"" + _("Send this comment") + "\" onclick=\""+comment_f+"()"+"\" />"
 txt += "</form>"
 txt += "</div>"

 return txt
}

function html_loadAreaTxt_reset(area_reset_id, reset_f) {
 reset_boardsize_id = area_reset_id+'_boardsize'
 reset_handicap_id  = area_reset_id+'_handicap'
 reset_komi_id      = area_reset_id+'_komi'

 txt = ""
 txt += "<div class=\"reset_area hidden_area\" id=\""+area_reset_id+"\">"
 txt += "<form class=\"form_tosend\" onsubmit='return false'>"
 txt += _("Size :")
 txt += "<select name=\"board_size\" id=\""+reset_boardsize_id+"\" >"
 txt += "<option value=\"19\" >19</option>"
 txt += "<option value=\"13\" >13</option>"
 txt += "<option value=\"9\" >9</option>"
 txt += "</select>\n"
 txt += _("Handicap :")
 txt += "<select name=\"handicap\" id=\""+reset_handicap_id+"\" >"
 for(i=0; i<=8; i++) txt += "<option value=\""+i+"\" >"+i+"</option>"
 txt += "</select>\n"
 txt += _("Komi :")
 txt += "<select name=\"komi\" id=\""+reset_komi_id+"\" >"
 txt += "<option value=\"6.5\" >6.5</option>"
 txt += "<option value=\"7.5\" >7.5</option>"
 txt += "</select>\n"

 txt += "<input type=\"button\" value=\"" + _("Reset game") + "\" onclick=\""+reset_f+"(document.getElementById('"+reset_boardsize_id+"').value, document.getElementById('"+reset_handicap_id+"').value, document.getElementById('"+reset_komi_id+"').value)"+"\" />"
 txt += "</form>"
 txt += "</div>"

 return txt
}

function html_loadAreaTxt_oldGames(old_games_area_id, oldgames_f) {
 txt = ""
 txt += "<div class=\"old_games_area hidden_area\" id=\""+old_games_area_id+"\">"
 txt += "<form class=\"form_tosend\" onsubmit='return false'>"
 txt += _("Game :")
 // because of a ie bug (including version 7,8,9), select.innerHTML bugs
 old_games_file_id = html_getId_oldGameFiles(old_games_area_id)
 old_games_file_spanid = html_getSpanId_oldGameFiles(old_games_area_id)
 txt += "<span id=\""+old_games_file_spanid+"\">"
 txt += "</span>"
 txt += "<input type=\"button\" value=\"" + _("Load") + "\" onclick=\""+oldgames_f+"(document.getElementById('"+old_games_file_id+"').value)"+"\" />"
 txt += "</form>"
 txt += "</div>"

 return txt
}
