# SOSlive webapp

A SOSlive mobil appok webes felülete. Minimál Laravel backend: minden adat (események, user config) a user saját
Google Drive-jában, JSON fájlokban van, és **csak a mobil app írja**. A web csak olvas, a mobil app pedig a backendet
egyáltalán nem hívja – a user a mobil használata után bármikor beléphet a weben. Formátum:
[docs/EVENT_FORMAT.md](docs/EVENT_FORMAT.md).

## Mit tárol a backend

- `users`: email, Google ID, név, utolsó belépés (Google SSO). **Google tokent nem tárol, Google API-t nem hív.**
- `admins`: admin felhasználók (Laravel `database` auth provider, külön `admin` guard).
- Esemény-adatot és configot nem tárol és nem cache-el: ha a user törli a fájlt / mappát a Drive-jából, az oldalon sem látszik.

## Működés

- **Saját és velem megosztott események.**
  - A weben mindenki a saját eseményeit látja (a Drive `SOSlive/events/` mappájából).
  - Látja azokét is, akik a mobil appban **megosztották vele** az `events` mappájukat. Ehhez egyszer ki kell választania
    a mappát a Google Pickerben, a dashboardon („Velem megosztott események”).
  - Az érintettek (értesítendők) emailt vagy SMS-t kapnak a mobil apptól az esemény linkjével. A link (`/e/{fileId}`)
    belépés nélkül, bárkinek megnyílik.
- A **saját eseménylistát és a configot a böngésző olvassa** a Drive-ból, a user saját Google tokenjével
  (Google Identity Services, `drive.file`). A belépéskor megadott Drive engedély miatt ez többnyire magától megy; ha a
  böngésző letiltja a Google ablakot, egy „Google Drive hozzáférés engedélyezése” gomb jelenik meg. A token a fül
  `sessionStorage`-ában a lejáratáig (max. 1 óra) megmarad, kilépéskor törlődik.
- **Beállítások**: csak olvasható, módosítani csak a mobil appban lehet. Tartalma:
  - a `config.json` (értesítendő emailek, telefonszámok, `max_events`);
  - a „Kik látják az eseményeidet” lista (az `events` mappa megosztásai).
- **Chat**: a webről nem lehet írni; az eseményoldal szerint SMS-ben lehet válaszolni arra a számra, ahonnan az
  értesítés jött (a válasz a tulaj telefonján natív SMS-ként jelenik meg).
- `max_events`: a mobil app a legrégebbi eseményeket a Drive kukájába teszi (30 napig visszaállíthatók).

Az oldalak két zónára oszlanak (részletek: [docs/CACHING.md](docs/CACHING.md)):

| Útvonal | Zóna | Ki | Mi |
|---|---|---|---|
| `/` | statikus | bárki | kezdőlap, Google belépés |
| `/dashboard` | statikus | belépett user | saját események (a böngésző olvassa a Drive-ból), max. 100 / `max_events`; velem megosztott események |
| `/settings` | statikus | belépett user | a `config.json` csak olvashatóan („csak a mobil appban módosítható”), Drive mappa link |
| `/e/{fileId}` | statikus | **bárki**, aki ismeri a linket | Drive API + API key, csak olvasás (keresők nem indexelik); vendégnél a backendet sem hívja |
| `/app/me` | dinamikus | bárki | JSON: belépett user (vendégnek `null`) és CSRF token |
| `/app/auth/google`, `/app/logout` | dinamikus | | belépés, kilépés |
| `/app/auth/dev`, `/app/dev/drive/files/{id}` | dinamikus | csak nem production | teszt belépés, demó események |
| `/app/admin` | dinamikus | admin | userek listája, törlés |

A statikus oldalak cookie és session nélkül, mindenkinek ugyanazzal a HTML-lel mennek, reverse proxyban (Varnish,
Cloudflare) cache-elhetők; a dinamikus zóna (`/app`) session-nel, sosem cache-elődik.

## Térkép (eseményoldal)

Az eseményoldalon egy kis térkép mutatja az útvonalat: egy vonal köti össze a pozíciókat, és külön jelölő mutatja a
kezdőpontot és az utolsó pozíciót.
- A térkép élő eseménynél követi a mozgást. Ha a néző kézzel elmozdítja, a térkép nem ugrik vissza; a „Követés” gomb
  állítja vissza.
- A térkép **Leaflet 1.9.4**. A webapp saját maga szolgálja ki a `public/vendor/leaflet/1.9.4/` alól (BSD-2 licenc, külső
  CDN nincs), és csak az eseményoldal tölti be, akkor is csak ha van pozíció.
- A csempék alapból az **openstreetmap.org**-ról jönnek. Ez csak mérsékelt forgalomra való
  ([tile usage policy](https://operations.osmfoundation.org/policies/tiles/)). Nagyobb forgalomnál szolgáltatói vagy saját
  csempe szerver kell (pl. MapTiler, Stadia, Thunderforest). Beállítás a `.env`-ben: `SOSLIVE_MAP_TILE_URL` (URL sablon,
  `{z}/{x}/{y}`) és `SOSLIVE_MAP_ATTRIBUTION` (a kötelező forrásmegjelölés).

## Google Cloud beállítás

1. Egy Google Cloud projekt a web **és** a mobil appok OAuth kliensei számára (a `drive.file` hozzáférés projekt-szintű:
   a web csak így látja a mobil által létrehozott fájlokat).
2. APIs: **Google Drive API** és **Google Picker API** engedélyezése.
3. OAuth consent screen: scope-ok `openid`, `email`, `profile`, `https://www.googleapis.com/auth/drive.file`
   (a `drive.file` nem „restricted” scope, nem kell hozzá CASA audit). Élesben „In production” állapot.
4. OAuth client (Web application):
   - *Authorized redirect URIs*: `https://<domain>/app/auth/google/callback`,
   - *Authorized JavaScript origins*: `https://<domain>` (a böngészőben kért Drive tokenhez).
   A mobil appok saját (Android / iOS) OAuth klienst használnak ugyanebben a projektben.
5. API key a böngészőnek (kötelező, ezzel olvassa a nyilvános eseményeket): korlátozás *HTTP referrer* = a webapp
   domainje, *API restrictions* = Drive API és Google Picker API.
6. `.env`: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`, `GOOGLE_API_KEY`, és `GOOGLE_APP_ID`. Ez
   utóbbi a projekt száma (*Project number*, a Cloud Console kezdőlapján). A Picker kell hozzá; enélkül a „Megosztott
   mappa hozzáadása” gomb nem jelenik meg.
   A megosztás működését élesítés előtt két valódi fiókkal ellenőrizni kell, lásd
   [docs/EVENT_FORMAT.md](docs/EVENT_FORMAT.md#ellenőrzés-valódi-fiókokkal-megosztás).

## Telepítés

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env && php artisan key:generate   # DB_* beállítása (élesben MySQL)
php artisan migrate --force
php artisan admin:create admin@example.com --name="Admin"
```

A webszerver document rootja a `public/` mappa. Nincs frontend build lépés:
- CSS: `public/css/app.css` (színek tokenekben, automatikus sötét mód);
- JS: natív ES modulok a `public/js/` alatt:
  - `app.js`: belépési pont;
  - `lib/`: config, DOM, HTTP, Drive, közös UI;
  - `pages/`: oldalanként egy-egy modul.

A modulok egymást `soslive/…` névvel importálják. Az import mapet a `resources/views/partials/config.blade.php`
generálja, és minden fájlhoz `?v=filemtime` verziót tesz, így deploy után a cache sem ad vissza régi modult.
A `SESSION_PATH=/app` beállítás kötelező (ettől cache-elhetők a statikus oldalak). Reverse proxy (Varnish,
Cloudflare) beállítása: [docs/CACHING.md](docs/CACHING.md).

Fejlesztés: `php artisan serve`, tesztek: `php artisan test`, kódstílus: `./vendor/bin/pint`.

### Fejlesztői környezet Dockerrel (Laravel Sail)

Csak Docker kell hozzá, helyi PHP nem:

```bash
./bin/sail-setup
```

A parancs:
- létrehozza a `.env`-et MySQL-lel;
- Dockerben lefuttatja a `composer install`-t;
- elindítja az alkalmazást és a MySQL 8.4-et (`compose.yaml`);
- kulcsot generál és migrál.

Többször is futtatható. Az app a `http://localhost` címen fut; másik port: `APP_PORT=8080 ./bin/sail-setup`.
Utána:

```bash
./vendor/bin/sail up -d          # indítás / ./vendor/bin/sail stop
./vendor/bin/sail artisan admin:create te@example.com
./vendor/bin/sail test
```

**Teszt belépés Google nélkül:** ha az `APP_ENV` nem `production`, a `/app/auth/dev` oldalon (link a kezdőlapon és a
menüben) bármilyen email címmel be lehet lépni. A user létrejön, ha még nincs. Production alatt az oldal 404-et ad.
A Drive-os részekhez (eseménylista, beállítások) a böngésző ettől még Google hozzáférést kér.

**Demó események (végoldal teszt Google nélkül):** ha az `APP_ENV` nem `production`, a kezdőlapon és a dashboardon
megjelenik egy „Demó események” lista. Ezek a `/e/demo-…` linkek a valódi Drive helyett a backend ál-Drive végpontjából
(`/app/dev/drive/files/{id}`, adatok: `app/Support/DemoEvents.php`) olvasnak, ugyanazzal a formátummal:

| Link | Mit tesztel |
|---|---|
| `/e/demo-live-event-0000001` | élő esemény: stream, üzenetek, kép; 30 mp-enként új pozíció (a polling frissít) |
| `/e/demo-ended-event-000001` | lezárt esemény, benne hibás bejegyzések (`javascript:` kép, ismeretlen típus, HTML a szövegben) |
| `/e/demo-empty-event-000001` | most indult esemény: nincs stream, nincs bejegyzés |
| `/e/demo-deleted-event-0001` | törölt esemény („nem érhető el”) |

Production alatt a végpont 404-et ad, és a `demo-` kezdetű linkek is a Google Drive API-hoz fordulnak.

Google belépéshez a `.env`-ben meg kell adni a `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` és `GOOGLE_API_KEY` értékét. A Google
Cloud Console-ban a redirect URI `http://localhost/app/auth/google/callback`, a JavaScript origin `http://localhost`.

## ⚠️ Teendő: kompromittált jelszavak

A korábbi (törölt) alkalmazás `inc/config/config.php` fájlja éles adatbázis- és FTP-jelszavakat, valamint API app
tokent tartalmazott, és ezek a git historyban továbbra is benne vannak. **Ezeket le kell cserélni** (DB user jelszó, FTP jelszó, app token, salt),
függetlenül attól, hogy a régi kód fut-e még.
