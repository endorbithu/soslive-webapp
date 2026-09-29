# SOSlive Drive / Sheets adatszerződés

A web és a mobil appok ugyanezt a formátumot olvassák/írják. **Minden kliens ugyanabban a Google Cloud
projektben** legyen (web OAuth client + Android + iOS client), és csak a
`https://www.googleapis.com/auth/drive.file` scope-ot kérje: így az app csak a saját maga által
létrehozott fájlokat látja, de a web látja a mobil által létrehozottakat is (a drive.file hozzáférés
projekt-szintű).

## User mappa

| Tulajdonság     | Érték                                    |
|-----------------|------------------------------------------|
| név             | `SOSlive`                                |
| mimeType        | `application/vnd.google-apps.folder`     |
| appProperties   | `{"soslive": "root"}`                    |

Megkeresés (a mobil appnak is így kell):

```
GET https://www.googleapis.com/drive/v3/files
  ?q=appProperties has { key='soslive' and value='root' } and mimeType='application/vnd.google-apps.folder' and trashed=false
  &orderBy=createdTime&pageSize=1&fields=files(id)
```

Ha nincs találat, létre kell hozni. A web első belépéskor megkeresi / létrehozza, és az ID-ját eltárolja
(`users.drive_folder_id`).

## Esemény = egy spreadsheet a mappában

Létrehozás (mobil app, esemény indításakor):

1. `POST https://sheets.googleapis.com/v4/spreadsheets`
   - `properties.title`: `YYYY-MM-DD HH:mm:ss` (UTC), pl. `2026-09-29 14:03:22`
   - egy munkalap kis griddel: `sheets[0].properties.gridProperties = {rowCount: 200, columnCount: 6}`
     (a Google Sheets fájlonként max. 10M cellát enged, és a kis grid gyorsabban olvasható; az `append` szükség szerint bővít)
2. `PATCH https://www.googleapis.com/drive/v3/files/{id}?addParents={folderId}&removeParents=root`
   body: `{"appProperties": {"soslive": "event"}}`
3. `POST https://www.googleapis.com/drive/v3/files/{id}/permissions` body: `{"type": "anyone", "role": "reader"}`
   – ettől nyitható meg a publikus végoldal backend nélkül (API key-jel). Egy link csak ezt az egy eseményt adja ki.
   (Google Workspace domainek tilthatják az „anyone with link” megosztást.)
4. `A1` = stream URL, `B1:F1` = fejléc (lásd lent).
5. **Rotáció**: ha a mappában a `soslive=event` jelölésű fájlok száma > `max_events`, a legrégebbieket
   `PATCH files/{id}` `{"trashed": true}`-val kukába kell tenni. (A web is megteszi, amikor a tulaj megnyitja a listát.)
6. A megosztható link: `https://<webapp>/e/{spreadsheetId}`

## Munkalap (az első munkalap)

|       | A          | B                  | C                     | D        | E        | F                                |
|-------|------------|--------------------|-----------------------|----------|----------|----------------------------------|
| **1** | stream URL | `Idő`              | `Koordináta`          | `Feladó` | `Üzenet` | `Képek`                          |
| 2..   | (üres)     | ISO 8601 időpont   | `lat,lng` (tizedes)   | név      | szöveg   | kép URL(ek) szóközzel elválasztva |

- Egy sor lehet pozíció (B, C), chat üzenet (B, D, E) vagy kép (B, F) – vagy ezek kombinációja.
- Új sort **mindig** `values.append`-del kell írni, hogy párhuzamos írók ne írják felül egymást:
  `POST .../values/A:F:append?valueInputOption=RAW&insertDataOption=INSERT_ROWS`
  (`RAW`: a szöveg sosem értelmeződik képletként).
- A1 változhat (pl. új stream URL), a web figyeli.
- A kép URL-ek legyenek publikusan elérhetők (a publikus végoldal `<img>`-ként jeleníti meg őket).

## Olvasás a webről

- Publikus (vendég): `GET .../v4/spreadsheets/{id}/values/A1:F?key={API_KEY}`
- Bejelentkezett, jogosult user: ugyanez a tulaj access tokenjével (a backend `/token/{owner}` adja).
- Frissítés: 5 mp-enként `GET drive/v3/files/{id}?fields=modifiedTime` (Drive kvóta bőséges), és csak
  változáskor jön a Sheets olvasás (a Sheets API kvótája szűkös: ~60 olvasás/perc/user, ~300/perc/projekt).
