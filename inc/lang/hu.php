<?php

//login

const NO_PERMISSION = 'Nincs jogosultságod a oldalhoz/művelethez!';
const YES = 'Igen';
const NO = 'Nem';

const L_USERS = 'Állomány';
const L_USER = 'Felhasználó';
const L_USERNAME = 'Felhasználónév';
const L_NAME = 'Név';
const L_ROLE = 'Szerepkör';
const L_GROUP = 'Csoport';
const L_VERIFIED = 'Engedélyezve';
const L_LAST_MODIFY = 'Utolsó módosítás';
const L_LAST_MODIFY_WITH_ROLES = 'Szerepköröket vagy jogosultságokat érintő utolsó módosítás';
const L_PERMISSIONS = 'Jogosultságok';

const L_USER_MODIFY = 'Felhasználó módosítása';
const L_ADD_USER = 'Új felhasználó';
const L_CHOOSE = 'Válassz..';
const L_PASSWORD = 'Jelszó';
const L_REPEAT_PASSWORD = 'Jelszó mégegyszer';
const L_DELETE = 'Törlés';
const L_ARE_U_SURE_DEL = 'Biztos törli?';
const L_SAVE = 'Mentés';
const L_PASSWORD_RESET = 'Jelszó megváltoztatása';

const L_ROLES = 'Szerepkörök';
const L_MOVE = 'Mozgatás';
const L_SYSTEM_MODIFY_PERM = '<span data-toggle="tooltip" title="Csoportok, szerepkörök módosítása, és az alatta lévő szerepkörök jogosultságainak beállítása.">Rendszer módosítási jog <span class="glyphicon glyphicon-info-sign"></span></span>';
const L_USER_MODIFY_PERM = '<span data-toggle="tooltip" title="Saját magát és a csoportjában az alatta lévő felhasználókat.">Felhasználó módosítási jog <span class="glyphicon glyphicon-info-sign"></span></span>';
const L_POST_MODIFY_PERM = '<span data-toggle="tooltip" title="Saját eseményeit és a csoportjában az alatta lévő userek eseményeit, amennyiben van az adott eseményre láthatási joga.">Esemény módosítási jog <span class="glyphicon glyphicon-info-sign"></span></span>';
const L_ALL_GROUP_PERM = 'Összes csoporthoz hozzáfér';
const L_LEAST_ROLE_CAN_SEE = '<span data-toggle="tooltip" title="Eseményenként külön módosítható utólag.">A legalacsonyabb szerepkör<br> amely látja az eseményeit <span class="glyphicon glyphicon-info-sign"></span></span>';

const L_LEAST_ROLE_CAN_SEE_POST = 'A legalacsonyabb szerepkör amely láthatja';

const L_LOGIN = 'Bejelentkezés';

const L_LISTEN_ON_MAP = 'Térképes eseményfigyelő';
const L_POSTS = 'Események';
const L_ADD_GROUP = 'Új csoport';
const L_MY_ACCOUNT = 'Adataim';
const L_MY_INCIDENTS = 'Eseményeim';
const L_LOGOUT = 'Kijelentkezés';

const L_GROUPS = 'Csoportok';
const L_HIGHTEST_PERSON = 'Rangidős';
const L_EMAILS_ON_NEWS = 'Értesítési email(ek) új esménynél';
const L_BACK_TO_GROUPS = 'Vissza a csoportokhoz';

const L_BACK_TO_GROUP = 'Vissza a csoporthoz';
const L_SUCCESS_DELETE = 'Sikeres törlés!';
const L_SUCCESS_MODIFY  = 'Sikeres módosítás!';
const L_SUCCESS_ADDED  = 'Sikeres hozzáadás!';

const L_MODIFY_GROUP = 'Csoport módosítása';
const L_NOTIFIED_EMAILS = 'Értesítési emailek (vesszővel elválasztva)';

const L_NOTIFIED_TELNUM = 'SOSnél értesítési telefonszámok (vesszővel elválasztva)';

const L_SMS_TEXT = 'SMS szövege';
const L_EMAIL_TEXT = 'Email szövege';

const L_ADD = 'Hozzáadás';

const L_MODIFY_ROLE = 'Szerepkör módosítása';
const L_ADD_ROLE = 'Új szerepkör';

const L_BACK_TO_USERS = 'Vissza az felhasználókhoz';

const PASSWORD_NOT_MATCHED = 'Nem egyezik meg a két jelszó!';
const PASSWORD_LESS = 'Jelszó túl rövid!';
const L_BACK_TO_PERMISSIONS = 'Vissza a jogosultságokhoz';

const L_ACCOUNT_DISABLED = 'Hozzáférés felfüggesztve!';
const L_WRONG_USER_PASSW = 'Rossz felhasználónév vagy jelszó!';

const L_DUPLICATED_OR_FOREIGN_KEY_C = 'Nem sikerült a művelet! Talán még függnek tőle adatok, vagy (feltöltés esetén) egyedi adatot követel meg a rendszer!';
const L_ERROR_OCCURED = 'Hiba lépett fel a művelet közben!';

const L_REQUIRE = 'Kötelező kitölteni!';
const L_MIN_LENGTH = 'Minimum hossz';
const L_MAX_LENGTH = 'Max hossz';
const L_MAX_CHAR = 'karakter';
const L_AT_LEAST_ONE_DIGIT = 'Számnak és betűnek is kell szerepelnie benne!';

//login vége

const EVENT_TYPE = 'Esemény típusa';

const FIRST_COMMENT = 'A beküldő első hozzászólása';

const SUBMITTER  = 'Beküldő';

const ID = 'Azon.';


const STOPPED_AT = 'Megállítva';
const DOWNLOAD_ALL = 'Összes fájl és adat letöltése (zip)';

const DOWNLOAD = 'Letöltés';


const SOSLIVE = 'Live videó (SOS)';
const LIVE = 'Live videó';
const PHOTO = 'Fényképek';

const ALL_ = 'Összes...';
const ALL = 'Összes';

const COMMENTS = 'Hozzászólások';

//index.php

const HEAD_TEXT = "Facebook live videó veszélyhelyzetben egy gombnyomással";

const PLEASE_LOGIN = 'A folytatáshoz jelentkezzen be!';
const LANG_FLAG = '<a class="flags" href="?lang=en"><img alt="hu" src="/style/en.gif"></a>';
const WHAT_IS_IT = "Mi ez?";
const API = "Adatvédelem";
const CONTACT = "Kapcsolat";
const LOGIN = "Belépés";
const LOGOUT = "Kilépés";
const MY_VIDEOS = "Videóim";
const UNDER = "FEJLESZTÉS ALATT...";

const SUMMARY = "SOSlive";
const AUTH = "Hatósági jelenlét";
const FUTURE = "Jövő";
const WHAT_IS_IT_SHORT_OLD = 'Facebook live videó indítása veszélyhelyzetben. Az SOSlive rendszer az <a target="_blank" href="https://play.google.com/store/apps/details?id=info.soslivebg"> 
            SOSlive mobil applikációból </a> és az soslive.info weboldalból áll. Az soslive.info weboldalon az SOSlive mobil applikación keresztül indított facebook
            live videók adatait lehet megtekinteni és letölteni (helyadat, videó-fájl hivatkozás, pillanatképek, hozzászólások). 
            A live videó leírásába beilleszthető  formaszövegben egy link az aktuális videó soslive.info adat-oldalára mutat.
            A website továbbá egy <a href="/map">térképes oldalon</a> összegzi a videókat, és ha a térképen beállított területen belül új live indul, villogó pont tűnik fel a hely felett, és hangjelzéssel is riaszt. 
            </p><p>veszélyh
            
            <div class="row">
        <div class="col-xs-0 col-md-2"></div>
        <div class="col-xs-12 col-md-8">
        <iframe width="100%" height="390" src="https://www.youtube.com/embed/XlLPskUmqrA" frameborder="0" allowfullscreen></iframe>
        </div>
        <div class="col-xs-0 col-md-2"></div>
        </div>';
        
const WHAT_IS_IT_SHORT = '
<div style="color:#EF4040;">
Az appot egyelőre csak a Facebookon beállított "teszt felhasználók" tudják használni. Ha te is szeretnéd tesztelni, küldd el a facebook profilod webcímét: <span class="footer-menu" data-target="contact-text">kapcsolat</span>.<br>
</div><br><p>
<span class="bold">'.WHAT_IS_IT.'</span> <br>
<a style="font-weight:bold" href="https://play.google.com/store/apps/details?id=info.soslive" taget="_blank">Mobil applikáció</a>, mellyel facebook live videót tudunk indítani veszélyhelyzetben. Az <a style="font-weight:bold" href="https://play.google.com/store/apps/details?id=info.soslive" taget="_blank">app</a> futtatása után egy gombnyomással 
el tudjuk indítani a live-ot, és a program automatikusan beilleszti a videóhoz tartozó soslive.info <span class="bold">adatoldal</span> webcímét a live poszt leírásába. Ez az <span class="bold">adatoldal</span> az indítás után másodperceken belül elkészül,
és itt a videóhoz tartozó FÁJL WEBCÍMÉT, PILLANATKÉPEKET, FELHASZNÁLÓ ÁLTAL ÍRT HOZZÁSZÓLÁSOKAT és HELYADATOKAT a rendszer folyamatosan frissíti, és le is lehet tölteni őket. 
Két hét után véglegesen törlődnek az adatok (csak az oldalon, a facebookon nem), de a saját live videó mentéseket addig is el lehet rejteni (az applikációból tudjuk elérni őket).
A website továbbá egy <a style="font-weight:bold" href="/map">térképes oldalon </a> összegzi a videókat, és ha a térképen beállított területen belül új live indul, 
villogó pont tűnik fel a hely felett, és hangjelzéssel is riaszt. 
Ezt a térképet le lehet szűkíteni 1 felhasználó live videóinak figyelésére is.<br><br>
    
    <div class="row">
<div class="col-xs-0 col-md-2"></div>
<div class="col-xs-12 col-md-8">
<iframe width="100%" height="390" src="https://www.youtube.com/embed/XlLPskUmqrA" frameborder="0" allowfullscreen></iframe>
</div>
<div class="col-xs-0 col-md-2"></div>
</div></p>';

        
const WHAT_IS_IT_TEXT = '

        <div id="wii-text" class="footer-text toggle-hash-inside">
            
           
        
            <h3>SOSlive</h3>
            
            <p>
            <h4>Röviden:</h4>
            ' . WHAT_IS_IT_SHORT . '
            
            
            <h4>Hosszabban:</h4>
            <p>
            <span class="bold">SOSlive mobil applikáció:</span> telepítés utáni első használatkor be kell jelentkezni Facebookon, és ezután bármikor elindítva az appot automatikusan a Facebook applikációra irányít, ahol el lehet indítani a live videót. 
            E közben az app vágólapra tesz egy sablonszöveget, és a linket a videó soslive.info-s adat-oldalához, és ezt egyben be lehet illeszteni a leírásba vagy hozzászólásba. 
            Ez az adat-oldal az indítást követően azonnal elérhető, és ott félpercenként frissítve láthatóak a videó PILLANATKÉPEI, a felhasználó HOZZÁSZÓLÁSAI továbbá a telefon által meghatározott térképen jelölt HELYADAT. 
            A weboldal a live videó adás befejezése után a hozzátartozó VIDEÓ-FÁJL webcímét is elmenti.</p><p>
            
             <span class="bold">Videó-fájl:</span> A befejezett live videóból a facebook készít egy videó-fájlt (mp4), amely letölthető az soslive.info-n feltüntetett közvetlen webcímről.</p><p>
            
             <span class="bold">Pillanatképek:</span> A faceebook kb. percenként készít pillanatképeket az élő adásról, ezeket az soslive.info elmenti, tehát az adott live video post meglététől függetlenül megtekinthetőek, és zip fájlban letölthetőek.
            </p><p>
             <span class="bold">Helyadat:</span> A telefon (GPS vagy hálózat alapú) által meghatározott helyadatot elmenti az oldal, és térképen fel is tünteti.
            </p><p>
             <span class="bold">Hozzászólások:</span> A felhasználó live alatt írt hozzászólásait szintén elmenti a rendszer, és le is lehet tölteni szöveges (txt) formátumban.
            </p>
          
            
        </div>
        <div id="auth-text" class="footer-text toggle-hash-inside">
                <div id="authorities">
                <h3>Hatósági jelenlét</h3>
                <p>A hatósági személyeknek lehetőségük van igazolni hitelesített csatornán (pl.: vonalas telefon, hivatalos email, postai levél), hogy egy adott facebook profil hozzájuk tartozik.
                Ezt az eljárást a  <span class="footer-menu" data-target="contact-text"> ' . CONTACT . '</span> résznél lehet kezdeményezni. 
                A rendszer nem menti le ezen hitelesített facebook profilok adatait (csak az appon belüli azonosítóikat), 
                továbbá nem naplózza a tevékenységüket, csupán az aktuális státuszukkal operál (a jelenlévők mely területeket nézik épp).
                </p><p>Ha egy kiválasztott térképrészletet teljesen lefednek ezek a területek, a térkép felett ezt jelzi is a rendszer: <img src="/style/sirenicon.gif">
                (pl.: ha a hatósági felhasználók megfigyelt területei egész Magyarországot lefedik, akkor ezen belül bárhova közelítünk a térképen, azt fogja jelezni, hogy a terület hatósági személy(ek) által is figyelve van).
                A hitelesített hatósági felhasználóknak csak annyi dolguk van, hogy (akár a háttérben) megnyitják az oldalt, és belépnek. Ezután csak a hangjelzésre kell figyelniük
                ami jelzi nekik, ha a beállított területen belül indítottak SOSlive videót és le tudják ellenőrizni.
                </p>
                </div>
        </div>
                
        <div id="future-text" class="footer-text toggle-hash-inside">
                <h3>Jövő</h3>
                Létezik egy "testvér" applikáció, amely csupán 2 gombnyomás után elindítja a facebook live videót a linket tartalmazó sablonszöveget tartalmazva, (ezenkívül ugyananúgy működik minden),
               <br><br> <iframe width="560" height="315" src="https://www.youtube.com/embed/s1t6bYpn3kU" frameborder="0" allowfullscreen></iframe> <br><br>
                de sajnos jelenleg a Facebook nem engedi, hogy facebook live streamet indítson bármely más mobil applikáció, és azt sem, hogy egy posthoz a rendszer automatikusan kitöltse 
                a leírást, tehát hogy ne a felhasználó adja meg a leírás szövegét. 
                Így ez az applikáció csak teszt üzemben létezik, de amint elhárulnak ezek az akadályok, ez az applikáció fog a mostani helyébe lépni.
        
        </div>

';




//landing
const API_TEXT_OLD = '<h3>Adatvédelmi információk</h3>


Kérjük, figyelmesen olvassa el, mert a rendszer használatának feltétele ennek a szövegnek az elfogadása:<br><br>
<span class="bold">A rendszerünk a következő összetevőkből áll:</span>
SOSlive telefon applikáció  (amely elindítja a live videót)<br>
soslive.info web application (amely összegyűjti és megmutatja az adatokat)<br>
SOSlive Facebook applikáció (a live-hoz és videókhoz való jogosultság megszerzéséhez)<br>
<br>
<span class="bold">SOSlive Facebook app:</span><br>
It is implemented in SOSlive smart phone application(s). By accepting SOSlive Facebook app login form, we can get user\'s public video datas only, so we have no influence on Facebook\'s staffs, in this topic please visit facebook privacy policy.
<br><br>
<span class="bold">OWhat personal information do we collect from the people that use SOSlive phone application?</span><br>
If you start a Facebook live video while the SOSlive phone application is running on the background your<br>
 - name<br>
 - facebook profile URL<br>
 - actual location datas<br>
 - actual live video\'s source file\'s reference<br>
 - actual live video screenshots<br>
 - actual live video facebook comments<br> 
will be saved to our server (soslive.info) with public access.
<br><br>
<span class="bold">How do we protect your information?</span><br>
Every datas we store are public, but for Facebook authentication and transferring datas we use SSL connection.
<br><br>
<span class="bold">How do we use your information?</span><br>
We just collect and show these information to the public because, in emergency, these informations can be useful for helping. 
<br><br>
<span class="bold">How long do we retain datas?</span><br>
Every datas will be deleted (only from soslive.info of course) after 4 weeks from creating live video.
<br><br>
<span class="bold">What if facebook live video post will be deleted?</span><br>
The source file will be available for a while (appr. one day depends on Facebook cdn system) but location datas, screenshots, comments will be  available to expiry.
<br><br>
<span class="bold">Can user send requests to delete data before expiry?</span><br>
It has no effect, except of illegal content.
<br><br>
<span class="bold">Do we use \'cookies\'?</span><br>
We do not use cookies.
<br><br>
<span class="bold">Third-party disclosure</span><br>
We do not sell, trade, or otherwise transfer to outside parties your Personally Identifiable Information.
<br><br>

If there are any questions regarding this privacy policy, you may contact us using the information below.
<br><br>
soslive.info
<br><br>
Last Edited on 2017-06-30


';

const API_TEXT = '<h3>Adatvédelmi információk</h3>


<span class="bold">Rendszerünk a következő összetevőkből áll:</span><br>
<a href="play.google.com/store/apps/details?id=info.soslive" target="_blank">SOSlive telefon applikáció</a>  (amelyen elindítja a live videót a felhasználó)<br>
<a href="/" target="_blank">soslive.info</a> webalkalmazás (amely összegyűjti és megmutatja az adatokat)
"SOSlive Camera" Facebook applikáció (integráltan, a live-hoz és videókhoz való jogosultság megszerzéséhez)<br>

<br><br>
<span class="bold">Milyen személyes adatokat mentünk le a rendszert használó felhasználóktól?</span><br>
A live videó elindítását követően ezeket az adatokat mentjük le: <br>
 - facebook live videó befejezéséig regisztrált helyadatokat<br>
 - (Faceook) felhasználói név<br>
 - (Faceook) profil webcím<br>
 - (Faceook) live videó fájljának a webes elérhetőségét<br>
 - (Faceook) live videó pillanatképeit<br>
 - (Faceook) live videó leírását<br>
 - (Faceook) live videó hozzászólásait, amelyeket a felhasználó írt<br>
<br>
<span class="bold">Hogyan védjük meg a személyes adatokat?</span><br>
Az adatok alapértelmezetten nyilvánosak, de természetesen Facebook alapú bejelentkezés után lehetőség van elrejteni a saját adatokat.
A mobil applikációval és más szerverekkel való kommunikációhoz biztonságos SSL kapcsolatot használunk.
<br><br>
<span class="bold">Hogy használjuk az adatokat?</span><br>
Mi csak lementjük, és lehetőséget adunk az adatok hozzáféréséhez bárkinek, mert veszély esetén ezek az adatok segítségül szolgálhatnak.
<br><br>
<span class="bold">Mennyi ideig őrizzük meg az adatokat?</span><br>
Minden adat a keletkezésétől számított 14. napon véglegesen törlődik, de addig is a saját adataidat el tudod rejteni.
<br><br>
<span class="bold">Mi van, ha a facebook live video postot törlik, vagy privátra állítják az elérhetőségét?</span><br>
A live videóhoz tartozó fájl webcíme élni fog még néhány óráig  (esetleg napig, a facebook cdn rendszerétől függően), 
viszont a helyadatok, pillanatképek, a leírás és a felhasználói kommentek a facebook poszttól függetlenül az soslive.info oldalon maradnak a már említett elévülési ideig.

<br><br>
<span class="bold">Van-e lehetőség jelenteni videót?</span><br>
Igen, itt meg lehet tenni: <a href="/contact">kapcsolat </a>, és töröljük is a hozzátartozó adatokat, ha illegális tartalom merül fel.
<br><br>
<span class="bold">Használunk \'cookie\'-kat?</span><br>
Mi nem használunk \'cookie\'-kat, de a Facebook igen (az oldalra Facebook segítségével lehet bejelentkezni):  <a class="newwindow" target="_blank" href="https://www.facebook.com/policies/cookies">Facebook policy for cookies</a>
<br><br>
<span class="bold">Harmadik fél</span><br>
Nem adjuk át harmadik félnek a rendszerben található személyes adatokat (amelyek alapértelmezetten egyébként nyilvánosak).
<br><br>

Ha felmerült még kérdés az adatvédelemmel kapcsolatban, itt lehet feltenni:  <a href="/contact">kapcsolat</a> .
<br><br>
soslive.info



';



const PROFILE_OR_VIDEO_LINK = 'Facebook <span style="color: #fff; background-color:#3b5998;padding: 2px 4px;border-radius: 3px">Profil</span> VAGY <span style="color: #fff; background-color:#EF4040;padding: 2px 4px;border-radius: 3px">LIVE videó</span> linkje';





//user.php
const VIDEO_VIA_SOSLIVE = 'Facebook LIVE videók (SOSlive appon keresztül):';
const CREATED = 'Létrehozva';
const LENGTH = 'Hossz';
const POST_URL = 'Videó post webcíme';
const MIN = 'perc';
const FB_POST_URL = 'Facebook Live videó post';
const REMOVE_AFTER_TIME = 'Törlés 24 óra múlva';
const HIDE = 'elrejtés';
const PUBLISH = 'közzézétel';

//video.php
const NO_RESULT = 'Nincs találat';
const NO_RESULT_OR_NO_PERM = 'Nincs találat vagy nincs jogosultság';
const NO_VIDEOS_YET = 'Még nem készítettél Facebook live videót <span class="webview"><a target="_blank" href="https://play.google.com/store/apps/details?id=info.soslive">SOSlive appon</a> keresztül</span>!';
const LISTEN_USER_ON_MAP = 'Felhasználó live videóinak térképes figyelése (új live videónál hangjelzést ad)';
const USER_VIDEO_ON_MAP = 'Térképes nézet (új live videónál hangjelzést is ad)';
const TODAY = 'ma';
const JUST_TEST = 'TESZTELÉS, NINCS VÉSZHELYZET';
const DELETE_AT = 'Törölve';
const LIVE_IS_RUNNING = 'A live még nem ért véget, vagy még nincs feldolgozva';
const STATUS_TRY_TO_GET_LINK = 'STÁTUSZ: VÉGE, kísérlet a videó-fájl linkjének a megszerzésére...';
const STATUS_LINK_SAVED = 'STÁTUSZ: VÉGE, videó-fájl linkje elmentve!';
const STATUS_POST_DELETED_HAS_LINK = 'STÁTUSZ: poszt nem publikus vagy törölve, videó-fájl linkje egy darabig elérhető!';
const STATUS_POST_AND_LINK_DELETED = 'STÁTUSZ: poszt nem publikus vagy törölve, és a videó-fájl linkje sem elérhető már!';
const STATUS_CANT_GET_LINK = 'STÁTUSZ: VÉGE, nem sikerült lementeni a videó-fájl linkjét!';
const NEW_UPPER = 'ÚJ';
const BEGIN_UPPER = 'INDULT';
const VIDEO_FILE = 'Videó-fájl';
const NO_VIDEO_FILE = 'Videó-fájl nem elérhető';
const DOWNLOAD_VIDEO_FILE = 'Videó-fájl letöltése';
const NO_START_VIDEO = 'STÁTUSZ: Videó stream nem indult el!';

const DESCRIPTION = 'Leírás';

const SCREENSHOTS = 'Pillanatképek';
const DOWNLOAD_SCREENSHOTS = 'Pillanatképek letöltése';
const DOWNLOAD_SCREENSHOTS_TO = "Eddig készült pillanatképek letöltése";
const NO_SCREENSHOTS = 'Nincsenek pillanatképek';

const LOCATION_DATAS = 'Helyadat';
const DOWNLOAD_LOCATIONS = 'Helyadatok letöltése';
const NO_LOCATION_DATA = 'Nincsenek helyadatok';
const DOWNLOAD_COMMENTS = 'Felhasználó hozzászólásainak letöltése';
const NO_COMMENTS = 'A felhasználó nem szólt hozzá a videóhoz';
const DOWNLOAD_COMMENTS_TO = 'Felhasználó eddigi hozzászólásainak letöltése';


//maps and screenshots
const REFRESH = 'Frissítés';
const STOP_IT = 'álljon meg';
const RESTART = 'újraindítás';
const SCREENSHOTS_NEWER_AT_TOP = 'Pillanatképek';
const LOCATION_DATA_BY_PHONE = 'Helyadat a telefon alapján (GPS vagy hálózat)';
const CLICK_TO_LOC = 'Katt a hely/időpontra';
//const LAST_LOCATION_AND_TIME = 'Utoljára elküldött pozíció, (időpont: ';
const WATCH_BY_AUTH = "Ezt a területet figyeli(k) hatósági személy(ek) is! (CSAK TESZT)";

const LAST_LOCATION_AND_TIME = 'Időpont: ';

const SETTLEMENT = 'Település';

const AT_TIME = 'időpont:';
const USERS_COMMENTS = 'Felhasználó hozzászólásai';
const MAP_LABEL = 'Facebook live-ok (SOSlive-ról indítva) térképen, riasztással <img   id="speaker" height="18px" src="/style/speaker.png">';
const MAP_DESC = '<span class="bold">Események az elmúlt 6 órában</span><br>Ha a kinagyított területen belül beküldenek egy eseményt, az oldal hangjelzést ad le  <img id="speaker" alt="speaker" style="cursor: pointer; height: 18px" src="/style/speaker.png"> , és a hely felett 
<img alt="blink" src="/style/blinking_dot_1.gif"> jelenik meg.';
//' . (!$onlySos ? '<img alt="blink" src="/style/blinking_dot_4.gif"> vagy ' : '' ) .'


const NO_LOCATION_DATA_YET =  "Nincsen helyadat a videóhoz jelenleg!";
const NO_SNAPSHOT_DATA_YET =  "Nincsenek pillanatképek a videóhoz jelenleg!";
const NO_COMMENTS_DATA_YET =  "Nincsenek a felhasználó által beküldött hozzászólások a videóoz jelenleg!";

const LOGIN_BUTTON = 'Belépés az soslive.info-ra Facebookkal';
const ACCEPT_PRIVACY = '
    <span style="color: #EF4040">Fel lett véve előzetesen a Facebook profilom  "Tesz felhasználó"-nak <span class="footer-menu" data-target="faq-text">(info)</span> </span>,
    és elfogadom az soslive.info 
    <span class="footer-menu" style="text-align: center;font-weight: bold;color: #337ab7;cursor: pointer;" data-target="faq-text"> 
    Adatvédelmi információiban
    </span> leírt eljárásait, és elfogadom a Facebook <a style="font-weight: bold;color: #337ab7;" target="_blank" href="https://www.facebook.com/policies/cookies/"> Cookie használati feltételeit </a>.';

const START_FACEBOOK = 'Facebook live indítása';

const BY_TAPPING = 'A gomb megnyomása után az (5 pecen belül induló) Facebook live <br> videó adatai le lesznek mentve az soslive.info-ra';

const CREATED_LOCAL = 'Live elindítva (helyi idő)';
const NAME = 'Név';

//Contact
const YOUR_NAME= 'Név';
const YOUR_EMAIL= 'Email';
const YOUR_CONTACT =  'Elérhetőség';
const SUBJECT= 'Tárgy';
const REPORT_VIDEO= 'Videó jelentése';
const TECHNICAL_ISSUE= 'Hiba bejelentése';
const ADVICE= 'Észrevétel';
const OTHER= 'Egyéb';
const AUTHORIES = 'Hatósági személy vagyok';
const APPLY_FOR_TEST = 'Teszt felhasználónak jelentkezem';

const ERROR_REPORT = 'Hiba bejelentése';

const MESSAGE= 'Üzenet';
const MESSAGE_SENT= 'Levél elküldve!';
const MESSAGE_NOT_SENT= 'Hiba, levél nem lett elküldve!';
const SEND_MESSAGE = 'Küldés';
const PRIVACY = 'Adatvédelem';






