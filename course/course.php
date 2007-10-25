<?php
//IMathAS:  Main course page
//(c) 2006 David Lippman
   require("../validate.php");
   require("courseshowitems.php");
   if (!isset($teacherid) && !isset($tutorid) && !isset($studentid) && !isset($guestid)) {
	   require("../header.php");
	   echo "You are not enrolled in this course.  Please return to the <a href=\"../index.php\">Home Page</a> and enroll\n";
	   require("../footer.php");
	   exit;
   }
   $cid = $_GET['cid'];
   
   if (isset($teacherid) && isset($_GET['from']) && isset($_GET['to'])) {
	   $from = $_GET['from'];
	   $to = $_GET['to'];
	   $block = $_GET['block'];
	   $query = "SELECT itemorder FROM imas_courses WHERE id='{$_GET['cid']}'";
	   $result = mysql_query($query) or die("Query failed : " . mysql_error());
	   $items = unserialize(mysql_result($result,0,0));
	   
	   $blocktree = explode('-',$block);
	   $sub =& $items;
	   for ($i=1;$i<count($blocktree)-1;$i++) {
		   $sub =& $sub[$blocktree[$i]-1]['items']; //-1 to adjust for 1-indexing
	   }
	   if (count($blocktree)>1) {
		   $curblock =& $sub[$blocktree[$i]-1]['items'];
		   $blockloc = $blocktree[$i]-1;
	   } else {
		   $curblock =& $sub;
	   }
	   	   
	   $blockloc = $blocktree[count($blocktree)-1]-1; 
	   //$sub[$blockloc]['items'] is block with items
	   
	   if (strpos($to,'-')!==false) {  //in or out of block
		   if ($to[0]=='O') {  //out of block
			  $itemtomove = $curblock[$from-1];  //+3 to adjust for other block params
			  //$to = substr($to,2);
			  array_splice($curblock,$from-1,1);
			  if (is_array($itemtomove)) {
				  array_splice($sub,$blockloc+1,0,array($itemtomove));
			  } else {
				  array_splice($sub,$blockloc+1,0,$itemtomove);
			  }
		   } else {  //in to block
			  $itemtomove = $curblock[$from-1];  //-1 to adjust for 0 indexing vs 1 indexing
			  array_splice($curblock,$from-1,1);
			  $to = substr($to,2);
			  if ($from<$to) {$adj=1;} else {$adj=0;}
			  array_push($curblock[$to-1-$adj]['items'],$itemtomove);
		   }
	   } else { //move inside block
		   $itemtomove = $curblock[$from-1];  //-1 to adjust for 0 indexing vs 1 indexing
		   array_splice($curblock,$from-1,1);
		   if (is_array($itemtomove)) {
			   array_splice($curblock,$to-1,0,array($itemtomove));
		   } else {
			   array_splice($curblock,$to-1,0,$itemtomove);
		   }
	   }
	   $itemlist = addslashes(serialize($items));
	   $query = "UPDATE imas_courses SET itemorder='$itemlist' WHERE id='{$_GET['cid']}'";
	   mysql_query($query) or die("Query failed : " . mysql_error()); 
	   header("Location: http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/course.php?cid={$_GET['cid']}");
   }
   $query = "SELECT name,itemorder,hideicons,allowunenroll,msgset,topbar,cploc FROM imas_courses WHERE id='$cid'";
   $result = mysql_query($query) or die("Query failed : " . mysql_error());
   $line = mysql_fetch_array($result, MYSQL_ASSOC);
   if ($line == null) {
	   echo "Course does not exist.  <a href=\"../index.php\">Return to main page</a></body></html>\n";
	   exit;
   }
   $allowunenroll = $line['allowunenroll'];
   $hideicons = $line['hideicons'];
   $pagetitle = $line['name'];
   $items = unserialize($line['itemorder']);
   $msgset = $line['msgset'];
   $useleftbar = ($line['cploc']==1);
   $topbar = explode('|',$line['topbar']);
   $topbar[0] = explode(',',$topbar[0]);
   $topbar[1] = explode(',',$topbar[1]);
   if ($topbar[0][0] == null) {unset($topbar[0][0]);}
   if ($topbar[1][0] == null) {unset($topbar[1][0]);}
   
   if (isset($teacherid) && isset($_GET['togglenewflag'])) { //handle toggle of NewFlag
	$sub =& $items;
	$blocktree = explode('-',$_GET['togglenewflag']);
	if (count($blocktree)>1) {
		for ($i=1;$i<count($blocktree)-1;$i++) {
			$sub =& $sub[$blocktree[$i]-1]['items']; //-1 to adjust for 1-indexing
		}
	}
	$sub =& $sub[$blocktree[$i]-1];
	if (!isset($sub['newflag']) || $sub['newflag']==0) {
		$sub['newflag']=1;
	} else {
		$sub['newflag']=0;
	}
	$itemlist = addslashes(serialize($items));
	$query = "UPDATE imas_courses SET itemorder='$itemlist' WHERE id='$cid'";
	mysql_query($query) or die("Query failed : " . mysql_error()); 	   
   }
   
   //enable teacher guest access
   if (isset($guestid)) {
	   $teacherid = $guestid;
   }
   
   if ((!isset($_GET['folder']) || $_GET['folder']=='') && !isset($sessiondata['folder'.$cid])) {
	   $_GET['folder'] = '0';  
	   $sessiondata['folder'.$cid] = '0';
	   writesessiondata();
   } else if ((isset($_GET['folder']) && $_GET['folder']!='') && $sessiondata['folder'.$cid]!=$_GET['folder']) {
	   $sessiondata['folder'.$cid] = $_GET['folder'];
	   writesessiondata();
   } else if ((!isset($_GET['folder']) || $_GET['folder']=='') && isset($sessiondata['folder'.$cid])) {
	   $_GET['folder'] = $sessiondata['folder'.$cid];
   }
   if ($_GET['folder']!='0') {
	   $now = time() + $previewshift;
	   $blocktree = explode('-',$_GET['folder']);
	   $backtrack = array();
	   for ($i=1;$i<count($blocktree);$i++) {
		$backtrack[] = array($items[$blocktree[$i]-1]['name'],implode('-',array_slice($blocktree,0,$i+1)));
		if (!isset($teacherid) && $items[$blocktree[$i]-1]['SH'][0]!='S' &&($now<$items[$blocktree[$i]-1]['startdate'] || $now>$items[$blocktree[$i]-1]['enddate'])) {
			$_GET['folder'] = 0;
			$items = unserialize($line['itemorder']);
			unset($backtrack);
			unset($blocktree);
			break;
		}
		$items = $items[$blocktree[$i]-1]['items']; //-1 to adjust for 1-indexing
	   }
   }
  
   $placeinhead = "<script type=\"text/javascript\" src=\"$imasroot/javascript/course.js\"></script>";
   require("../header.php");
   if (isset($teacherid)) {
	   echo "<script type=\"text/javascript\">\n";
	   echo "function moveitem(from,blk) { \n";
	   echo "  var to = document.getElementById(blk+'-'+from).value; \n";
	   $address = "http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/course.php?cid={$_GET['cid']}";
	   echo "  if (to != from) {\n";
	   echo "  	var toopen = '$address&block=' + blk + '&from=' + from + '&to=' + to;\n";
	   echo "  	window.location = toopen; \n";
	   echo "  }\n";
	   echo "}\n";
	   echo "function additem(blk) { \n";
	   echo "  var type = document.getElementById('addtype'+blk).value; \n";
	   echo "  if (type!='') {\n";
	   $address = "http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	   echo "    var toopen = '$address/add' + type + '.php?block='+blk+'&cid={$_GET['cid']}';\n";
	   echo "    window.location = toopen; \n";
	   echo "  } \n";
	   echo "}\n";
	   echo "</script>\n";
   }
   $openblocks = Array(0);
   if (isset($_COOKIE['openblocks-'.$cid]) && $_COOKIE['openblocks-'.$cid]!='') {$openblocks = explode(',',$_COOKIE['openblocks-'.$cid]); $firstload=false;} else {$firstload=true;}
   $oblist = implode(',',$openblocks);
   
   echo "<script>\n";
   echo "var getbiaddr = 'getblockitems.php?cid=$cid&folder=';\n";
   echo "var oblist = '$oblist';\n";
   echo "var cid = '$cid';\n";
   /*
   echo 'function arraysearch(needle,hay) {';
   echo '   for (var i=0; i<hay.length;i++) {';
   echo '         if (hay[i]==needle) {';
   echo '               return i;';
   echo '         }';
   echo '   }';
   echo '   return -1;';
   echo '}';
   echo "function toggleblock(bnum) {\n";
   echo "   var node = document.getElementById('block'+bnum);\n";
   echo "   var butn = document.getElementById('but'+bnum);\n";
   echo "   oblist = oblist.split(',');\n";
   echo "   var loc = arraysearch(bnum,oblist);\n";
   echo "   if (node.className == 'blockitems') {\n";
   echo "       node.className = 'hidden';\n";
   echo "       butn.value = 'Expand';\n";
   echo "       if (loc>-1) {oblist.splice(loc,1);}\n";
   echo "   } else { ";
   echo "      	node.className = 'blockitems';\n";
   echo "       butn.value = 'Collapse';\n";
   echo "       if (loc==-1) {oblist.push(bnum);} \n";
   echo "   }\n";
   echo "   oblist = oblist.join(',');\n";
   echo "   document.cookie = 'openblocks-$cid=' + oblist;\n";
   echo "}\n";
   */
   echo "</script>\n";
   
   
   echo "<div class=breadcrumb><span class=\"padright\">";
   if (isset($guestid)) {
	   echo '<span class="red">Instructor Preview</span> ';
   }
   echo "$userfullname</span><a href=\"../index.php\">Home</a> &gt; ";
   if (isset($backtrack) && count($backtrack)>0) {
	   echo "<a href=\"course.php?cid=$cid&folder=0\">$coursename</a> ";
	   for ($i=0;$i<count($backtrack);$i++) {
		   echo "&gt; ";
		   if ($i!=count($backtrack)-1) {
			   echo "<a href=\"course.php?cid=$cid&folder={$backtrack[$i][1]}\">";
		   }
		   echo $backtrack[$i][0];
		   if ($i!=count($backtrack)-1) {
			   echo "</a>";
		   }
	   }
	   $curname = $backtrack[count($backtrack)-1][0];
   } else {
	   echo $coursename;
	   $curname = $coursename;
   }
   echo "<div class=clear></div></div>\n";
   
   if ($msgset<3) {
	   $query = "SELECT COUNT(id) FROM imas_msgs WHERE msgto='$userid' AND (isread=0 OR isread=4)";
	   $result = mysql_query($query) or die("Query failed : " . mysql_error());
	   if (mysql_result($result,0,0)>0) {
		   $newmsgs = " <span style=\"color:red\">New Messages</span>";
	   } else {
		   $newmsgs = '';
	   }
   }
   
   if ($useleftbar && isset($teacherid)) {
	echo "<div id=\"leftcontent\">";
	echo "<p>".generateadditem($_GET['folder']). '</p>';
	echo "<p><b>Show:</b><br/>";
	echo "<a href=\"$imasroot/msgs/msglist.php?cid=$cid&folder={$_GET['folder']}\">Messages</a>$newmsgs<br/>";
	echo "<a href=\"listusers.php?cid=$cid\">Students</a><br/>\n";
	echo "<a href=\"gradebook.php?cid=$cid\">Gradebook</a><br/>\n";
	echo "<a href=\"course.php?cid=$cid&stuview=0\">Student View</a></p>\n";
	echo "<p><b>Manage:</b><br/>";
	echo "<a href=\"manageqset.php?cid=$cid\">Question Set</a><br>\n";
	echo "<a href=\"managelibs.php?cid=$cid\">Libraries</a><br/>";
	echo "<a href=\"managestugrps.php?cid=$cid\">Groups</a></p>";
	if ($allowcourseimport) {
		echo "<p><b>Export/Import:</b><br/>";
		echo "<a href=\"../admin/export.php?cid=$cid\">Export Question Set<br/></a>\n";
		echo "<a href=\"../admin/import.php?cid=$cid\">Import Question Set<br/></a>\n";
		echo "<a href=\"../admin/exportlib.php?cid=$cid\">Export Libraries<br/></a>\n";
		echo "<a href=\"../admin/importlib.php?cid=$cid\">Import Libraries</p></a>\n";
	} 
	echo "<p><b>Course Items:</b><br/>";
	echo "<a href=\"copyitems.php?cid=$cid\">Copy</a><br/>\n";
	echo "<a href=\"../admin/exportitems.php?cid=$cid\">Export</a><br/>\n";
	echo "<a href=\"../admin/importitems.php?cid=$cid\">Import</a></p>\n";
	echo "<p><b>Change:</b><br/>";
	//echo "<a href=\"timeshift.php?cid=$cid\">Shift all Course Dates</a><br/>\n";
	echo "<a href=\"chgassessments.php?cid=$cid\">Assessments</a><br/>\n";
	echo "<a href=\"masschgdates.php?cid=$cid\">Dates</a><br/>";
	echo "<a href=\"../admin/forms.php?action=modify&id=$cid&cid=$cid\">Course Settings</a>";
	echo "</p>";   
	echo "<p><a href=\"$imasroot/help.php?section=coursemanagement\">Help</a><br/>\n";
	echo "<a href=\"../actions.php?action=logout\">Log Out</a></p>\n";
	echo "</div>";
	echo "<div id=\"centercontent\">";
   }
   
   if ($previewshift>-1) {
	echo '<script type="text/javascript">';
	echo 'function changeshift() {';
	
	$address = "http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/course.php?cid=$cid";
	echo '  var shift = document.getElementById("pshift").value;'. "\n";
	echo "  var toopen = '$address&stuview='+shift;\n";
	echo " 	window.location = toopen; \n";
	echo '}';
	echo '</script>';
   }
   
   if (isset($teacherid) && count($topbar[1])>0) {
	echo '<div class=breadcrumb>';
	if (in_array(0,$topbar[1]) && $msgset<3) { //messages
		echo "<a href=\"$imasroot/msgs/msglist.php?cid=$cid\">Messages</a>$newmsgs &nbsp; ";
	}
	if (in_array(1,$topbar[1])) { //Stu view
		echo "<a href=\"course.php?cid=$cid&stuview=0\">Student View</a> &nbsp; ";
	}
	if (in_array(2,$topbar[1])) { //Gradebook
		echo "<a href=\"gradebook.php?cid=$cid\">Show Gradebook</a> &nbsp; ";
	}
	if (in_array(3,$topbar[1])) { //List stu
		echo "<a href=\"listusers.php?cid=$cid\">List Students</a> &nbsp; \n";
	}
	if (in_array(9,$topbar[1])) { //Log out
		echo "<a href=\"../actions.php?action=logout\">Log Out</a>";
	}
	echo '<div class=clear></div></div>';
   } else if (!isset($teacherid) && (count($topbar[0])>0 || $previewshift>-1)) {
	echo '<div class=breadcrumb>';
	if (in_array(0,$topbar[0]) && $msgset<3) { //messages
		echo "<a href=\"$imasroot/msgs/msglist.php?cid=$cid\">Messages</a>$newmsgs &nbsp; ";
	}
	if (in_array(1,$topbar[0])) { //Gradebook
		echo "<a href=\"gradebook.php?cid=$cid\">Show Gradebook</a> &nbsp; ";
	}
	if (in_array(9,$topbar[0])) { //Log out
		echo "<a href=\"../actions.php?action=logout\">Log Out</a>";
	}
	if ($previewshift>-1 && count($topbar[0])>0) { echo '<br />';}
	if ($previewshift>-1) {
		echo 'Showing student view. Show view: <select id="pshift" onchange="changeshift()">';
		echo '<option value="0" ';
		if ($previewshift==0) {echo "selected=1";}
		echo '>Now</option>';
		echo '<option value="3600" ';
		if ($previewshift==3600) {echo "selected=1";}
		echo '>1 hour from now</option>';
		echo '<option value="14400" ';
		if ($previewshift==14400) {echo "selected=1";}
		echo '>4 hours from now</option>';
		echo '<option value="86400" ';
		if ($previewshift==86400) {echo "selected=1";}
		echo '>1 day from now</option>';
		echo '<option value="604800" ';
		if ($previewshift==604800) {echo "selected=1";}
		echo '>1 week from now</option>';
		echo '</select>';
		echo " <a href=\"course.php?cid=$cid&teachview=1\">Back to instructor view</a>";
	   }
	echo '<div class=clear></div></div>';
	   
   }
   	
   echo "<h2>$curname</h2>\n";
   
   //get exceptions
   $now = time() + $previewshift;
   $exceptions = array();
   if (!isset($teacherid)) {
	   $query = "SELECT items.id,ex.startdate,ex.enddate FROM ";
	   $query .= "imas_exceptions AS ex,imas_items as items,imas_assessments as i_a WHERE ex.userid='$userid' AND ";
	   $query .= "ex.assessmentid=i_a.id AND (items.typeid=i_a.id AND items.itemtype='Assessment') ";
	   $query .= "AND (($now<i_a.startdate AND ex.startdate<$now) OR ($now>i_a.enddate AND $now<ex.enddate))";
	   $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
	   while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		   $exceptions[$line['id']] = array($line['startdate'],$line['enddate']);
	   }
   }
   
   if (count($items)>0) {
	   //update block start/end dates to show blocks containing items with exceptions
	   if (count($exceptions)>0) {
		   upsendexceptions($items);
	   }
	   	   
	   showitems($items,$_GET['folder']);
   }
   
   
   if ($useleftbar && isset($teacherid)) {
	   echo "</div>";
   } else {
	   if ($msgset<3) {
		   echo "<div class=cp>\n";
		   echo "<span class=column>";
		   echo "<a href=\"$imasroot/msgs/msglist.php?cid=$cid&folder={$_GET['folder']}\">Messages</a>$newmsgs ";
		   echo "</span>";
		   echo "<div class=clear></div></div>\n";
	   }
	   
	   
	   if (isset($teacherid)) {
		echo "<div class=cp>\n";
		echo "<span class=column>";
		echo generateadditem($_GET['folder']);
		echo "<a href=\"listusers.php?cid=$cid\">List Students</a><br/>\n";
		echo "<a href=\"gradebook.php?cid=$cid\">Show Gradebook</a><br/>\n";
		echo "<a href=\"course.php?cid=$cid&stuview=0\">Student View</a></span>\n";
		echo "<span class=column><a href=\"manageqset.php?cid=$cid\">Manage Question Set<br></a>\n";
		if ($allowcourseimport) {
			echo "<a href=\"../admin/export.php?cid=$cid\">Export Question Set<br></a>\n";
			echo "<a href=\"../admin/import.php?cid=$cid\">Import Question Set</span></a>\n";
			echo "<span class=column><a href=\"managelibs.php?cid=$cid\">Manage Libraries</a><br>";
			echo "<a href=\"../admin/exportlib.php?cid=$cid\">Export Libraries</a><br/>\n";
			echo "<a href=\"../admin/importlib.php?cid=$cid\">Import Libraries</span></a>\n";
			echo "<span class=column><a href=\"copyitems.php?cid=$cid\">Copy Course Items</a><br/>\n";
			echo "<a href=\"managestugrps.php?cid=$cid\">Student Groups</a></span>\n";
		} else {
			echo "<a href=\"managelibs.php?cid=$cid\">Manage Libraries</a><br>";
			echo "<a href=\"copyitems.php?cid=$cid\">Copy Course Items</a></span>\n";
			echo "<span class=column><a href=\"managestugrps.php?cid=$cid\">Student Groups</a><br/>";
			echo "<a href=\"../admin/forms.php?action=modify&id=$cid&cid=$cid\">Course Settings</a></span>\n";
		}
		echo "<div class=clear></div></div>\n";
	   }
	   echo "<div class=cp>\n";
	   
	   if (!isset($teacherid)) {
		echo "<a href=\"../actions.php?action=logout\">Log Out</a><BR>\n";
		echo "<a href=\"gradebook.php?cid=$cid\">Show Gradebook</a><br/>\n";   
		echo "<a href=\"$imasroot/help.php?section=usingimas\">Help Using IMathAS</a><br/>\n";   
		if ($myrights > 5 && $allowunenroll==1) {
			echo "<p><a href=\"../forms.php?action=unenroll&cid=$cid\">Unenroll From Course</a></p>\n";
		}
		
	   } else {
		echo "<span class=column>";
		echo "<a href=\"../actions.php?action=logout\">Log Out</a><BR>\n";
		if ($allowcourseimport) {
			echo "<a href=\"copyitems.php?cid=$cid\">Copy Course Items</a><br/>\n";
		}
		echo "<a href=\"../admin/exportitems.php?cid=$cid\">Export Course Items</a><br/>\n";
		echo "<a href=\"../admin/importitems.php?cid=$cid\">Import Course Items</a><br/>\n";
		echo "</span><span class=column>";
		echo "<a href=\"$imasroot/help.php?section=coursemanagement\">Help</a><br/>\n";
		echo "<a href=\"timeshift.php?cid=$cid\">Shift all Course Dates</a><br/>\n";
		echo "<a href=\"chgassessments.php?cid=$cid\">Mass Change Assessments</a>\n";
		echo "</span>";
		echo "<span class=column>";
		echo "<a href=\"masschgdates.php?cid=$cid\">Mass Change Dates</a>";
		echo "</span>";
	   }
	   echo "<div class=clear></div></div>\n";
   }
   if ($firstload) {
	   echo "<script>document.cookie = 'openblocks-$cid=' + oblist;</script>\n";
   }
   require("../footer.php");
   
   function getpts($scs) {
	   $tot = 0;
	   foreach(explode(',',$scs) as $sc) {
		if (strpos($sc,'~')===false) {
			if ($sc>0) { 
				$tot += $sc;
			} 
		} else {
			$sc = explode('~',$sc);
			foreach ($sc as $s) {
				if ($s>0) { 
					$tot+=$s;
				}
			}
		}
	   }
	   return $tot;
   }
   
?>

