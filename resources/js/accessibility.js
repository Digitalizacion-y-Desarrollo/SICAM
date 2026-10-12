const storageKey = 'sicam-accessibility';
const root = document.documentElement;
const widget = document.querySelector('[data-accessibility-widget]');

if (widget) {
    const toggle = widget.querySelector('[data-accessibility-toggle]');
    const panel = widget.querySelector('[data-accessibility-panel]');
    const close = widget.querySelector('[data-accessibility-close]');
    const status = widget.querySelector('[data-accessibility-status]');
    const defaultSettings = { font: 'normal', contrast: false, links: false, spacing: false, readable: false, motion: false, grayscale: false };
    let settings = { ...defaultSettings };

    try { settings = { ...defaultSettings, ...JSON.parse(localStorage.getItem(storageKey) || '{}') }; } catch { /* Se usan los valores predeterminados. */ }

    const labels = { contrast: 'Alto contraste', links: 'Enlaces subrayados', spacing: 'Espaciado ampliado', readable: 'Fuente de fácil lectura', motion: 'Movimiento reducido', grayscale: 'Escala de grises' };

    const apply = (announce = false) => {
        root.classList.toggle('a11y-font-large', settings.font === 'large');
        root.classList.toggle('a11y-font-xlarge', settings.font === 'xlarge');
        Object.keys(labels).forEach((key) => root.classList.toggle(`a11y-${key}`, settings[key]));
        widget.querySelectorAll('[data-a11y-font]').forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.a11yFont === settings.font)));
        widget.querySelectorAll('[data-a11y-toggle]').forEach((button) => button.setAttribute('aria-pressed', String(settings[button.dataset.a11yToggle])));
        localStorage.setItem(storageKey, JSON.stringify(settings));
        if (announce) status.textContent = 'Configuración de accesibilidad actualizada.';
    };

    const setPanel = (open) => {
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
        if (open) panel.focus(); else toggle.focus();
    };

    toggle.addEventListener('click', () => setPanel(panel.hidden));
    close.addEventListener('click', () => setPanel(false));
    widget.querySelectorAll('[data-a11y-font]').forEach((button) => button.addEventListener('click', () => { settings.font = button.dataset.a11yFont; apply(true); }));
    widget.querySelectorAll('[data-a11y-toggle]').forEach((button) => button.addEventListener('click', () => { const key = button.dataset.a11yToggle; settings[key] = !settings[key]; apply(true); }));
    widget.querySelector('[data-a11y-reset]').addEventListener('click', () => { settings = { ...defaultSettings }; apply(true); });
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !panel.hidden) setPanel(false); });
    document.addEventListener('click', (event) => { if (!panel.hidden && !widget.contains(event.target)) setPanel(false); });
    apply();
}
