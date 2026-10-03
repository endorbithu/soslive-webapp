// DOM és formázó segédek. Felhasználói / Drive-ból jövő adat csak textContent-ként vagy ellenőrzött URL-ként kerül ki.

export function el(tag, attrs, children) {
    const node = document.createElement(tag);
    Object.entries(attrs || {}).forEach(([k, v]) => {
        if (k === 'text') node.textContent = v;
        else node.setAttribute(k, v);
    });
    (children || []).forEach((c) => c && node.append(c));
    return node;
}

export function safeUrl(value) {
    try {
        const u = new URL(String(value).trim());
        return (u.protocol === 'https:' || u.protocol === 'http:') ? u.href : null;
    } catch (e) {
        return null;
    }
}

export function formatTime(value) {
    if (!value) return '';
    const d = new Date(value);
    return isNaN(d) ? String(value) : d.toLocaleString('hu-HU');
}

export function coord(lat, lng) {
    lat = Number(lat);
    lng = Number(lng);
    return (isFinite(lat) && isFinite(lng) && Math.abs(lat) <= 90 && Math.abs(lng) <= 180) ? { lat, lng } : null;
}

export function displayName(fileName) {
    return String(fileName || '').replace(/\.json$/i, '');
}

export function mapLink(c) {
    return el('a', {
        href: 'https://www.openstreetmap.org/?mlat=' + c.lat + '&mlon=' + c.lng + '#map=16/' + c.lat + '/' + c.lng,
        target: '_blank',
        rel: 'noopener',
        text: c.lat + ', ' + c.lng,
    });
}

const scripts = {};

/** Külső (nem modul) script betöltése egyszer, pl. hls.js, Google Identity Services. */
export function loadScript(src) {
    scripts[src] = scripts[src] || new Promise((resolve, reject) => {
        const s = el('script', { src });
        s.onload = resolve;
        s.onerror = () => {
            delete scripts[src];
            reject(new Error('Nem tölthető be: ' + src));
        };
        document.head.append(s);
    });
    return scripts[src];
}
