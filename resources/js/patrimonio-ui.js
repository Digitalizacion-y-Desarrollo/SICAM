import Swal from 'sweetalert2';
import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';

const dialog = Swal.mixin({ confirmButtonColor: '#601633', cancelButtonColor: '#64748b', confirmButtonText: 'Aceptar' });
const pending = new WeakSet();
const originals = new Map();

document.querySelectorAll('form').forEach((form) => {
    if (form.method.toUpperCase() === 'GET' || form.querySelector('[name="_request_id"]')) return;
    const bytes = crypto.getRandomValues(new Uint8Array(16));
    bytes[6] = (bytes[6] & 15) | 64; bytes[8] = (bytes[8] & 63) | 128;
    const hex = [...bytes].map((b) => b.toString(16).padStart(2, '0')).join('');
    const token = document.createElement('input'); token.type = 'hidden'; token.name = '_request_id';
    token.value = `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
    form.append(token);
});

let showingValidation = false;
document.addEventListener('invalid', (event) => {
    event.preventDefault();
    if (showingValidation) return;
    showingValidation = true;
    dialog.fire({ icon: 'warning', title: 'Revisa los datos', text: event.target.validationMessage }).then(() => {
        showingValidation = false; event.target.focus();
    });
}, true);

function lock(element) {
    if (originals.has(element)) return;
    originals.set(element, { html: element.innerHTML, disabled: element.disabled });
    element.setAttribute('aria-busy', 'true');
    element.setAttribute('aria-disabled', 'true');
    if ('disabled' in element) element.disabled = true;
    element.innerHTML = '<span class="action-spinner" aria-hidden="true"></span><span>Procesando…</span>';
}

function unlockAll() {
    originals.forEach((original, element) => {
        element.innerHTML = original.html;
        element.removeAttribute('aria-busy');
        element.removeAttribute('aria-disabled');
        if ('disabled' in element) element.disabled = original.disabled;
    });
    originals.clear();
    document.querySelectorAll('form').forEach((form) => pending.delete(form));
}

document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    event.preventDefault();
    if (pending.has(form)) return;
    pending.add(form);
    if (form.hasAttribute('data-download-form')) {
        form.querySelectorAll('button:not([type="button"])').forEach(lock);
        try {
            const url = new URL(form.action); url.search = new URLSearchParams(new FormData(form)).toString();
            const response = await fetch(url, { headers: { Accept: 'application/octet-stream' } });
            if (!response.ok || response.redirected) throw new Error('No se pudo descargar la plantilla. Revisa la categoría seleccionada.');
            const blobUrl = URL.createObjectURL(await response.blob());
            const link = document.createElement('a'); link.href = blobUrl;
            link.download = response.headers.get('Content-Disposition')?.match(/filename="?([^";]+)/)?.[1] || 'plantilla';
            document.body.append(link); link.click(); link.remove();
            window.setTimeout(() => URL.revokeObjectURL(blobUrl), 1000);
        } catch (error) { await dialog.fire({ icon: 'error', title: 'No se completó la descarga', text: error.message }); }
        finally { unlockAll(); }
        return;
    }
    const method = (form.querySelector('[name="_method"]')?.value || form.method).toUpperCase();
    if (method === 'DELETE' || form.dataset.confirm) {
        const result = await dialog.fire({
            title: '¿Estás seguro?', text: form.dataset.confirm || 'Se eliminará este registro. La acción quedará en la auditoría.',
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, continuar', cancelButtonText: 'Cancelar', focusCancel: true,
        });
        if (!result.isConfirmed) { pending.delete(form); return; }
    }
    // Conservar el valor del botón antes de deshabilitarlo.
    if (event.submitter?.name) {
        const value = document.createElement('input');
        value.type = 'hidden'; value.name = event.submitter.name; value.value = event.submitter.value;
        form.append(value);
    }
    form.querySelectorAll('button:not([type="button"]), input[type="submit"]').forEach(lock);
    form.setAttribute('aria-busy', 'true');
    HTMLFormElement.prototype.submit.call(form);
});

document.addEventListener('click', async (event) => {
    const link = event.target.closest('a[data-crud], a[data-download]');
    if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.button !== 0) return;
    if (originals.has(link)) { event.preventDefault(); return; }
    if (!link.hasAttribute('data-download')) { lock(link); return; }
    event.preventDefault();
    lock(link);
    try {
        const response = await fetch(link.href, { headers: { Accept: 'application/octet-stream' } });
        if (!response.ok || response.redirected) throw new Error('No se pudo descargar el archivo. Vuelve a intentarlo.');
        const blob = await response.blob();
        const url = URL.createObjectURL(blob);
        const download = document.createElement('a');
        download.href = url;
        download.download = link.dataset.filename || 'descarga';
        document.body.append(download); download.click(); download.remove();
        window.setTimeout(() => URL.revokeObjectURL(url), 1000);
    } catch (error) {
        await dialog.fire({ icon: 'error', title: 'No se completó la descarga', text: error.message });
    } finally { unlockAll(); }
});

window.addEventListener('pageshow', unlockAll);

const notifications = document.querySelector('#system-notifications');
if (notifications) {
    const data = JSON.parse(notifications.textContent);
    Object.entries(data.fieldErrors || {}).forEach(([key, messages], index) => {
        if (document.querySelector(`[data-field-error="${CSS.escape(key)}"]`)) return;

        const parts = key.split('.');
        const fieldName = parts.shift() + parts.map((part) => `[${part}]`).join('');
        let fields = [...document.getElementsByName(fieldName)];
        if (!fields.length && parts.every((part) => /^\d+$/.test(part))) {
            fields = [...document.getElementsByName(`${key.split('.')[0]}[]`)];
        }
        const field = fields.find((candidate) => candidate.type !== 'hidden') || fields[0];
        if (!field) return;

        field.closest('details')?.setAttribute('open', '');
        const error = document.createElement('p');
        error.id = `error-campo-${index}`;
        error.className = 'mt-1 text-sm text-red-700';
        error.dataset.fieldError = key;
        error.setAttribute('role', 'alert');
        error.textContent = messages.join(' ');
        field.classList.add('border-red-500');
        field.setAttribute('aria-invalid', 'true');
        field.setAttribute('aria-describedby', [field.getAttribute('aria-describedby'), error.id].filter(Boolean).join(' '));
        field.insertAdjacentElement('afterend', error);
    });
    if (data.errors.length) dialog.fire({ icon: 'error', title: 'Revisa los datos', text: data.errors.join('\n') });
    else if (data.error) dialog.fire({ icon: 'error', title: 'No se completó la acción', text: data.error });
    else if (data.success) dialog.fire({ icon: 'success', title: 'Acción completada', text: data.success });
}

document.querySelectorAll('[data-help]').forEach((button) => {
    button.addEventListener('click', () => {
        const steps = [...document.querySelectorAll('main [data-tour]')].filter((element) => element.getClientRects().length)
            .map((element) => ({ element, popover: { title: element.dataset.tourTitle || 'Ayuda', description: element.dataset.tour } }));
        if (!steps.length) {
            const title = document.querySelector('main h1');
            if (title) steps.push({ element: title, popover: { title: title.textContent, description: 'Usa las opciones de esta sección para consultar y administrar los registros.' } });
            const form = document.querySelector('main form');
            if (form) steps.push({ element: form, popover: { title: 'Formulario', description: 'Completa los datos indicados. Al guardar, espera el resultado antes de continuar.' } });
            const table = document.querySelector('main table');
            if (table) steps.push({ element: table, popover: { title: 'Registros', description: 'Aquí encontrarás los registros y las acciones disponibles para cada uno.' } });
        }
        driver({ showProgress: true, nextBtnText: 'Siguiente', prevBtnText: 'Anterior', doneBtnText: 'Terminar', progressText: '{{current}} de {{total}}', steps }).drive();
    });
});
