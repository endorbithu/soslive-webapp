# Reverse proxy cache (Varnish, Cloudflare)

A HTML oldalak úgy készülnek, hogy egy reverse proxy (Varnish, Cloudflare, nginx `proxy_cache`) kiszolgálhassa őket
a PHP megszólítása nélkül. A backendnek csak a userfüggő, kis JSON válaszokat kell dinamikusan előállítania.

## Két zóna

| Zóna | Útvonalak | Cookie | `Cache-Control` |
|---|---|---|---|
| **Statikus** (`routes/static.php`) | `/`, `/dashboard`, `/settings`, `/e/{fileId}` | nincs (se kérésben, se válaszban) | `public, max-age=60, s-maxage=3600` + `ETag` (304 támogatással) |
| **Dinamikus** (`routes/web.php`, `/app` prefix) | `/app/me`, `/app/auth/*`, `/app/logout`, `/app/admin/*` | session / XSRF / remember cookie, **`path=/app`** | `no-store, private` |

- A session cookie útvonala `/app` (`SESSION_PATH=/app`), ezért a böngésző a statikus oldalak kérésével **nem küld cookie-t**
  – így a Varnish alap-VCL is cache-eli őket, és a Cloudflare sem kerüli meg a cache-t.
- A statikus HTML mindenkinek ugyanaz: nincs benne user adat, CSRF token vagy session üzenet. A belépett user és a CSRF
  token az `/app/me` JSON-ból jön, az eseménylistát és a configot a böngésző a Drive-ból olvassa; az üzenetek
  (pl. sikertelen belépés) `?msg=kód` query paraméterrel.
- A nyilvános eseményoldal vendégként egyáltalán nem hívja a backendet (a JS csak akkor kéri az `/app/me`-t, ha a böngésző
  korábban belépett).
- A statikus oldalak URL-jei relatívak, az assetek `?v=<mtime>` verzióval hivatkozottak – a cache-elt HTML nem függ a kérés
  hostjától / sémájától, és deploy után új asset URL-t kap.
- Új statikus oldalnál: **soha ne rendereljen userfüggő adatot** (`auth()`, `session()`, `csrf_token()`, `$errors`).
  A `tests/Feature/CachingTest.php` ellenőrzi: nincs `Set-Cookie`, a HTML vendégnek és usernek azonos.

## TTL-ek

`.env`:

```
SOSLIVE_PAGE_MAX_AGE=60      # böngésző
SOSLIVE_PAGE_S_MAXAGE=3600   # reverse proxy
```

Az oldalak csak deploykor változnak, ezért az `s-maxage` lehet hosszú is, ha deploy után purge-ölsz:
- Cloudflare: *Caching → Purge Everything* (vagy API: `POST /zones/{zone}/purge_cache {"purge_everything":true}`),
- Varnish: `varnishadm "ban req.url ~ ."`.

## Varnish

A statikus útvonalak kéréséből érdemes minden cookie-t eldobni (pl. analitika cookie-k miatt), az `/app` útvonalakat
pedig mindig továbbengedni:

```vcl
sub vcl_recv {
    if (req.url ~ "^/app(/|$)") {
        return (pass);
    }
    if (req.method == "GET" || req.method == "HEAD") {
        unset req.http.Cookie;
    }
}
```

A többit (a `Cache-Control` / `s-maxage` tisztelete, `ETag` / 304) a Varnish alapból kezeli.

## Cloudflare

A Cloudflare alapból nem cache-el HTML-t, ehhez egy **Cache Rule** kell:

- *When incoming requests match*:
  `(not starts_with(http.request.uri.path, "/app"))`
- *Then*: **Eligible for cache**, Edge TTL: **Use cache-control header if present**, Browser TTL: **Respect origin**.

Az `/app` válaszok `no-store, private` fejléccel mennek, ezeket a Cloudflare nem cache-eli.

## Proxy mögötti futtatás

Ha a Laravel proxy mögött fut (HTTPS a proxyn végződik), a `trustProxies` beállítás kell ahhoz, hogy a belépés utáni
átirányítások és a Google OAuth callback URL `https://` sémát kapjanak (`bootstrap/app.php`:
`$middleware->trustProxies(at: [...])` a proxy IP-ivel).
