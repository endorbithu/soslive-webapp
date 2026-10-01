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

- **Mindenki csak a saját eseményeit látja** a weben. Az érintettek (értesítendők) emailt / SMS-t kapnak a mobil apptól
  az esemény linkjével; a link (`/e/{fileId}`) belépés nélkül, bárkinek megnyílik.
- A **saját eseménylistát és a configot a böngésző olvassa** a Drive-ból, a user saját Google tokenjével
  (Google Identity Services, `drive.file`). A belépéskor megadott Drive engedély miatt ez többnyire magától megy; ha a
  böngésző letiltja a Google ablakot, egy „Google Drive hozzáférés engedélyezése” gomb jelenik meg. A token a fül
  `sessionStorage`-ában a lejáratáig (max. 1 óra) megmarad, kilépéskor törlődik.
- **Beállítások**: a `config.json` (értesítendő emailek, telefonszámok, `max_events`) csak olvasható; módosítani csak a
  mobil appban lehet.
- **Chat**: a webről nem lehet írni; az eseményoldal szerint SMS-ben lehet válaszolni arra a számra, ahonnan az
  értesítés jött (a válasz a tulaj telefonján natív SMS-ként jelenik meg).
- `max_events`: a mobil app a legrégebbi eseményeket a Drive kukájába teszi (30 napig visszaállíthatók).

Az oldalak két zónára oszlanak (részletek: [docs/CACHING.md](docs/CACHING.md)):

| Útvonal | Zóna | Ki | Mi |
|---|---|---|---|
| `/` | statikus | bárki | kezdőlap, Google belépés |
| `/dashboard` | statikus | belépett user | saját események (a böngésző olvassa a Drive-ból), max. 100 / `max_events` |
| `/settings` | statikus | belépett user | a `config.json` csak olvashatóan („csak a mobil appban módosítható”), Drive mappa link |
| `/e/{fileId}` | statikus | **bárki**, aki ismeri a linket | Drive API + API key, csak olvasás (keresők nem indexelik); vendégnél a backendet sem hívja |
| `/app/me` | dinamikus | bárki | JSON: belépett user (vendégnek `null`) és CSRF token |
| `/app/auth/google`, `/app/logout` | dinamikus | | belépés, kilépés |
| `/app/admin` | dinamikus | admin | userek listája, törlés |

A statikus oldalak cookie és session nélkül, mindenkinek ugyanazzal a HTML-lel mennek, reverse proxyban (Varnish,
Cloudflare) cache-elhetők; a dinamikus zóna (`/app`) session-nel, sosem cache-elődik.

## Google Cloud beállítás

1. Egy Google Cloud projekt a web **és** a mobil appok OAuth kliensei számára (a `drive.file` hozzáférés projekt-szintű:
   a web csak így látja a mobil által létrehozott fájlokat).
2. APIs: **Google Drive API** engedélyezése (más nem kell).
3. OAuth consent screen: scope-ok `openid`, `email`, `profile`, `https://www.googleapis.com/auth/drive.file`
   (a `drive.file` nem „restricted” scope, nem kell hozzá CASA audit). Élesben „In production” állapot.
4. OAuth client (Web application):
   - *Authorized redirect URIs*: `https://<domain>/app/auth/google/callback`,
   - *Authorized JavaScript origins*: `https://<domain>` (a böngészőben kért Drive tokenhez).
   A mobil appok saját (Android / iOS) OAuth klienst használnak ugyanebben a projektben.
5. API key a böngészőnek (kötelező, ezzel olvassa a nyilvános eseményeket): korlátozás *HTTP referrer* = a webapp
   domainje, *API restrictions* = Drive API.
6. `.env`: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`, `GOOGLE_API_KEY`.

## Telepítés

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env && php artisan key:generate   # DB_* beállítása (élesben MySQL)
php artisan migrate --force
php artisan admin:create admin@example.com --name="Admin"
```

A webszerver document rootja a `public/` mappa. Nincs frontend build lépés (vanilla JS: `public/js/soslive.js`).
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

Google belépéshez a `.env`-ben meg kell adni a `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` és `GOOGLE_API_KEY` értékét. A Google
Cloud Console-ban a redirect URI `http://localhost/app/auth/google/callback`, a JavaScript origin `http://localhost`.

## ⚠️ Teendő: kompromittált jelszavak

A korábbi (törölt) alkalmazás `inc/config/config.php` fájlja éles adatbázis- és FTP-jelszavakat, valamint API app
tokent tartalmazott, és ezek a git historyban továbbra is benne vannak. **Ezeket le kell cserélni** (DB user jelszó, FTP jelszó, app token, salt),
függetlenül attól, hogy a régi kód fut-e még.
