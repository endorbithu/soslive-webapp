// Kezdőlap: belépett usernek a fő gomb az eseménylistára visz.
export function initHome(me) {
    const cta = document.getElementById('home-cta');
    if (cta && me.user) {
        cta.href = '/dashboard';
        cta.textContent = 'Eseményeim';
    }
}
