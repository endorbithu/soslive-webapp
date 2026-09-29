# SOSlive webapp

A SOSlive mobil appok webes felülete. Minimál Laravel backend, az esemény-adatok (stream link, pozíció,
chat, képek) **nem a backenden**, hanem a user saját Google Drive-jában, eseményenként egy JSON fájlban
vannak. A web **csak olvas**: az eseményeket a böngésző közvetlenül a Google Drive API-ból olvassa API key-jel,
írni csak a mobil app ír. Formátum: [docs/EVENT_FORMAT.md](docs/EVENT_FORMAT.md).

## Mit tárol a backend

- `users`: email, Google ID, név, **titkosított** Google refresh token, SOSlive Drive mappa ID,
  értesítendő emailek / telefonszámok (vesszővel, max 255), `max_events`, utolsó belépés.
- `user_allowed_emails`: kik láthatják a user eseményeit (a notification emailek automatikusan bekerülnek).
- A user config (értesítendők, hozzáférők) **csak a mobil appból módosítható** a [mobil API](docs/MOBILE_API.md)-n;
  a web Beállítások oldala és az admin csak megjeleníti (az admin a `max_events`-et állíthatja).
- `admins`: admin felhasználók (Laravel `database` auth provider, külön `admin` guard).
- Cache: a Google access token (titkosítva, lejárat előtt 5 percig). Esemény-adatot nem tárolunk és nem cache-elünk:
  ha a user törli a fájlt / mappát a Drive-jából, az oldalon sem látszik.

## Működés

| Oldal | Ki | Honnan jön az adat |
|---|---|---|
| `/` | vendég | Google belépés gomb |
| `/dashboard` | belépett user | saját + a vele megosztott userek eseményei, max. 100 / `max_events` (a listát a backend adja) |
| `/events/{owner}` | belépett user | JSON eseménylista (ID, cím, idő), ha `owner == én` vagy az emailem szerepel a tulaj `user_allowed_emails` listájában; a backend a tulaj tokenjével kéri le a Drive-ból |
| `/e/{fileId}` | **bárki**, aki ismeri a linket | Drive API + API key, csak olvasás (keresők nem indexelik) |
| `/settings` | belépett user | értesítendők, hozzáférők csak olvashatóan („csak a mobil appban módosítható”); Drive mappa ellenőrzés/újralétrehozás |
| `/api/session`, `/api/config` | mobil app (Google ID token) | belépés, config olvasás / írás – [docs/MOBILE_API.md](docs/MOBILE_API.md) |
| `/admin` | admin | userek listája, `max_events` szerkesztése, config megtekintése, törlés |

Jogosultság: egy user a saját eseményeit, és azon userek eseményeit látja a listában, akik felvették az email
címét. A publikus link csak olvasásra ad hozzáférést, és csak ahhoz az egy eseményhez. Google token a böngészőhöz
nem kerül.

Chat: a webről nem lehet írni. Az eseményoldal azt írja ki, hogy SMS-ben lehet válaszolni arra a számra, ahonnan
az értesítés jött; a válasz a tulaj telefonján natív SMS-ként jelenik meg (nem kerül az esemény fájlba).

`max_events`: ha több esemény van, a legrégebbiek a Drive kukájába kerülnek (30 napig visszaállíthatók).
Ezt a mobil app végzi új esemény létrehozásakor.

## Google Cloud beállítás

1. Egy Google Cloud projekt a web **és** a mobil appok OAuth kliensei számára (a `drive.file` hozzáférés projekt-szintű).
2. APIs: **Google Drive API** engedélyezése (más nem kell).
3. OAuth consent screen: scope-ok `openid`, `email`, `profile`, `https://www.googleapis.com/auth/drive.file`
   (a `drive.file` nem „restricted” scope, nem kell hozzá CASA audit).
4. OAuth client (Web application): redirect URI `https://<domain>/auth/google/callback`. Ennek a client ID-nak kell
   a mobil appoknak a serverAuthCode-ot kérnie.
   Android és iOS OAuth client: ezek ID-ja megy a `GOOGLE_MOBILE_CLIENT_IDS`-be (a mobil ID tokenek `aud` mezője).
5. API key a böngészőnek (kötelező, ezzel olvassa az eseményeket): korlátozás *HTTP referrer* = a webapp domainje,
   *API restrictions* = Drive API.
6. `.env`: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`, `GOOGLE_API_KEY`, `GOOGLE_MOBILE_CLIENT_IDS`.

## Telepítés

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env && php artisan key:generate   # DB_* beállítása (élesben MySQL)
php artisan migrate --force
php artisan admin:create admin@example.com --name="Admin"
```

A webszerver document rootja a `public/` mappa. Nincs frontend build lépés (vanilla JS: `public/js/soslive.js`).

Fejlesztés: `php artisan serve`, tesztek: `php artisan test`, kódstílus: `./vendor/bin/pint`.

## ⚠️ Teendő: kompromittált jelszavak

A korábbi (törölt) alkalmazás `inc/config/config.php` fájlja éles adatbázis- és FTP-jelszavakat, valamint API app
tokent tartalmazott, és ezek a git historyban továbbra is benne vannak. **Ezeket le kell cserélni** (DB user jelszó, FTP jelszó, app token, salt),
függetlenül attól, hogy a régi kód fut-e még.
