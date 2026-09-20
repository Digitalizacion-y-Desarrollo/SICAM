import './bootstrap';
import './software-responsables';
import Swal from 'sweetalert2';
// import './registro-bien';
// import './campos-categoria';
import './departamentos-responsable';
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

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        if (!input) return;

        const showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        button.setAttribute('aria-label', showing ? 'Mostrar contraseña' : 'Ocultar contraseña');
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
    }
}
