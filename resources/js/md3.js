const openers = new WeakMap();

function initializeTheme() {
    const toggle = document.querySelector('[data-md3-theme]');
    if (!toggle) return;

    const root = document.documentElement;
    const updateToggle = () => {
        const dark = root.dataset.theme === 'dark';
        const label = dark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap';
        toggle.setAttribute('aria-label', label);
        toggle.title = label;
        toggle.querySelector('[data-theme-icon="light"]').toggleAttribute('hidden', dark);
        toggle.querySelector('[data-theme-icon="dark"]').toggleAttribute('hidden', !dark);
    };

    updateToggle();
    toggle.hidden = false;
    toggle.addEventListener('click', () => {
        root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
        try {
            localStorage.setItem('sembaku.theme', root.dataset.theme);
        } catch {
            // Tema tetap dapat diubah ketika penyimpanan browser diblokir.
        }
        updateToggle();
    });
}

function initializePassword(toggle) {
    const input = document.getElementById(toggle.getAttribute('aria-controls'));
    if (!(input instanceof HTMLInputElement)) return;

    toggle.hidden = false;
    toggle.title = toggle.getAttribute('aria-label');
    toggle.addEventListener('click', () => {
        const visible = input.type === 'password';
        const label = (visible ? 'Sembunyikan ' : 'Tampilkan ') + toggle.dataset.passwordLabel;
        input.type = visible ? 'text' : 'password';
        toggle.setAttribute('aria-label', label);
        toggle.title = label;
        toggle.querySelector('[data-password-icon="show"]').toggleAttribute('hidden', visible);
        toggle.querySelector('[data-password-icon="hide"]').toggleAttribute('hidden', !visible);
    });

    const capsLock = document.getElementById(input.dataset.capsLock);
    if (capsLock) {
        const updateCapsLock = (event) => { capsLock.hidden = !event.getModifierState('CapsLock'); };
        input.addEventListener('keydown', updateCapsLock);
        input.addEventListener('keyup', updateCapsLock);
        input.addEventListener('blur', () => { capsLock.hidden = true; });
    }
}

function afterExit(element, complete) {
    if (element.hasAttribute('data-md3-exiting')) {
        return;
    }

    element.setAttribute('data-md3-exiting', '');
    const exit = element.getAnimations().find((animation) => animation.animationName === 'md3-exit');
    const finish = () => {
        complete();
        element.removeAttribute('data-md3-exiting');
    };

    if (exit) {
        exit.finished.then(finish, finish);
    } else {
        finish();
    }
}

function initializeDrawer() {
    const trigger = document.querySelector('[data-md3-drawer-trigger]');
    const sidebar = document.querySelector('[data-md3-drawer-sidebar]');
    const navigation = document.getElementById('mainNavigation');
    const dialog = document.getElementById(trigger?.getAttribute('aria-controls'));
    const content = dialog?.querySelector('[data-md3-drawer-content]');
    if (!trigger || !sidebar || !navigation || !content || typeof dialog.showModal !== 'function') return;

    const mobile = matchMedia('(max-width: 48rem)');
    const dockNavigation = () => {
        if (mobile.matches) {
            content.append(navigation);
            sidebar.hidden = true;
            trigger.hidden = false;
        } else {
            dialog.close();
            sidebar.append(navigation);
            sidebar.hidden = false;
            trigger.hidden = true;
        }
    };

    trigger.addEventListener('click', () => {
        if (!mobile.matches || dialog.open) return;

        openers.set(dialog, trigger);
        trigger.setAttribute('aria-expanded', 'true');
        dialog.showModal();
        const firstLink = navigation.querySelector('[aria-current="page"]') ?? navigation.querySelector('a');
        firstLink?.focus();
    });
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) afterExit(dialog, () => dialog.close());
    });
    dialog.addEventListener('close', () => trigger.setAttribute('aria-expanded', 'false'));
    mobile.addEventListener('change', dockNavigation);
    dockNavigation();
}

function initializeConfirmations() {
    const dialog = document.getElementById('confirmRecordAction');
    if (typeof dialog?.showModal !== 'function') return;

    const message = dialog.querySelector('[data-md3-confirm-message]');
    const confirm = dialog.querySelector('[data-md3-confirm-submit]');
    let pending = null;
    let approvedForm = null;

    document.querySelectorAll('form[data-md3-confirm]').forEach((form) => {
        form.removeAttribute('onsubmit');
        form.addEventListener('submit', (event) => {
            if (approvedForm === form) {
                approvedForm = null;
                return;
            }

            event.preventDefault();
            if (dialog.open) return;

            const submitter = event.submitter ?? form.querySelector('button[type="submit"]');
            pending = { form, submitter };
            message.textContent = form.dataset.md3Confirm;
            confirm.textContent = form.dataset.md3ConfirmAction;
            openers.set(dialog, submitter);
            dialog.showModal();
        });
    });

    confirm.addEventListener('click', () => {
        if (!pending || dialog.hasAttribute('data-md3-exiting')) return;

        const { form, submitter } = pending;
        pending = null;
        approvedForm = form;
        dialog.close();
        try {
            form.requestSubmit(submitter ?? undefined);
        } finally {
            approvedForm = null;
        }
    });
    dialog.addEventListener('close', () => { pending = null; });
}

function focusValidationError() {
    if (!document.body.hasAttribute('data-md3-validation-failed')) return;

    const invalid = [...document.querySelectorAll('main :is(input, select, textarea):is([aria-invalid="true"], .md3-control--error):enabled')]
        .find((field) => field.getClientRects().length);
    const summary = document.querySelector('main [data-md3-error-summary], main .md3-alert[role="alert"]');
    (invalid ?? summary)?.focus();
}

function initializeMd3() {
    initializeTheme();
    initializeDrawer();
    initializeConfirmations();
    document.querySelectorAll('[data-md3-password]').forEach(initializePassword);

    document.querySelectorAll('select[data-md3-select-value]').forEach((select) => {
        const values = JSON.parse(select.dataset.md3SelectValue);

        const options = [...select.options];
        const match = options.findIndex((option) => option.value === values[0]);
        if (select.multiple || match !== -1) {
            options.forEach((option, index) => {
                option.defaultSelected = select.multiple ? values.includes(option.value) : index === match;
                option.selected = option.defaultSelected;
            });
            if (!select.multiple) {
                select.selectedIndex = match;
            }
        }
    });

    document.querySelectorAll('[data-md3-disclosure]').forEach((toggle) => {
        toggle.setAttribute('aria-expanded', 'false');
        toggle.hidden = false;
        const body = document.getElementById(toggle.getAttribute('aria-controls'));
        body?.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true' && toggle.getClientRects().length) {
                toggle.setAttribute('aria-expanded', 'false');
                toggle.focus();
            }
        });
    });
    focusValidationError();
}

document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) {
        return;
    }

    const control = event.target.closest('[data-md3-disclosure], [data-md3-dialog-open], [data-md3-dialog-close], [data-md3-snackbar-show], [data-md3-snackbar-dismiss]');

    if (!control || control.matches(':disabled, [aria-disabled="true"]')) {
        return;
    }

    if (control.hasAttribute('data-md3-disclosure')) {
        control.setAttribute('aria-expanded', String(control.getAttribute('aria-expanded') !== 'true'));
    } else if (control.hasAttribute('data-md3-dialog-open')) {
        const dialog = document.getElementById(control.dataset.md3DialogOpen);
        if (dialog instanceof HTMLDialogElement && dialog.hasAttribute('data-md3-dialog') && !dialog.open) {
            openers.set(dialog, control);
            dialog.showModal();
        }
    } else if (control.hasAttribute('data-md3-dialog-close')) {
        const dialog = control.closest('dialog[data-md3-dialog]');
        if (dialog?.open) {
            afterExit(dialog, () => dialog.close());
        }
    } else if (control.hasAttribute('data-md3-snackbar-show')) {
        const snackbar = document.getElementById(control.dataset.md3SnackbarShow);
        if (snackbar?.matches('[data-md3-snackbar]')) {
            openers.set(snackbar, control);
            snackbar.hidden = false;
        }
    } else {
        const snackbar = control.closest('[data-md3-snackbar]');
        if (snackbar) {
            afterExit(snackbar, () => {
                const returnFocus = snackbar.contains(document.activeElement);
                snackbar.hidden = true;
                if (returnFocus) {
                    openers.get(snackbar)?.focus();
                }
            });
        }
    }
});

document.addEventListener('cancel', (event) => {
    const dialog = event.target;
    if (dialog instanceof HTMLDialogElement && dialog.hasAttribute('data-md3-dialog')) {
        event.preventDefault();
        afterExit(dialog, () => dialog.close());
    }
}, true);

document.addEventListener('close', (event) => {
    if (event.target instanceof HTMLDialogElement) {
        const opener = openers.get(event.target);
        if (opener?.getClientRects().length) opener.focus();
        openers.delete(event.target);
    }
}, true);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeMd3, { once: true });
} else {
    initializeMd3();
}
