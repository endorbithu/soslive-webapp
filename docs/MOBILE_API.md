# SOSlive mobil API

A mobil appok (Android, iOS) ezen keresztül lépnek be és kezelik a user configot. **A config (értesítendő emailek,
telefonszámok, hozzáférők) csak itt módosítható** – a web és az admin csak megjeleníti –, így a mobil appnak nem kell
pollingolnia a változásokért: amit ő mentett, az az érvényes. (A `max_events`-et az admin állítja; a mobil
`POST /api/session`-kor kapja meg.)

Alap URL: `https://<webapp>/api`. Minden válasz JSON.

## Hitelesítés

Minden kérésben: `Authorization: Bearer <Google ID token>`.

- A mobil app a Google Sign-In SDK-val lép be a `https://www.googleapis.com/auth/drive.file` scope-pal, és az
  ID tokent küldi (az SDK csendben frissíti, kb. 1 óráig érvényes).
- Az ID token `aud` mezője a mobil app saját OAuth client ID-ja legyen; ezeket a backend `.env`-jében a
  `GOOGLE_MOBILE_CLIENT_IDS` sorolja fel (vesszővel, Android + iOS).
- Ellenőrzés: aláírás a Google kulcsaival, `iss`, `aud`, lejárat, `email_verified`.
- Nincs saját API token vagy session – minden kérés önállóan hitelesített.

Hibák: `401 {"error":"missing_token"}`, `401 {"error":"invalid_token"}`.

### serverAuthCode

A web eseménylistájához a backendnek a user refresh tokenje kell. Ezt a mobil app egy **serverAuthCode**-dal adja át:
- Android: `GoogleSignInOptions.Builder().requestServerAuthCode(WEB_CLIENT_ID)` (vagy Credential Manager / Identity
  `AuthorizationRequest` ugyanígy a **web** client ID-val),
- iOS: `GIDSignIn` `serverClientID = WEB_CLIENT_ID`, a kód: `serverAuthCode`.

A kódot a backend a web client ID + secret-tel cseréli (`redirect_uri` alapból üres; szükség esetén
`GOOGLE_SERVER_AUTH_CODE_REDIRECT`). Csak akkor kell küldeni, ha a backend kéri (`409 server_auth_code_required`),
és ilyenkor a mobil a Google-lel **consenttel** (force code for refresh token) kérjen új kódot.

## Végpontok

### `POST /api/session`
App indításkor (és belépéskor). Létrehozza / frissíti a usert, eltárolja a refresh tokent, és biztosítja a
SOSlive Drive mappát.

Body (opcionális): `{"server_auth_code": "4/0Ab…"}`

Válasz `200`: a config (lásd `GET /api/config`). A **`drive_folder_id`-t a mobil innen használja** az esemény
fájlok létrehozásához – nem keres és nem hoz létre saját mappát.

Hibák:
| Státusz | `error` | Teendő |
|---|---|---|
| 409 | `server_auth_code_required` | új user, vagy visszavont hozzáférés: kérj serverAuthCode-ot (consenttel) és küldd újra |
| 422 | `invalid_server_auth_code` | a kód lejárt / már felhasznált – kérj újat |
| 422 | `drive_scope_missing` | a user nem engedélyezte a Drive hozzáférést |

### `GET /api/config`

```json
{
  "email": "jane@example.com",
  "name": "Jane Doe",
  "drive_folder_id": "1AbC…",
  "max_events": 100,
  "notification_emails": ["mom@example.com"],
  "notification_phones": ["+36 30 123 4567"],
  "allowed_emails": ["friend@example.com", "mom@example.com"]
}
```

Hiba: `409 {"error":"session_required"}` – előbb `POST /api/session` kell.

### `PUT /api/config`

Body (a három mező bármelyike; tömb vagy vesszővel elválasztott szöveg is lehet):

```json
{
  "notification_emails": ["mom@example.com", "dad@example.com"],
  "notification_phones": ["+36 30 123 4567"],
  "allowed_emails": ["friend@example.com"]
}
```

Szabályok:
- email címek: érvényes formátum, egyenként max 128 karakter; telefonszám: `+`, számjegyek, szóköz, `()-`, 6–20 karakter;
- az értesítendő emailek és telefonszámok összefűzve (`, `) max 255 karakter;
- **az értesítendő email címek automatikusan hozzáférést kapnak** (bekerülnek az `allowed_emails`-be);
- a user saját email címe nem kerül az `allowed_emails`-be.

Válasz `200`: a friss config. Validációs hiba: `422` a Laravel szokásos formátumában
(`{"message": "...", "errors": {"notification_emails.0": ["Érvénytelen email cím: …"]}}`).

## Korlátok

- 60 kérés / perc (IP / user szerint).
- Az esemény fájlokat a mobil app közvetlenül a Google Drive API-val írja, nem ezen az API-n (lásd
  [EVENT_FORMAT.md](EVENT_FORMAT.md)).
