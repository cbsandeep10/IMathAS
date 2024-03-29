<?php
  //IMathAS:  Main config file.
	//This shows some examples of how you can modify the config.php
	//created automatically by install.php
  //(c) 2006 David Lippman

  //these settings must be editted before running dbsetup.php
  $dbserver = "localhost";  		//database server
  $dbname = "imathasdb";			//database name
  $dbusername = "root";		//database user
  $dbpassword = "Ra!nb0w";	//database user's password

	//error reporting level.  Set to 0 for production servers.
	error_reporting(E_ALL & ~E_NOTICE);

  //The name for this installation.  For personalization
  $installname = "IMathAS";

    //For new user, long description of username requirement
  $longloginprompt = "Enter a username.  Use only numbers, letters, or the _ character.";

  //short prompt, for login page
  $loginprompt = "Username";

  //Require the username to meet a specific format.  Choose one of the following,
  //or write your own.
  $loginformat = '/^[\w\-]+$/';  //A-Z, a-z, 0-9, _ are the only allowed characters
  //$loginformat = '/^(\d{9}|lti-\d+)$/';  //Requires a 9 digit number
  //$loginformat = '/^(\d{3}-\d{2}-\d{4}|lti-\d+)$/';  //SSN type format: 555-55-5555

	/* Additional options available for restricting login format and related:
	$loginformat can be an array of regexs instead of a single one to impose
	  multiple restrictions
	$CFG['acct']['SIDformaterror'] = a custom error message to display if the username
	  does not match the $loginformat requirement.
	$CFG['acct']['passwordMinlength'] = min length for passwords (default 6)
	$CFG['acct']['passwordFormat'] = a single regex string or array of regexs
	  as requirements for the password
	$CFG['acct']['passwordFormaterror'] = a custom error message to display if the
		password does not match the requirement.
	$CFG['acct']['emailFormat'] = a single regex string or array of regexs
	  as requirements for the email
	$CFG['acct']['emailFormaterror'] = a custom error message to display if the
		email does not match the requirement.
	*/
  //If set to true, the system will send an email to newusers when they try
  //to enroll.  They have to respond to the email before they are allowed to
  //log in.
  $emailconfirmation = false;

  //the email address to have as the "from" listing on confirmation emails
  //also the address new instructor requests (if you use that page) will be
  //sent to
  $sendfrom = "imathas@yoursite.com";

  //web root to imathas:  http://yoursite.com $imasroot
  //set = "" if installed in web root dir
  $imasroot = "/IMathAS";

  //base site url - use when generating full URLs to site pages.
  $httpmode = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO']=='https')
    ? 'https://' : 'http://';
  $GLOBALS['basesiteurl'] = $httpmode . Sanitize::domainNameWithPort($_SERVER['HTTP_HOST']) . $imasroot;

  //absolute path or full url to Mimetex CGI, for math image fallback
  //if you do not have a local install, feel free to use:
  // $mathimgurl = "http://www.imathas.com/cgi-bin/mimetex.cgi"
  $mathimgurl = "/cgi-bin/mimetex.cgi";

  //shift color of icons from green to red as deadline approaches?
  $colorshift = true;

  //A small logo to display on the upper right of course pages
  //set = '<img src="/path/to/img.gif">' or = 'Some Text'
  //Image should be about 120 x 80px
  $smallheaderlogo = $installname;

  //should non-admins be allowed to create new non-group libraries?
  //on a single-school install, set to true; for larger installs that plan to
  //use the instructor-groups features, set to false
  $allownongrouplibs = false;

  //should anyone be allowed to import/export questions and libraries from the
  //course page?  Intended for easy sharing between systems, but the course page
  //is cleaner if turned off.
  $allowcourseimport = false;

  //allow installation of macro files by admins?  macro files contain a large
  //security risk.  If you are going to have many admins, and don't trust the
  //security of their passwords, you should set this to false.  Installing
  //macros is equivalent in security risk to having FTP access to the IMathAS
  //server.
  //For single-admin systems, it is recommended you leave this as false, and
  //change it when you need to install a macro file.  Do install macro files
  //using the web system; a help file is automatically generated when you install
  //through the system
  $allowmacroinstall = true;

  //This is used to change the session file path different than the default.
  //This is usually not necessary unless your site is on a server farm, or
  //you're on a shared server and want more security of session data.
  //This may also be needed to allow setting the garbage collection time limit
  //so that session data isn't removed after 24 minutes.
  //Make sure this directory has write access by the server process.
  //$sessionpath = '/tmp/persistent/imathas/sessions';

  //enables use of IMathAS as a BasicLTI producer.
  $enablebasiclti = true;

  //template user id
  //Generally not needed.  Use if you want a list of Template courses in the
  //copy course items page.  Set = to a user's ID who will serve as the
  //template holder instructor.  Add that user to all courses to list as a
  //template
  //$templateuser = 10;

  //For text editor file/image uploads and assessment file uploads, we can use
  //Amazon S3 service to hold these files.  If using this option, provide your
  //Amazon S3 key and secret below.  You'll also need to create a bucket and
  //specify it below.
  //If this is not provided, local storage will be used.
  //$AWSkey = "";
  //$AWSsecret = "";
  //$AWSbucket = "";

	//use secure password hashing
	$CFG['GEN']['newpasswords'] = 'only';


  //Special Config tags for customizing
  //$loginpage = 'custom-login-page.php';
  /*
  $studentTOS = "info/studentTOS.php";
  $CFG['CPS']['theme'] = array("modern.css",1);  //default theme - second value 0=fixed, 1=can change
  $CFG['CPS']['themelist'] = "default.css,modern.css";  //list of allowed themes
  $CFG['CPS']['themenames'] = "Default, Modern";  //to give names to the theme files

  $CFG['GEN']['allowinstraddstus'] = false; //disallow instructors to add students from the roster page
  $CFG['GEN']['allowInstrImportStuByName'] = false;  //disallow instructors from using first_last username on import
  $CFG['GEN']['allowinstraddtutors'] = false;  //disallow instructors to enroll tutors
  $CFG['GEN']['addteachersrights'] = 75;  //min rights to add/remove teachers to a course
  $CFG['GEN']['homelayout'] = '|0,1,2|10,11|';  //default home layout
  $CFG['GEN']['fixedhomelayout'] = array(3);  //prevent changing of home layout sections
  $CFG['GEN']['hometitle'] = "Home";	//title for course list page
  $CFG['GEN']['headerinclude'] = "headercontent.php";  //page to include in all headers

  //$CFG['GEN']['noFileBrowser'] = true;  //disallow file uploads through the tinyMCE editor.

  //if you want to allow people to create guest accounts by just logging in with username "guest",
  //provide an arrary of course ids to automatically enroll them in
  $CFG['GEN']['guesttempaccts'] = array(264);

   //userid for instructor of student self-enroll courses (that show up in enroll list)
   $CFG['GEN']['selfenrolluser'] = 92434;

  //can set almost any assessment setting this way
	 $CFG['AMS']['defpoints'] = 1;  //default points
	 $CFG['AMS']['showtips'] = 2;   //entry answer format

	 $CFG['AMS']['guesslib'] = true;  //guess library based on where most questions are from

	 //and most of the gradebook settings
	 $CFG['GBS']['defgbmode'] = 1011;    //default gradebook mode
	 $CFG['GBS']['orderby'] = 1;         //default gradebook ordering
	 $CFG['GBS']['lockheader'] = true;   //lock headers?

	 //and course settings.  All but themelist are in the form
	 //array(defvalue, allowchange)
	 $CFG['CPS']['hideicons'] = array(0,0);
	 $CFG['CPS']['picicons'] =  array(1,0);
	 $CFG['CPS']['unenroll'] = array(0,0);
	 $CFG['CPS']['showlatepass'] = array(1,0);

	 $CFG['CPS']['additemtype'] = array('links',0);   //instead of pull-down
	 $CFG['CPS']['leftnavtools'] = false;      //hide roster, etc from left nav
	 $CFG['CPS']['templateoncreate'] = true;  //ask to template course on creation?

	 $CFG['CPS']['itemicons'] = array(
		 'folder'=>'folder_icon.png',
		 'assess'=>'assess_icon.png',
		 'forum'=>'forum_icon.png',
		 'inline'=>'text_icon.png',
		 'web'=>'link_icon.png',
		 'doc'=>'link_icon.png',
		 'html'=>'link_icon.png');   //custom icons

	 $CFG['CPS']['miniicons'] = array(
		 'inline'=>'text_mini.png',
		 'linked'=>'link_mini.png',
		 'assess'=>'assess_mini.png',
		 'forum'=>'forum_mini.png',
		 'folder'=>'folder_mini.png',
		 'calendar'=>'cal_mini.png');   //custom mini icons, for links display

	//custom icons for test display, in place of half-full box, etc
	 $CFG['TE']['navicons'] = array(
	 	 'untried'=>'te_blue_arrow.png',
	 	 'canretrywrong'=>'te_red_redo.png',
	 	 'canretrypartial'=>'te_yellow_redo.png',
	 	 'noretry'=>'te_blank.gif',
	 	 'correct'=>'te_green_check.png',
	 	 'wrong'=>'te_red_ex.png',
	 	 'partial'=>'te_yellow_check.png');
	 */

  //no need to change anything from here on
  /* Connecting, selecting database */
    // MySQL with PDO_MYSQL
     try {
      $DBH = new PDO("mysql:host=$dbserver;dbname=$dbname", $dbusername, $dbpassword);
      $DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
      // global $DBH;
      $GLOBALS["DBH"] = $DBH;
     } catch(PDOException $e) {
      die("<p>Could not connect to database: <b>" . $e->getMessage() . "</b></p></div></body></html>");
     }
		 $DBH->query("set session sql_mode=''");

	  unset($dbserver);
	  unset($dbusername);
	  unset($dbpassword);

  //clean up post and get if magic quotes aren't on
  function addslashes_deep($value) {
	return (is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value));
  }
  //DB if (!get_magic_quotes_gpc()) {
  //DB  $_GET    = array_map('addslashes_deep', $_GET);
  //DB  $_POST  = array_map('addslashes_deep', $_POST);
  //DB  $_COOKIE = array_map('addslashes_deep', $_COOKIE);
  //DB }
?>
