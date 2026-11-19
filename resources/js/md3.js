const openers = new WeakMap();

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

function initializeMd3() {
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
        openers.get(event.target)?.focus();
        openers.delete(event.target);
    }
}, true);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeMd3, { once: true });
} else {
    initializeMd3();
}
