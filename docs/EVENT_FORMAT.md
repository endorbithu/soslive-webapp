# SOSlive Drive / esemény JSON adatszerződés

Az esemény-fájlokat és a `config.json`-t **csak a mobil app írja**, a web csak olvassa őket. A mobil app a
SOSlive backendet **nem hívja** – mindent a saját Google tokenjével, közvetlenül a Drive API-val végez. **Minden kliens ugyanabban a Google
Cloud projektben** legyen (web OAuth client + Android + iOS client), és csak a
`https://www.googleapis.com/auth/drive.file` scope-ot kérje: így az app csak a saját maga által
létrehozott fájlokat látja, de a web látja a mobil által létrehozottakat is (a drive.file hozzáférés
projekt-szintű). Csak a **Google Drive API** kell (a Sheets API nem).

## User mappa

| Tulajdonság     | Érték                                    |
|-----------------|------------------------------------------|
| név             | `SOSlive`                                |
| mimeType        | `application/vnd.google-apps.folder`     |
| appProperties   | `{"soslive": "root"}`                    |

Megkeresés (a mobil app és a web is így keresi):

```
GET https://www.googleapis.com/drive/v3/files
  ?q=appProperties has { key='soslive' and value='root' } and mimeType='application/vnd.google-apps.folder' and trashed=false
  &orderBy=createdTime&pageSize=1&fields=files(id)
```

Ha nincs találat, létre kell hozni (`POST /drive/v3/files` a fenti tulajdonságokkal). **Több találatnál a legrégebbi az
érvényes** (`orderBy=createdTime`) – így ha a mobil és a web egyszerre hozna létre mappát, mindkettő ugyanazt használja.
A mappa **nem publikus**.

## User config = `config.json` a mappában

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
  appban módosítható”). Nem publikus – ne adj rá „anyone” megosztást.
- Létrehozás: `POST https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart` (metadata: név, mimeType,
  `parents: [folderId]`, `appProperties`), módosítás: teljes felülírás `PATCH .../upload/drive/v3/files/{id}?uploadType=media`.
- `max_events` hiányában 100. A `notification_*` listák a mobil app értesítéseihez (email / SMS) valók.

## Esemény = egy JSON fájl a mappában

Létrehozás (mobil app, esemény indításakor):

1. `POST https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id`
   - metadata: `{"name": "YYYY-MM-DD HH:mm:ss.json", "mimeType": "application/json", "parents": ["{folderId}"], "appProperties": {"soslive": "event"}}`
     – a név az esemény kezdete UTC-ben, pl. `2026-09-29 14:03:22.json` (a web ezt mutatja címként, `.json` nélkül)
   - tartalom: a kezdeti JSON (lásd lent)
2. `POST https://www.googleapis.com/drive/v3/files/{id}/permissions` body: `{"type": "anyone", "role": "reader"}`
   – ettől nyitható meg a publikus végoldal backend nélkül (API key-jel). Egy link csak ezt az egy eseményt adja ki.
   (Google Workspace domainek tilthatják az „anyone with link” megosztást.)
3. **Rotáció**: ha a mappában a `soslive=event` jelölésű fájlok száma > `max_events` (a `config.json`-ból), a
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
| `stream` | a stream URL (a 3rd party RTMP szerver által generált, lehetőleg HLS `.m3u8`); változhat |
| `entries[]` | időrendi bejegyzések, a végére kell fűzni |
| `t` | ISO 8601 időpont (UTC) |
| `type: "pos"` | `lat`, `lng` tizedes számként |
| `type: "msg"` | `name` (megjelenített feladó), `text` |
| `type: "img"` | `url` – publikusan elérhető kép (a végoldal `<img>`-ként mutatja) |

Az ismeretlen `type`-ú bejegyzéseket a web kihagyja, így a formátum visszafelé kompatibilisen bővíthető.

## Válasz SMS-ben

A webről nem lehet írni. Az eseményoldal ezt írja ki: *„SMS-ben válaszolhatsz arra a számra, ahonnan az
értesítést kaptad.”* A válasz SMS a tulaj telefonján a **natív SMS értesítésben** jelenik meg; a mobil app
**nem olvassa** az SMS-eket, és nem kerülnek az esemény fájlba. Ehhez az értesítő SMS-t a tulaj saját számáról
kell küldeni (a címzettek: `notification_phones` a `config.json`-ból).

A `msg` bejegyzést csak a mobil app írja (pl. a tulaj saját üzenete). **A fájl publikus: telefonszám ne kerüljön bele.**

## Olvasás a webről (a web semmit nem ír)

- Saját eseménylista és `config.json`: a böngésző olvassa a **user saját Google tokenjével** (Google Identity Services,
  `drive.file`), a backend nem vesz részt benne. Lista: `files.list` a mappára,
  `appProperties has { key='soslive' and value='event' }`, `orderBy=createdTime desc`, `pageSize = min(100, max_events)`.
  Mindenki csak a saját eseményeit látja.
- Esemény, mindenkinek, API key-jel:
  - 5 mp-enként `GET https://www.googleapis.com/drive/v3/files/{id}?fields=name,modifiedTime,trashed&key={API_KEY}`
  - csak ha a `modifiedTime` változott: `GET https://www.googleapis.com/drive/v3/files/{id}?alt=media&key={API_KEY}`
- Mindkettő a Drive API kvótáját használja (kb. 12 000 kérés/perc/projekt), nem a szűkös Sheets kvótát.
