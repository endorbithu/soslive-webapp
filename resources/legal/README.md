# Jogi dokumentumok (adatvédelem, felhasználási feltételek)

A forrás az itteni HTML (nyomtatási stílus: `legal.css`). A weboldalon a `public/legal/` alatti PDF-ek érhetők el
statikusan (a webszerver közvetlenül szolgálja ki őket):

| Forrás | PDF | URL |
|---|---|---|
| `privacy-hu.html` | `public/legal/adatvedelem.pdf` | https://soslive.endorbit.hu/legal/adatvedelem.pdf |
| `terms-hu.html` | `public/legal/felhasznalasi-feltetelek.pdf` | https://soslive.endorbit.hu/legal/felhasznalasi-feltetelek.pdf |
| `privacy-en.html` | `public/legal/privacy-policy.pdf` | https://soslive.endorbit.hu/legal/privacy-policy.pdf (Google OAuth consent screen: *Privacy policy link*) |
| `terms-en.html` | `public/legal/terms-of-service.pdf` | https://soslive.endorbit.hu/legal/terms-of-service.pdf (Google OAuth consent screen: *Terms of service link*) |

Módosítás után a PDF-ek újragenerálása (bármely Chrome / Chromium, a repó gyökeréből):

```bash
for pair in privacy-hu:adatvedelem terms-hu:felhasznalasi-feltetelek privacy-en:privacy-policy terms-en:terms-of-service; do
  chromium --headless --no-pdf-header-footer --print-to-pdf="public/legal/${pair#*:}.pdf" "file://$PWD/resources/legal/${pair%%:*}.html"
done
```

A hatálybalépés dátumát mindkét nyelven, a fejlécben és a szövegben is frissíteni kell.
