<?php
//login
const NO_PERMISSION = 'Nincs jogosultságod a oldalhoz/művelethez!';
const YES = 'Yes';
const NO = 'No';

const L_USERS = 'Users';
const L_USER = 'User';
const L_USERNAME = 'Username';
const L_NAME = 'Name';
const L_ROLE = 'Role';
const L_GROUP = 'Group';
const L_VERIFIED = 'Enabled';
const L_LAST_MODIFY = 'Last modify';
const L_LAST_MODIFY_WITH_ROLES = 'Last modify related to roles or permissions';
const L_PERMISSIONS = 'Permissions';


const L_USER_MODIFY = 'User modify';
const L_ADD_USER = 'Add user';
const L_CHOOSE = 'Choose..';
const L_PASSWORD = 'Password';
const L_REPEAT_PASSWORD = 'Repeat password';
const L_DELETE = 'Delete';
const L_ARE_U_SURE_DEL = 'Are you sure?';
const L_SAVE = 'Save';
const L_PASSWORD_RESET = 'Reset password';


const L_ROLES = 'Roles';
const L_MOVE = 'Move';
const L_SYSTEM_MODIFY_PERM = 'Permission for system modify (roles+groups)';
const L_USER_MODIFY_PERM = 'Permission for Users<br>(own datas and users who have lower role)';
const L_POST_MODIFY_PERM = 'Permission for Incidents<br>((own incidents and users\' who have lower role if there is a permission for the incident)';
const L_ALL_GROUP_PERM = 'Can manage all groups';
const L_LEAST_ROLE_CAN_SEE = 'The lowest role that can see its incidents defaultly (afterwards, it can be set separately)';

const L_LEAST_ROLE_CAN_SEE_POST = 'The lowest role that can see';


const L_LOGIN = 'Login';

const L_LISTEN_ON_MAP = 'Map with alarm';
const L_POSTS = 'Incidents';
const L_ADD_GROUP = 'Add group';
const L_MY_ACCOUNT = 'My account';
const L_MY_INCIDENTS = 'My incidents';
const L_LOGOUT = 'Logout';

const L_GROUPS = 'Groups';
const L_HIGHTEST_PERSON = 'the highest role person';
const L_EMAILS_ON_NEWS = 'Emails notified on new incidents';
const L_BACK_TO_GROUPS = 'Back to groups';
const L_SUCCESS_DELETE = 'Item has been successfully deleted!';
const L_SUCCESS_MODIFY  = 'Item has been successfully modified!';
const L_SUCCESS_ADDED  = 'Item has been successfully added!';

const L_MODIFY_GROUP = 'Modify group';
const L_NOTIFIED_EMAILS = 'Notified emails (comma separated)';
const L_NOTIFIED_TELNUM = 'Notified telnumbers at SOS (comma separated)';

const L_SMS_TEXT = 'SMS text';
const L_EMAIL_TEXT = 'Email text';

const L_ADD = 'Add';

const L_MODIFY_ROLE = 'Modify role';
const L_ADD_ROLE = 'Add role';

const L_BACK_TO_USERS = 'Back to users';

const PASSWORD_NOT_MATCHED = 'Password fields must match';

const PASSWORD_LESS = 'Password is too short';
const L_BACK_TO_PERMISSIONS = 'Back to permissions';

const L_ACCOUNT_DISABLED = 'Account is suspended!';
const L_WRONG_USER_PASSW = 'Wrong username or password!';

const L_DUPLICATED_OR_FOREIGN_KEY_C = 'Cannot be processed, maybe some data is still related to the item or it has to be unique data, please check it!';
const L_ERROR_OCCURED = 'An error occurred... try again!';

const L_REQUIRE = 'This field is required';
const L_MIN_LENGTH = 'Min length is';
const L_MAX_LENGTH = 'Max length is';
const L_MAX_CHAR = 'character';
const L_AT_LEAST_ONE_DIGIT = 'Password must contain digit and letter';

const L_ = 'Csoport';

const EVENT_TYPE = 'Event type';

const FIRST_COMMENT = 'User\'s first comment';

const ID = 'ID';

const SUBMITTER = 'User';


const STOPPED_AT = 'Stopped at';
const DOWNLOAD = 'Download';
const DOWNLOAD_ALL = 'Download all file and data (zip)';

const SOSLIVE = 'Live video (SOS)';
const LIVE = 'Live video';
const PHOTO = 'Photos';

const ALL = 'Összes';
const ALL_ = 'All...';

const COMMENTS = 'Comments';

//login vége-----------------------------------------------------------------------------------


//index.php
const LANG_FLAG = '<a class="flags" href="?lang=hu"><img alt="hu" src="/style/hu.png"></a>';

const PLEASE_LOGIN = 'Please log in!';
const HEAD_TEXT = "Facebook live video by one tap in case of emergency";

const WHAT_IS_IT = "What is it?";
const API = "Privacy policy";
const CONTACT = "Contact";
const LOGIN = "Login";
const LOGOUT = "Logout";
const MY_VIDEOS = "My videos";
const UNDER = "UNDER CONSTRUCTION...";

const SUMMARY = "SOSlive";
const AUTH = "Presence of authorities";
const FUTURE = "Future";

const WHAT_IS_IT_SHORT_OLD = '<p>Start Facebook live in case of emergency. The system consists <a target="_blank" href="https://play.google.com/store/apps/details?id=info.soslivebg"> SOSlive mobile app </a>
        and <a target="_blank" href="/">soslive.info</a> website. After launching <a target="_blank" href="https://play.google.com/store/apps/details?id=info.soslivebg"> SOSlive app </a> 
        you will be redirected to native Facebook app, and then the system will be "listening" your new live video and save its datas to soslive.info and show it 
        on <a target="_blank" href="/map">map</a>. 
        On the live video\'s soslive.info data-page: video file reference, snapshots, location data, and user\'s comments will be available and downloadable for 2 weeks 
        however you can hide your own items.

        It is important to set audience to "Public" for live video otherwise it will not be saved to soslive.info!
        For more information, future prospects and possibility of cooperation with authorities click <span class="footer-menu" data-target="faq-text">What is it?</span> in footer. </p>
        
        <div class="row">
        <div class="col-xs-0 col-md-2"></div>
        <div class="col-xs-12 col-md-8">
        <iframe width="100%" height="390" src="https://www.youtube.com/embed/XlLPskUmqrA" frameborder="0" allowfullscreen></iframe>
        </div>
        <div class="col-xs-0 col-md-2"></div>
        </div>';

const WHAT_IS_IT_SHORT = '<div style="color:#EF4040;">Only facebook test user (of its facebook app) can use this app yet! Its main functions work properly, of course.
If you want to be test user <span class="footer-menu" data-target="contact-text">contact</span> us.
</div>
<span class="bold">'.WHAT_IS_IT.'</span> <br>
After launching <a style="font-weight:bold" href="https://play.google.com/store/apps/details?id=info.soslive" taget="_blank">mobil app </a> you can start facebook live video by one tap 
and the app inserts the current live video\'s soslive.info data page\'s URL to the description 
automatically and soslive.info saves the live\'s: VIDEO FILE\'S URL, SNAPSHOTS, DESCRIPTION, USER\'S COMMENTS and LOCATION 
DATAS and  they can be downloadable.
Every datas will be permanently deleted after two weeks automatically but you can hide your own items.

Moreover, the website has a <a style="font-weight:bold" href="/map">map page</a>, and if live video starts in the set area 
it sounds a beep and blinking red point appears at the location. You can set the map up to listen only one user.
<br><br>
        <div class="row">
        <div class="col-xs-0 col-md-2"></div>
        <div class="col-xs-12 col-md-8">
            <iframe width="100%" height="390" src="https://www.youtube.com/embed/XlLPskUmqrA" frameborder="0" allowfullscreen></iframe>        </div>
        <div class="col-xs-0 col-md-2"></div>
        </div>';
        
const WHAT_IS_IT_TEXT = '

    <div id="wii-text" class="footer-text toggle-hash-inside">
        
        <h4>SOSlive</h4>
        <p style="font-weight: bold">In short: </p>
        ' . WHAT_IS_IT_SHORT . '
        <br><br>
        <p style="font-weight: bold">More: </p>
       
        <p>
        
        

        When you first use the application, you have to log in to Facebook and after launching the app you will be redirected automatically to native 
        Facebook application where you can start a live video.
        
        In the meantime, the app inserts a template text and a soslive link to the clipboard, whiches can be inserted into the description or comments.
        This soslive.info data page is available immediately and refreshes in every 30 seconds. There can be seen the snapshots, comments, location data on map and after the end of live video, you can download the video file. 
        </p>
        
        
        <p>
        <span class="bold">Video file:</span>
        
        From a finished live video, a facebook makes a video file (mp4) that can be downloaded from the direct Facebook URL available in soslive.info data page.
        </p>
        <p>
        
        <span class="bold">Snapshots:</span>
        
        The facebook takes a snapshot of live broadcasts per minute, which are saved by soslive.info so you can download them in zip file regardless of facebook post status.
        
        
        </p><p>
        <span class="bold">Location data:</span>
        During live video the page is saving your location (GPS or network based) and drawing point on map with time data.
        Similarly, these location data can be accessed independently of a facebook live post.
        
        
        </p><p>
        <span class="bold">Comments:</span>
        The user\'s comments during live video are also saved and can be downloaded in text format.
        </p>
        
         </div>
        <div id="auth-text" class="footer-text toggle-hash-inside">
                <div id="authorities">
                    <h3>Presence of authorities</h3>
                    <p> Official people at authorities can log in with authenticated facebook profiles which are authenticated by SOSlive on an official 
                    channel: phone, email, letter etc. 
                    They can initiate it at <span class="footer-menu" data-target="contact-text"> ' . CONTACT . '</span> section.
        
                    System does not save the given profiles\' datas (except of ID in App) and furthermore it does not log their activities 
                    just works with the current statuses and locations they are watching. So if the custom part of map is being watched by authorities 
                    a little siren <img src="/style/sirenicon.gif"> appears above of the map.
                    
                    Authenticated official people just open site and log in (and may take it to the background), and listen the beep sound 
                    which means that an SOSlive video is running in the set area and they can check it.
                    
                   
                       
                </div>
        </div>
                
        <div id="future-text" class="footer-text toggle-hash-inside">
                <h3>Future</h3>
                There is a  "brother" application in which you can start Facebook live video by two touches and text is pasted automatically to description  
                (the rest part of system is the same with current system).
               <br><br> <iframe width="560" height="315" src="https://www.youtube.com/embed/s1t6bYpn3kU" frameborder="0" allowfullscreen></iframe> <br><br>
                But, unfortunately, Facebook does not allow for any other mobil application to start facebook live stream and also prefill description text. 
                So it is in test mode, but as these obstacle are over, current mobile app will be replaced by this one.
        </div>';




//landing
const API_TEXT_OLD = '<h3>Privacy Policy</h3>



<span class="bold">Our system consists:</span><br>
"SOSlive Camera" Facebook app (integrated, for getting right to handle facebook video datas)<br>
SOSlive phone application  (which by users can start facebook live video)<br>
soslive.info web application (that collects and shows datas)<br>
<br>
<span class="bold">SOSlive Facebook app:</span><br>
It is implemented in SOSlive smart phone application(s). By accepting SOSlive Facebook app login form, we just take user\'s specific videos\' datas only, 
we do not write or override, 
so we have no influence on Facebook\'s staffs, in this topic please visit <a class="newwindow" href="https://www.facebook.com/policies" target="_blank"> Facebook privacy policy</a>.
<br><br>
<span class="bold">What personal information do we collect from the people that use SOSlive phone application?</span><br>
If you start a Facebook live video while the SOSlive phone application is running on the background your<br>
 - name<br>
 - facebook profile URL<br>
 - actual location data<br>
 - actual live video\'s source file\'s reference<br>
 - actual live video snapshots<br>
 - actual live video\'s own facebook comments<br> 
will be saved to our server (soslive.info) with public access default.
<br><br>
<span class="bold">How do we protect your information?</span><br>
Every datas stored by our are public default, but of course you have the possibility to hide your own datas from anyone. For Facebook authentication and transferring datas we use SSL connection.
<br><br>
<span class="bold">How do we use your information?</span><br>
We just collect and show these information to the public because, in case of emergency, these informations might be useful for help. 
<br><br>
<span class="bold">How long do we retain datas?</span><br>
Every datas will be permanently deleted (only from soslive.info of course) after 2 weeks from creating live video, but you can hide own datas from everyone.
<br><br>
<span class="bold">What if facebook live video post has been deleted?</span><br>
The source file will be available for a while (appr. one day depends on Facebook CDN system) but location data, snapshots, comments will be available to expiry.
<br><br>
<span class="bold">Can user send requests to delete data before expiry?</span><br>
No, user only can hide own datas but also can <a href="/contact">report a video </a>  to remove if it contains illegal contents.
<br><br>
<span class="bold">Do we use \'cookies\'?</span><br>
We do not use cookies but Facebook uses: <a class="newwindow" target="_blank" href="https://www.facebook.com/policies/cookies">Facebook policy for cookies</a>
<br><br>
<span class="bold">Third-party disclosure</span><br>
We do not sell, trade, or otherwise transfer to outside parties your Personally Identifiable Information.
<br><br>

If there are any questions regarding this privacy policy, you may <a href="/contact"> ' . CONTACT . '</a> .
<br><br>
soslive.info
<br><br>
Last Edited on 2017-07-17

';

const API_TEXT = '<h3>Privacy Policy</h3>



<span class="bold">Our system consists:</span><br>
SOSlive phone application  (users can start facebook live video by it)<br>
soslive.info web application (that collects and shows datas)<br>
"SOSlive Camera" Facebook app (integrated, for getting right to start facebook live and handle facebook videos)<br>

<br>
<span class="bold">What personal information do we collect from the people that use our system?</span><br>
After starting Facebook live video by SOSlive phone application:<br>
 - actual location datas until live ends<br>
 - (Faceook) name<br>
 - (Faceook)  profile URL<br>
 - (Faceook) actual live video\'s source file\'s reference<br>
 - (Faceook) actual live video snapshots<br>
 - (Faceook) actual live video\'s comments creatd by user<br> 
 - (Faceook) actual live video\'s description<br> 
will be saved with public access default.
<br><br>
<span class="bold">How do we protect your information?</span><br>
Every datas stored by our are public default, but of course you have the possibility to hide your own datas from anyone. For Facebook authentication and transferring datas we use safe SSL connection.
<br><br>
<span class="bold">How do we use your information?</span><br>
We just collect and show these information to the public because, in case of emergency, these informations might be useful for help. 
<br><br>
<span class="bold">How long do we retain datas?</span><br>
Every datas will be permanently deleted (only from soslive.info of course) after 2 weeks from creating live video, but you can hide own datas from everyone.
<br><br>
<span class="bold">What if facebook live video post has been deleted?</span><br>
The source file will be available for a while (appr. one day depends on Facebook CDN system) but location data, snapshots, comments will be available to expiry.
<br><br>
<span class="bold">Can user send requests to delete data before expiry?</span><br>
No, user only can hide own datas but also can <a href="/contact">report a video </a>  to remove if it contains illegal contents.
<br><br>
<span class="bold">Do we use \'cookies\'?</span><br>
We do not use cookies but Facebook uses: <a class="newwindow" target="_blank" href="https://www.facebook.com/policies/cookies">Facebook policy for cookies</a>
<br><br>
<span class="bold">Third-party disclosure</span><br>
We do not sell, trade, or otherwise transfer to outside parties your Personally Identifiable Information.
<br><br>

If there are any questions regarding this privacy policy, you may <a href="/contact">contact</a> us.
<br><br>
soslive.info


';
const PROFILE_OR_VIDEO_LINK = 'Facebook <span style="color: #fff; background-color:#3b5998;padding: 2px 4px;border-radius: 3px">Profile</span> OR <span style="color: #fff; background-color:#EF4040;padding: 2px 4px;border-radius: 3px">LIVE video</span> URL';





//user.php
const VIDEO_VIA_SOSLIVE = 'Facebook LIVE videos (via SOSlive app):';
const CREATED = 'Created';
const LENGTH = 'Length';
const POST_URL = 'Facebook Live video post';
const MIN = 'min';
const FB_POST_URL = 'Facebook Live video post';
const REMOVE_AFTER_TIME = 'Delete in 24hours';
const HIDE = 'hide';
const PUBLISH = 'publish';

//video.php
const NO_RESULT = 'No results found';
const NO_RESULT_OR_NO_PERM = 'No results found or no permission';
const NO_VIDEOS_YET = 'You have not created Facebook live yet via <span class="webview"> <a target="_blank" href="https://play.google.com/store/apps/details?id=info.soslive">SOSlive app</a></span>!';
const LISTEN_USER_ON_MAP = 'Watching user\'s live video on map (alarming when new live starts)';
const USER_VIDEO_ON_MAP = 'Watchin on map (and alarming when new live starts)';
const TODAY = 'today';
const JUST_TEST = 'TEST, THERE IS NO EMERGENCY';
const DELETE_AT = 'Deleted at';
const LIVE_IS_RUNNING = 'Live is running or not processed...';
const STATUS_TRY_TO_GET_LINK = 'STATUS: STOPPED, trying to get video file\'s URL...';
const STATUS_LINK_SAVED = 'STATUS: STOPPED, video file\'s URL has been saved!';
const STATUS_POST_DELETED_HAS_LINK = 'STATUS: video post is not public or deleted, video file\'s URL is available for a while!';
const STATUS_POST_AND_LINK_DELETED = 'STATUS: video post is not public or deleted, video file\'s URL is not available anymore!';
const STATUS_CANT_GET_LINK = 'STATUS: STOPPED, video file\'s URL has NOT been saved!';
const NEW_UPPER = 'NEW';
const BEGIN_UPPER = 'IS RUNNING';
const VIDEO_FILE = 'Video file';
const NO_VIDEO_FILE = 'Video file is not available';
const DOWNLOAD_VIDEO_FILE = 'Download video file';
const NO_START_VIDEO = 'STATUS: Video stream did not start!';

const DESCRIPTION = 'Description';

const SCREENSHOTS = 'Snapshots';
const DOWNLOAD_SCREENSHOTS = 'Download snapshots';
const DOWNLOAD_SCREENSHOTS_TO = "Download snapshot created so far";
const NO_SCREENSHOTS = 'No snapshot';

const LOCATION_DATAS = 'Location datas';
const DOWNLOAD_LOCATIONS = 'Download location datas';
const NO_LOCATION_DATA = 'No location data';

const DOWNLOAD_COMMENTS = 'Download user\'s comments';
const NO_COMMENTS = 'No comments by user';
const DOWNLOAD_COMMENTS_TO = 'Download user\'s comments so far';


//maps and screenshots
const REFRESH = 'Refresh';
const STOP_IT = 'stop';
const RESTART = 'restart';
const SCREENSHOTS_NEWER_AT_TOP = 'Snapshots';
const LOCATION_DATA_BY_PHONE = 'Location by phone (GPS or network)';
const CLICK_TO_LOC = 'Click the location/time';
const LAST_LOCATION_AND_TIME = 'Time: ';
const AT_TIME = 'time:';
const USERS_COMMENTS = 'User\'s comments';
const SETTLEMENT = 'Settlement';
const WATCH_BY_AUTH = "This area is being watched by authoriti(es)! (JUST TEST)";

const MAP_LABEL = 'Facebook lives (via SOSlive) on map with sound alarm <img  id="speaker" height="18px" src="/style/speaker.png">';
const MAP_DESC = '<span class="bold">Posts over the past 6 hours</span><br>If new post is submitted in the set area it sounds a beep <img id="speaker" alt="speaker" style="cursor: pointer; height:18px" src="/style/speaker.png"> and 
<img alt="blink" src="/style/blinking_dot_1.gif"> appears at the location.';
//' . (!$onlySos ? '<img  alt="blink" src="/style/blinking_dot_4.gif"> or  ' : '' ) .'

const NO_LOCATION_DATA_YET =  "No location data has been sent yet!";
const NO_SNAPSHOT_DATA_YET =  "No snapshot has been created yet!";
const NO_COMMENTS_DATA_YET =  "User has not commented the post yet!";


const LOGIN_BUTTON = 'Login to soslive.info with Facebook';
const ACCEPT_PRIVACY = '
    <span style="color: #EF4040">My facebook profil was previously added as "test user" <span class="footer-menu" data-target="faq-text">(info)</span> </span> and 
     I accept soslive.info\'s processes mentioned in 
    <span class="footer-menu" style="text-align: center;font-weight: bold;color: #337ab7;cursor: pointer;" data-target="api-text"> 
    Privacy policy
    </span> and accept Facebook <a target="_blank" style="font-weight: bold;color: #337ab7;" href="https://www.facebook.com/policies/cookies/"> Cookies policy </a>. ';

const START_FACEBOOK ='Start Facebbok for live';
                     
const BY_TAPPING = 'By tapping the button starting Facebook live <br> video\'s datas (start within 5 minutes) will be saved to soslive.info';
const CREATED_LOCAL = 'Live started at (local time)';
const NAME = 'Name';

//Contact
const YOUR_NAME= 'Your name';
const YOUR_EMAIL= 'Your email';
const YOUR_CONTACT = 'Your contact';
const SUBJECT= 'Subject';
const REPORT_VIDEO= 'Report a video';
const TECHNICAL_ISSUE= 'Technical issue';
const ADVICE= 'Advice';
const OTHER= 'Other';
const MESSAGE= 'Message';
const AUTHORIES = 'I am an officer of the authorities';
const APPLY_FOR_TEST = 'I want to be test user';

const ERROR_REPORT = 'Report an error';

const MESSAGE_SENT= 'Message has been sent!';
const MESSAGE_NOT_SENT= 'Error, message has not been sent!';
const SEND_MESSAGE = 'Send message';
const PRIVACY = 'Privacy';


