<?php
/**
 * Footer - Se incluye en todas las paginas.
 */
$showSidebar = !isset($hide_sidebar) || !$hide_sidebar;
?>
        <?php if ($showSidebar): ?>
                </div>
            </div>
        <?php endif; ?>
    </main>
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 Sistema de Almacen. Todos los derechos reservados.</p>
        </div>
    </footer>

    <div class="app-message-stack" id="app_message_stack" aria-live="polite"></div>

    <div class="app-confirm-overlay" id="app_confirm_overlay" aria-hidden="true">
        <div class="app-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="app_confirm_title">
            <h3 id="app_confirm_title">Confirmar acci&oacute;n</h3>
            <p id="app_confirm_message"></p>
            <div class="app-confirm-actions">
                <button type="button" class="btn-accion app-confirm-cancel" id="app_confirm_cancel">Cancelar</button>
                <button type="button" class="btn-accion app-confirm-accept" id="app_confirm_accept">Confirmar</button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        const stack = document.getElementById('app_message_stack');
        const overlay = document.getElementById('app_confirm_overlay');
        const message = document.getElementById('app_confirm_message');
        const cancel = document.getElementById('app_confirm_cancel');
        const accept = document.getElementById('app_confirm_accept');
        let pendingAction = null;

        window.appAlert = function(text, type) {
            if (!stack) return;

            const alert = document.createElement('div');
            alert.className = 'app-message app-message-' + (type || 'error');
            alert.textContent = text;
            stack.appendChild(alert);

            setTimeout(function() {
                alert.classList.add('is-hiding');
                setTimeout(function() {
                    alert.remove();
                }, 300);
            }, 3500);
        };

        window.appConfirm = function(text, onAccept) {
            if (!overlay || !message) {
                if (typeof onAccept === 'function') onAccept();
                return;
            }

            pendingAction = onAccept;
            message.textContent = text;
            overlay.classList.add('is-visible');
            overlay.setAttribute('aria-hidden', 'false');
        };

        function closeConfirm() {
            overlay.classList.remove('is-visible');
            overlay.setAttribute('aria-hidden', 'true');
            pendingAction = null;
        }

        if (cancel) {
            cancel.addEventListener('click', closeConfirm);
        }

        if (accept) {
            accept.addEventListener('click', function() {
                const action = pendingAction;
                closeConfirm();
                if (typeof action === 'function') action();
            });
        }

        if (overlay) {
            overlay.addEventListener('click', function(event) {
                if (event.target === overlay) closeConfirm();
            });
        }

        document.querySelectorAll('a[data-confirm-message]').forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                window.appConfirm(link.dataset.confirmMessage, function() {
                    window.location.href = link.href;
                });
            });
        });

        document.querySelectorAll('form[data-confirm-message]').forEach(function(form) {
            if (form.dataset.confirmScope || form.closest('[data-confirm-scope]')) return;

            form.addEventListener('submit', function(event) {
                if (form.dataset.confirmed === 'true') return;

                event.preventDefault();
                window.appConfirm(form.dataset.confirmMessage, function() {
                    form.dataset.confirmed = 'true';
                    form.submit();
                });
            });
        });

        document.querySelectorAll('button[data-confirm-message]').forEach(function(button) {
            if (button.dataset.confirmScope || button.closest('[data-confirm-scope]')) return;

            button.addEventListener('click', function(event) {
                const form = button.form;
                if (!form || form.dataset.confirmed === 'true') return;

                if (!button.formNoValidate && typeof form.reportValidity === 'function' && !form.reportValidity()) {
                    return;
                }

                event.preventDefault();
                window.appConfirm(button.dataset.confirmMessage, function() {
                    form.dataset.confirmed = 'true';
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit(button);
                        return;
                    }

                    if (button.name) {
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = button.name;
                        hidden.value = button.value;
                        form.appendChild(hidden);
                    }
                    form.submit();
                });
            });
        });

        document.querySelectorAll('form[data-dirty-guard]').forEach(function(form) {
            const fields = Array.from(form.querySelectorAll('input:not([type="hidden"]), textarea, select'));
            const targets = Array.from(form.querySelectorAll('[data-dirty-submit]'));
            const originalValues = new Map();

            fields.forEach(function(field) {
                originalValues.set(field.name || field.id, field.value);
            });

            function updateDirtyState() {
                const isDirty = fields.some(function(field) {
                    return field.value !== originalValues.get(field.name || field.id);
                });

                targets.forEach(function(target) {
                    target.disabled = !isDirty;
                });
            }

            fields.forEach(function(field) {
                field.addEventListener('input', updateDirtyState);
                field.addEventListener('change', updateDirtyState);
            });

            updateDirtyState();
        });

        document.querySelectorAll('[data-page-alert]').forEach(function(alert) {
            setTimeout(function() {
                alert.classList.add('page-alert-hidden');
                setTimeout(function() {
                    alert.remove();
                }, 300);
            }, 3500);
        });

        document.querySelectorAll('[data-notifications]').forEach(function(wrapper) {
            const toggle = wrapper.querySelector('[data-notifications-toggle]');
            const close = wrapper.querySelector('[data-notifications-close]');

            function setOpen(isOpen) {
                wrapper.classList.toggle('is-open', isOpen);
                if (toggle) {
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                }
            }

            if (toggle) {
                toggle.addEventListener('click', function(event) {
                    event.stopPropagation();
                    setOpen(!wrapper.classList.contains('is-open'));
                });
            }

            if (close) {
                close.addEventListener('click', function(event) {
                    event.stopPropagation();
                    setOpen(false);
                });
            }

            wrapper.addEventListener('click', function(event) {
                event.stopPropagation();
            });

            document.addEventListener('click', function() {
                setOpen(false);
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    setOpen(false);
                }
            });
        });
    })();
    </script>
</body>
</html>
