<div class="accessibility-widget" data-accessibility-widget>
    <button type="button" class="accessibility-trigger" data-accessibility-toggle aria-expanded="false"
        aria-controls="accessibility-panel" aria-label="Abrir opciones de accesibilidad">
        <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="4.5" r="2" /><path d="M5 9h14M12 7v13M8 21l4-7 4 7" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span>Accesibilidad</span>
    </button>

    <section id="accessibility-panel" class="accessibility-panel" data-accessibility-panel hidden
        role="dialog" aria-modal="false" aria-labelledby="accessibility-title" tabindex="-1">
        <div class="flex items-start justify-between gap-3">
            <div><h2 id="accessibility-title">Accesibilidad</h2><p>Ajusta la interfaz a tus necesidades.</p></div>
            <button type="button" class="accessibility-close" data-accessibility-close aria-label="Cerrar opciones">×</button>
        </div>
        <fieldset><legend>Tamaño de texto</legend><div class="accessibility-options">
            <button type="button" data-a11y-font="normal" aria-pressed="true">Normal</button>
            <button type="button" data-a11y-font="large" aria-pressed="false">Grande</button>
            <button type="button" data-a11y-font="xlarge" aria-pressed="false">Muy grande</button>
        </div></fieldset>
        <fieldset><legend>Lectura y visualización</legend><div class="accessibility-options accessibility-options-stack">
            <button type="button" data-a11y-toggle="contrast" aria-pressed="false">Alto contraste</button>
            <button type="button" data-a11y-toggle="links" aria-pressed="false">Subrayar enlaces</button>
            <button type="button" data-a11y-toggle="spacing" aria-pressed="false">Más espaciado</button>
            <button type="button" data-a11y-toggle="readable" aria-pressed="false">Fuente de fácil lectura</button>
            <button type="button" data-a11y-toggle="motion" aria-pressed="false">Reducir movimiento</button>
            <button type="button" data-a11y-toggle="grayscale" aria-pressed="false">Escala de grises</button>
        </div></fieldset>
        <p class="accessibility-note">La aplicación no reproduce audio automático: alertas, validaciones y estados se muestran siempre de forma visual.</p>
        <button type="button" class="accessibility-reset" data-a11y-reset>Restablecer ajustes</button>
        <p class="sr-only" aria-live="polite" data-accessibility-status></p>
    </section>
</div>
