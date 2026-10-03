// Útvonal-térkép az eseményoldalon (Leaflet + OpenStreetMap csempék). A Leaflet csak akkor töltődik be, ha van pozíció.
import { cfg, LEAFLET } from 'soslive/lib/config.js';
import { loadScript, loadStyle } from 'soslive/lib/dom.js';

/**
 * Térképet hoz létre a containerben. Visszaad egy { update(points) } objektumot: points = [{lat, lng}, ...] időrendben.
 * Amíg a user nem mozgatja / nagyítja a térképet, az mindig az egész útvonalat mutatja; a „Követés” gomb ezt visszaállítja.
 */
export async function createRouteMap(container) {
    await Promise.all([loadStyle(LEAFLET + '.css'), loadScript(LEAFLET + '.js')]);
    const L = window.L;

    const map = L.map(container, { scrollWheelZoom: false, zoomAnimation: false, fadeAnimation: false, markerZoomAnimation: false });
    L.tileLayer(cfg.mapTileUrl, { maxZoom: 19, attribution: cfg.mapAttribution }).addTo(map);
    map.attributionControl.setPrefix(false);

    const accent = getComputedStyle(document.documentElement).getPropertyValue('--accent').trim() || '#d32f2f';
    const route = L.polyline([], { color: accent, weight: 4, opacity: 0.85 }).addTo(map);
    const start = L.circleMarker([0, 0], { radius: 6, color: '#fff', weight: 2, fillColor: '#5f616a', fillOpacity: 1 })
        .bindTooltip('Kezdőpont');
    const last = L.circleMarker([0, 0], { radius: 9, color: '#fff', weight: 3, fillColor: accent, fillOpacity: 1 })
        .bindTooltip('Utolsó pozíció');

    let follow = true;
    let programmatic = false;
    map.on('dragstart', () => { follow = false; });
    map.on('zoomstart', () => { if (!programmatic) follow = false; });

    const fit = () => {
        const points = route.getLatLngs();
        if (!points.length) return;
        programmatic = true;
        if (points.length === 1) map.setView(points[0], 16, { animate: false });
        else map.fitBounds(route.getBounds(), { padding: [44, 44], maxZoom: 17, animate: false }); // a vezérlők ne takarják
        programmatic = false;
    };

    const FollowControl = L.Control.extend({
        onAdd() {
            const button = L.DomUtil.create('button', 'map-follow');
            button.type = 'button';
            button.textContent = 'Követés';
            button.title = 'Az egész útvonal és az utolsó pozíció mutatása';
            L.DomEvent.disableClickPropagation(button);
            L.DomEvent.on(button, 'click', () => {
                follow = true;
                fit();
            });
            return button;
        },
    });
    new FollowControl({ position: 'topright' }).addTo(map);

    return {
        update(points) {
            const latlngs = points.map((p) => [p.lat, p.lng]);
            route.setLatLngs(latlngs);
            last.setLatLng(latlngs[latlngs.length - 1]).addTo(map);
            if (latlngs.length > 1) start.setLatLng(latlngs[0]).addTo(map);
            else start.remove();

            map.invalidateSize(); // a container közben láthatóvá válhatott / átméreteződhetett
            if (follow) fit();
        },
    };
}
