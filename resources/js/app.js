import './bootstrap';
import './software-responsables';
import Swal from 'sweetalert2';
import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';

window.Swal = Swal;
// import './registro-bien';
// import './campos-categoria';
import './departamentos-responsable';
import './busqueda-global';
// import './asignacion';
// import './patrimonio-ui';

const sidebar = document.querySelector('#main-sidebar');
const backdrop = document.querySelector('#sidebar-backdrop');

const closeSidebar = () => {
    sidebar?.classList.add('-translate-x-full');
    backdrop?.classList.add('hidden');
};

document.querySelector('[data-sidebar-open]')?.addEventListener('click', () => {
    sidebar?.classList.remove('-translate-x-full');
    backdrop?.classList.remove('hidden');
});

document.querySelector('[data-sidebar-close]')?.addEventListener('click', closeSidebar);
backdrop?.addEventListener('click', closeSidebar);

const responsibleCreate = document.querySelector('#responsible-create');
document.querySelector('[data-responsible-create]')?.addEventListener('click', () => responsibleCreate?.classList.toggle('hidden'));
document.querySelectorAll('input[name="responsabilidad"]').forEach((radio) => radio.addEventListener('change', () => {
    document.querySelector('#responsable-persona')?.classList.toggle('hidden', radio.value !== 'persona' || !radio.checked);
    document.querySelector('#responsable-area')?.classList.toggle('hidden', radio.value !== 'area' || !radio.checked);
}));

document.querySelectorAll('[data-dropdown-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
        const panel = document.querySelector(`#${toggle.dataset.dropdownToggle}`);
        const arrow = toggle.querySelector('.dropdown-arrow');
        const isOpen = toggle.getAttribute('aria-expanded') === 'true';

        toggle.setAttribute('aria-expanded', String(!isOpen));
        arrow?.classList.toggle('is-open', !isOpen);

        if (isOpen) {
            panel?.classList.remove('is-open');
            window.setTimeout(() => panel?.setAttribute('hidden', ''), 200);
        } else {
            panel?.removeAttribute('hidden');
            window.requestAnimationFrame(() => panel?.classList.add('is-open'));
        }
    });
});

const isTourElementVisible = (element) => element.getClientRects().length > 0;

const explicitTourElements = () => [...document.querySelectorAll('[data-tour]')]
    .filter(isTourElementVisible)
    .sort((first, second) => Number(first.dataset.tourOrder || 0) - Number(second.dataset.tourOrder || 0))
    .map((element) => ({
        element,
        popover: {
            title: element.dataset.tourTitle || 'Ayuda',
            description: element.dataset.tour,
        },
    }));

const formFields = (container) => [...container.querySelectorAll('input:not([type="hidden"]), select, textarea')]
    .filter((field) => isTourElementVisible(field) && !field.disabled);

const formFieldNames = (container) => {
    const names = [...container.querySelectorAll('.form-label, legend')]
        .filter(isTourElementVisible)
        .map((label) => label.textContent.replace(/\s*\*\s*$/, '').trim())
        .filter(Boolean);

    return [...new Set(names)];
};

const formSectionDescription = (section) => {
    const names = formFieldNames(section);
    const displayedNames = names.slice(0, 5);
    const remaining = names.length - displayedNames.length;
    const fields = displayedNames.length
        ? `Captura o revisa: ${displayedNames.join(', ')}${remaining > 0 ? ` y ${remaining} campo${remaining === 1 ? '' : 's'} más` : ''}.`
        : 'Completa la información solicitada en esta sección.';
    const required = formFields(section).some((field) => field.required)
        ? ' Los campos marcados con * son obligatorios.'
        : '';

    return `${fields}${required}`;
};

const formTourElements = () => {
    const forms = [...document.querySelectorAll('main form')]
        .filter((form) => isTourElementVisible(form)
            && (form.getAttribute('method') || 'get').toLowerCase() !== 'get'
            && formFields(form).length >= 2);

    if (!forms.length) return [];

    const steps = [];
    const pageTitle = document.querySelector('main h1');

    if (pageTitle && isTourElementVisible(pageTitle)) {
        steps.push({
            element: pageTitle,
            popover: {
                title: pageTitle.textContent.trim(),
                description: 'Este recorrido explica las secciones del formulario, los datos que debes capturar y la acción para guardar.',
            },
        });
    }

    forms.forEach((form) => {
        let sections = [...form.querySelectorAll('.form-card')]
            .filter((section) => isTourElementVisible(section) && formFields(section).length);

        sections = sections.filter((section) => !sections.some((parent) => parent !== section && parent.contains(section)));

        if (!sections.length) sections = [form];

        sections.forEach((section, index) => {
            const heading = section.querySelector('h2, legend');
            const formHeading = form.querySelector('h1, h2');

            steps.push({
                element: section,
                popover: {
                    title: heading?.textContent.trim() || formHeading?.textContent.trim() || `Datos del formulario ${index + 1}`,
                    description: formSectionDescription(section),
                },
            });
        });

        const submit = [...form.querySelectorAll('button:not([type]), button[type="submit"], input[type="submit"]')]
            .find(isTourElementVisible);

        if (submit) {
            const buttonText = submit.value || submit.textContent.trim() || 'Guardar';
            steps.push({
                element: submit,
                popover: {
                    title: buttonText,
                    description: 'Revisa la información capturada y selecciona este botón para guardar los cambios.',
                },
            });
        }
    });

    const explicitSteps = explicitTourElements().filter((step) => step.element.closest('main'));
    const uniqueSteps = [...steps, ...explicitSteps]
        .filter((step, index, allSteps) => allSteps.findIndex((candidate) => candidate.element === step.element) === index);

    return uniqueSteps.sort((first, second) => {
        if (first.element === second.element) return 0;

        return first.element.compareDocumentPosition(second.element) & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : 1;
    });
};

const tourElements = () => {
    const formSteps = formTourElements();

    return formSteps.length ? formSteps : explicitTourElements();
};

const startTour = () => {
    const steps = tourElements();

    if (!steps.length) {
        const title = document.querySelector('main h1');
        if (title) {
            steps.push({
                element: title,
                popover: {
                    title: title.textContent,
                    description: 'Consulta y administra la información disponible en esta sección.',
                },
            });
        }
    }

    if (!steps.length) return;

    driver({
        showProgress: true,
        allowClose: true,
        overlayClickBehavior: 'close',
        stagePadding: 8,
        stageRadius: 12,
        nextBtnText: 'Siguiente',
        prevBtnText: 'Anterior',
        doneBtnText: 'Terminar',
        progressText: '{{current}} de {{total}}',
        steps,
    }).drive();
};

document.querySelectorAll('[data-help]').forEach((button) => {
    button.addEventListener('click', startTour);
});

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        if (!input) return;

        const showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        button.setAttribute('aria-label', showing ? 'Mostrar contraseña' : 'Ocultar contraseña');
    });
});

document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const result = await Swal.fire({
            icon: 'warning',
            title: form.dataset.confirmTitle || '¿Eliminar registro?',
            text: form.dataset.confirmMessage || 'Esta acción no se puede deshacer.',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#601633',
            cancelButtonColor: '#64748b',
            reverseButtons: true,
            focusCancel: true,
        });

        if (result.isConfirmed) {
            form.submit();
        }
    });
});

const notifications = document.querySelector('#system-notifications');
if (notifications) {
    const data = JSON.parse(notifications.textContent);

    if (data.success) {
        Swal.fire({
            icon: 'success',
            title: 'Acción completada',
            text: data.success,
            confirmButtonColor: '#601633',
        });
    } else if (data.error) {
        Swal.fire({
            icon: 'error',
            title: 'No se pudo completar la acción',
            text: data.error,
            confirmButtonColor: '#601633',
        });
    }
}
