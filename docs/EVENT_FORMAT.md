# SOSlive Drive / esemény JSON adatszerződés

Az esemény-fájlokat és a `config.json`-t **csak a mobil app írja**, a web csak olvassa őket. A mobil app a
SOSlive backendet **nem hívja** – mindent a saját Google tokenjével, közvetlenül a Drive API-val végez. **Minden kliens ugyanabban a Google
Cloud projektben** legyen (web OAuth client + Android + iOS client), és csak a
`https://www.googleapis.com/auth/drive.file` scope-ot kérje: így az app csak a saját maga által
létrehozott fájlokat látja, de a web látja a mobil által létrehozottakat is (a drive.file hozzáférés
projekt-szintű). Csak a **Google Drive API** kell (a Sheets API nem).

## Mappaszerkezet

```
SOSlive/                         soslive=root     privát
  config.json                    soslive=config   privát
  events/                        soslive=events   megosztható userekkel (lásd „Megosztás”)
    2026-10-03 14:03:22.json     soslive=event    + „anyone with link”
    img 2026-10-03 14:05:10.jpg  soslive=image    + „anyone with link”
```

A jelölések `appProperties` értékek: `{"soslive": "<tag>"}`. Minden mappa `mimeType` értéke
`application/vnd.google-apps.folder`.

### SOSlive mappa (`soslive=root`)

Megkeresés (a mobil app és a web is így keresi):

```
GET https://www.googleapis.com/drive/v3/files
  ?q=appProperties has { key='soslive' and value='root' } and mimeType='application/vnd.google-apps.folder' and 'me' in owners and trashed=false
  &orderBy=createdTime&pageSize=1&fields=files(id)
```

- Ha nincs találat, létre kell hozni (`POST /drive/v3/files`, név `SOSlive`, a fenti tulajdonságokkal).
- **Több találatnál a legrégebbi az érvényes** (`orderBy=createdTime`). Így ha a mobil és a web egyszerre hozna létre
  mappát, mindkettő ugyanazt használja.
- A mappa **nem publikus**, és nem is osztható meg.

### `events` mappa (`soslive=events`)

A SOSlive mappán belül van, név: `events`. Ebben vannak az események és a képek. Megkeresés:

```
q='<SOSlive id>' in parents and appProperties has { key='soslive' and value='events' } and mimeType='application/vnd.google-apps.folder' and trashed=false
orderBy=createdTime&pageSize=1
```

- Több találatnál itt is a legrégebbi az érvényes. Ha nincs, a mobil app hozza létre.
- Ha a web hozza létre a SOSlive mappát (a dashboard „SOSlive mappa létrehozása” gombja), akkor az `events` mappát is
  létrehozza.
- **Átköltözés:** a korábban közvetlenül a `SOSlive/` mappába írt esemény- és képfájlokat a mobil app az első
  indításkor átmozgatja az `events/` mappába (`PATCH files/{id}?addParents=…&removeParents=…`). A fájl ID nem változik,
  ezért a már kiküldött `/e/{fileId}` linkek továbbra is működnek.

## User config = `config.json` a SOSlive mappában

| Tulajdonság     | Érték                                    |
|-----------------|------------------------------------------|
| név             | `config.json`                            |
| mimeType        | `application/json`                       |
| appProperties   | `{"soslive": "config"}`                  |

```json
{
  "v": 1,
  "notification_emails": ["mom@example.com", "dad@example.com"],
  "notification_phones": ["+36 30 123 4567"],
  "max_events": 100
}
```

- **Csak a mobil app írja** (a user a mobil appban állítja be); a web csak olvasható módon mutatja („csak a mobil
  appban módosítható”).
- Nem publikus: ne adj rá „anyone” megosztást. A SOSlive mappában marad, nem az `events` mappában, ezért az
  `events` mappa megosztottjai sem látják.
- Létrehozás: `POST https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart` (metadata: név, mimeType,
  `parents: [folderId]`, `appProperties`), módosítás: teljes felülírás `PATCH .../upload/drive/v3/files/{id}?uploadType=media`.
- `max_events` hiányában 100. A `notification_*` listák a mobil app értesítéseihez (email / SMS) valók.

## Esemény = egy JSON fájl az `events` mappában

Létrehozás (mobil app, esemény indításakor):

1. `POST https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id`
   - metadata: `{"name": "YYYY-MM-DD HH:mm:ss.json", "mimeType": "application/json", "parents": ["{eventsFolderId}"], "appProperties": {"soslive": "event"}}`
     – a név az esemény kezdete UTC-ben, pl. `2026-09-29 14:03:22.json` (a web ezt mutatja címként, `.json` nélkül)
   - tartalom: a kezdeti JSON (lásd lent)
2. `POST https://www.googleapis.com/drive/v3/files/{id}/permissions` body: `{"type": "anyone", "role": "reader"}`
   – ettől nyitható meg a publikus végoldal backend nélkül (API key-jel). Egy link csak ezt az egy eseményt adja ki.
   (Google Workspace domainek tilthatják az „anyone with link” megosztást.)
3. **Rotáció**: ha az `events` mappában a `soslive=event` jelölésű fájlok száma > `max_events` (a `config.json`-ból), a
   legrégebbieket `PATCH files/{id}` `{"trashed": true}`-val kukába kell tenni.
4. A megosztható link: `https://<webapp>/e/{fileId}`

Frissítés: a **teljes fájl felülírása** – `PATCH https://www.googleapis.com/upload/drive/v3/files/{id}?uploadType=media`.
Mivel a fájlt egyetlen író (a tulaj telefonja) írja, nincs ütközés; a mobil app a memóriában tartja az aktuális
állapotot, hozzáfűzi az új bejegyzést, és feltölti az egészet.

- **Pozíció: 30 másodpercenként** (nem gyakrabban) – ez tartja kicsiben a fájlt, a mobil adatforgalmat és a
  nézők letöltéseit. Több, közel egyszerre keletkező bejegyzést (pozíció + SMS + kép) érdemes egy feltöltésbe összevonni.
- Egy óra ≈ 120 pozíció ≈ 10–15 KB-os fájl.

## JSON szerkezet

```json
{
  "v": 1,
  "stream": "https://stream.example.com/live/abc.m3u8",
  "stream_page": "https://www.youtube.com/watch?v=abc",
  "recording": "https://stream.example.com/vod/abc.mp4",
  "entries": [
    {"t": "2026-09-29T14:03:22Z", "type": "pos", "lat": 47.4979, "lng": 19.0402},
    {"t": "2026-09-29T14:03:40Z", "type": "msg", "name": "Anya", "text": "Úton vagyunk"},
    {"t": "2026-09-29T14:04:05Z", "type": "img", "url": "https://img.example.com/abc.jpg"}
  ]
}
```

| Mező | Jelentés |
|---|---|
| `v` | formátum verzió, most `1` |
| `stream` | közvetlenül lejátszható URL (HLS `.m3u8` / MP4); változhat, **üres is lehet** |
| `stream_page` | opcionális: a user stream szolgáltatójának nézői oldala (pl. YouTube / Twitch). Nem biztos, hogy beágyazható, ezért a web linkként mutatja |
| `recording` | opcionális: link a felvétel letöltéséhez / visszanézéséhez; a web linkként mutatja |
| `entries[]` | időrendi bejegyzések, a végére kell fűzni |
| `t` | ISO 8601 időpont (UTC) |
| `type: "pos"` | `lat`, `lng` tizedes számként |
| `type: "msg"` | `name` (megjelenített feladó), `text` |
| `type: "img"` | `url` – publikusan elérhető kép (a végoldal `<img>`-ként mutatja). A képfájl az `events` mappában van (`soslive=image`, „anyone with link”) |

Az ismeretlen mezőket és `type`-ú bejegyzéseket a web figyelmen kívül hagyja, így a formátum visszafelé kompatibilisen
bővíthető. A web csak `http(s)` URL-t használ fel (a `stream`, `stream_page`, `recording` és az `img` `url` mezőkben).

## Megosztás: „Kik látják az eseményeidet”

A mobil app Beállítások képernyőjén X (az események tulajdonosa) megadhatja Y Google fiókjának e-mail címét. Az app
ekkor:
- az `events` mappát reader joggal megosztja Y-nal:
  `POST files/{eventsId}/permissions?sendNotificationEmail=true`, body `{"type": "user", "role": "reader", "emailAddress": "<Y>"}`.
  A Google e-mailben értesíti Y-t.
- visszavonáskor törli a jogosultságot: `DELETE files/{eventsId}/permissions/{permissionId}`.

A megosztás következményei:
- Y csak az eseményeket és a képeket látja, a `config.json`-t (X értesítendő telefonszámai, e-mailjei) nem.
- A mappa-megosztás öröklődik, így X később létrejövő eseményei is látszanak Y-nak.
- A megosztottak listája maga a Drive: az `events` mappa permissions listája. A `config.json`-be nem kerül.
- A web Beállítások oldala ezt csak olvasható módon mutatja (`GET files/{eventsId}/permissions`, a `type=user`,
  `role=reader` elemek).

Az egyes eseménylinkek (`/e/{fileId}`) a megosztástól függetlenül „anyone with link” módban működnek.

## Válasz SMS-ben

A webről nem lehet írni. Az eseményoldal ezt írja ki: *„SMS-ben válaszolhatsz arra a számra, ahonnan az
értesítést kaptad.”* A válasz SMS a tulaj telefonján a **natív SMS értesítésben** jelenik meg; a mobil app
**nem olvassa** az SMS-eket, és nem kerülnek az esemény fájlba. Ehhez az értesítő SMS-t a tulaj saját számáról
kell küldeni (a címzettek: `notification_phones` a `config.json`-ból).

A `msg` bejegyzést csak a mobil app írja (pl. a tulaj saját üzenete). **A fájl publikus: telefonszám ne kerüljön bele.**

## Olvasás a webről

A web semmit nem ír. Kivétel: ha a usernek még nincs SOSlive mappája, a dashboardon gombbal létrehozhatja a SOSlive és az
`events` mappát.

- Saját eseménylista és `config.json`: a böngésző olvassa a **user saját Google tokenjével** (Google Identity Services,
  `drive.file`), a backend nem vesz részt benne.
  - Lista:
    ```
    appProperties has { key='soslive' and value='event' } and ('<events id>' in parents or '<SOSlive id>' in parents) and trashed=false
    orderBy=createdTime desc, pageSize = min(100, max_events)
    ```
  - A SOSlive mappát az átmeneti időre kérdezi le a web is: ha egy telefon még a régi appot futtatja, az eseményei még
    ott vannak.
- **Velem megosztott események (Y nézete)**, csak olvasható:
  - A `drive.file` scope önmagában nem látja X mappáját, és a `sharedWithMe` lekérdezésben sem jelenik meg. Y ezért a
    dashboardon egyszer kiválasztja a vele megosztott `events` mappát a **Google Pickerben** („Megosztott mappa
    hozzáadása”). Ehhez a webnek kell az App ID (a Google Cloud projekt száma, `GOOGLE_APP_ID`), és az API key-en
    engedélyezni kell a Google Picker API-t.
  - A kiválasztott mappákat a Drive tartja nyilván, a web nem tárolja őket:
    ```
    appProperties has { key='soslive' and value='events' } and mimeType='application/vnd.google-apps.folder' and trashed=false
    fields=files(id,ownedByMe,owners(displayName,emailAddress))   → a nem saját (ownedByMe=false) mappák
    ```
  - Mappánként: X neve vagy e-mail címe (`owners[0]`), alatta az eseményei
    (`'<X events id>' in parents and appProperties has { key='soslive' and value='event' } and trashed=false`).
  - Visszavonás: ha X visszavonja Y hozzáférését, a mappa kiesik a listából. Ha egy lekérdezés 403-at vagy 404-et ad, a
    web csendben kihagyja, hibát nem mutat.
- Esemény, mindenkinek, API key-jel:
  - 5 mp-enként `GET https://www.googleapis.com/drive/v3/files/{id}?fields=name,modifiedTime,trashed&key={API_KEY}`
  - csak ha a `modifiedTime` változott: `GET https://www.googleapis.com/drive/v3/files/{id}?alt=media&key={API_KEY}`
- Mindkettő a Drive API kvótáját használja (kb. 12 000 kérés/perc/projekt), nem a szűkös Sheets kvótát.

## Ellenőrzés valódi fiókokkal (megosztás)

A Google dokumentációja nem egyértelmű abban, hogy a `drive.file` token a Pickerben kiválasztott **mappa tartalmát** is
látja-e, beleértve a később létrejövő fájlokat. Ezt két valódi Google fiókkal kell kipróbálni, élesítés előtt:

1. X a mobil appban megosztja az eseményeit Y-nal. Y megkapja a Google értesítő e-mailt.
2. Y belép a webre, és a dashboardon a „Megosztott mappa hozzáadása” gombbal kiválasztja X `events` mappáját.
3. Látszanak-e X **régi** eseményei Y „Velem megosztott események” listájában?
4. X új eseményt indít. Megjelenik-e Y listájában (oldalfrissítés után)?
5. X visszavonja a megosztást. Y listájából eltűnik-e X, hibaüzenet nélkül?

Ha a 3. vagy a 4. lépés nem működik (a web ilyenkor ezt írja: „Még nincs esemény (vagy a Google Drive nem adott
hozzáférést a mappa tartalmához)”), akkor a **tartalék megoldás** jön:
- a mobil app az `events` mappában vezet egy `index.json`-t az események listájával (`id`, `name`, `createdTime`);
- Y a Pickerben ezt az egy fájlt választja ki (egy fájl kiválasztása a dokumentáció szerint biztosan hozzáférést ad);
- a web ebből listáz. Maguk az esemény fájlok „anyone with link” módúak, ezért API key-jel olvashatók.

A másik lehetőség a `drive.readonly` scope és a `sharedWithMe` lekérdezés. Ez korlátozott scope: nyilvános apphoz
Google ellenőrzés és CASA biztonsági audit kell hozzá.
