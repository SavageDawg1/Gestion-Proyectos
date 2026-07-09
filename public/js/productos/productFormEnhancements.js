(function () {
    function normalizeText(value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim()
            .toLowerCase();
    }

    function initProductNameCheck(form) {
        const nameInput = form.querySelector('#nombre');
        const status = form.querySelector('[data-product-name-status]');
        if (!nameInput || !status) {
            return;
        }

        const currentId = form.dataset.productId || '';
        const originalName = normalizeText(nameInput.dataset.originalName || '');
        let timer = null;

        function setStatus(message, type) {
            status.textContent = message;
            status.className = 'field-status';
            if (type) {
                status.classList.add('field-status-' + type);
            }
        }

        function checkName() {
            const value = nameInput.value.trim();
            window.clearTimeout(timer);
            nameInput.setCustomValidity('');
            setStatus('', '');

            if (value === '' || (currentId && normalizeText(value) === originalName)) {
                return;
            }

            timer = window.setTimeout(() => {
                const params = new URLSearchParams({ nombre: value });
                if (currentId) {
                    params.set('excluir_id', currentId);
                }

                fetch('verificar_producto_nombre.php?' + params.toString(), {
                    headers: { 'Accept': 'application/json' }
                })
                    .then((response) => response.ok ? response.json() : null)
                    .then((data) => {
                        if (!data) {
                            return;
                        }

                        if (data.exists) {
                            const message = data.message || 'Ya existe un producto con este nombre.';
                            nameInput.setCustomValidity(message);
                            setStatus(message, 'warning');
                        } else {
                            nameInput.setCustomValidity('');
                            setStatus('Nombre disponible.', 'success');
                        }
                    })
                    .catch(() => {
                        setStatus('No se pudo comprobar el nombre ahora.', 'warning');
                    });
            }, 180);
        }

        nameInput.addEventListener('input', checkName);
        form.addEventListener('submit', function (event) {
            if (!nameInput.checkValidity()) {
                event.preventDefault();
                setStatus(nameInput.validationMessage, 'warning');
                nameInput.reportValidity();
            }
        });
    }

    function initCategoryInput(form) {
        const categoryInput = form.querySelector('#categoria_nombre');
        const categoryIdInput = form.querySelector('#categoria_id');
        const status = form.querySelector('[data-category-status]');
        const suggestionBox = form.querySelector('[data-category-suggestions]');
        const categories = Array.isArray(window.productFormCategories) ? window.productFormCategories : [];

        if (!categoryInput || !categoryIdInput || !status || !suggestionBox) {
            return;
        }

        function clearSuggestions() {
            suggestionBox.innerHTML = '';
        }

        function setStatus(message, type) {
            status.textContent = message;
            status.className = 'field-status';
            if (type) {
                status.classList.add('field-status-' + type);
            }
        }

        function selectCategory(category) {
            categoryInput.value = category.nombre;
            categoryIdInput.value = category.id;
            clearSuggestions();
            setStatus('Se usara la categoria existente: ' + category.nombre + '.', 'success');
        }

        function renderSuggestions(matches) {
            clearSuggestions();
            matches.slice(0, 5).forEach((category) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'category-suggestion';
                button.textContent = category.nombre;
                button.addEventListener('click', () => selectCategory(category));
                suggestionBox.appendChild(button);
            });
        }

        function updateCategoryState() {
            const value = categoryInput.value.trim();
            const normalized = normalizeText(value);
            categoryIdInput.value = '';
            clearSuggestions();

            if (value === '') {
                setStatus('Sin categoria.', '');
                return;
            }

            const exact = categories.find((category) => normalizeText(category.nombre) === normalized);
            if (exact) {
                categoryIdInput.value = exact.id;
                setStatus('Se usara la categoria existente: ' + exact.nombre + '.', 'success');
                return;
            }

            const matches = categories.filter((category) => normalizeText(category.nombre).includes(normalized));
            if (matches.length > 0) {
                renderSuggestions(matches);
                setStatus('Selecciona una categoria existente o guarda para crear "' + value + '".', 'warning');
                return;
            }

            setStatus('Se creara una nueva categoria: ' + value + '.', 'warning');
        }

        categoryInput.addEventListener('input', updateCategoryState);
        categoryInput.addEventListener('blur', updateCategoryState);
        updateCategoryState();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-product-form]').forEach((form) => {
            initProductNameCheck(form);
            initCategoryInput(form);
        });
    });
})();
