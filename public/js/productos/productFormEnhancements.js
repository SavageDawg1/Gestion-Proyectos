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
        const combobox = form.querySelector('[data-category-combobox]');
        const toggleButton = form.querySelector('[data-category-toggle]');
        const categories = Array.isArray(window.productFormCategories) ? window.productFormCategories : [];

        if (!categoryInput || !categoryIdInput || !status || !suggestionBox || !combobox) {
            return;
        }

        let activeIndex = -1;
        let visibleMatches = [];

        function openSuggestions() {
            combobox.classList.add('is-open');
            categoryInput.setAttribute('aria-expanded', 'true');
        }

        function closeSuggestions() {
            combobox.classList.remove('is-open');
            categoryInput.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
            updateActiveOption();
        }

        function clearSuggestions() {
            suggestionBox.innerHTML = '';
            visibleMatches = [];
            closeSuggestions();
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
            categoryInput.setCustomValidity('');
            clearSuggestions();
            setStatus('Categoria seleccionada: ' + category.nombre + '.', 'success');
        }

        function updateActiveOption() {
            Array.from(suggestionBox.querySelectorAll('.category-suggestion')).forEach((button, index) => {
                button.classList.toggle('is-active', index === activeIndex);
                if (index === activeIndex) {
                    button.setAttribute('aria-selected', 'true');
                } else {
                    button.setAttribute('aria-selected', 'false');
                }
            });
        }

        function renderSuggestions(matches, forceOpen) {
            suggestionBox.innerHTML = '';
            visibleMatches = matches.slice(0, 8);

            if (visibleMatches.length === 0) {
                closeSuggestions();
                return;
            }

            visibleMatches.forEach((category) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'category-suggestion';
                button.setAttribute('role', 'option');
                button.setAttribute('aria-selected', 'false');
                button.textContent = category.nombre;
                button.addEventListener('click', () => selectCategory(category));
                suggestionBox.appendChild(button);
            });

            if (forceOpen || document.activeElement === categoryInput) {
                openSuggestions();
            }
        }

        function getMatches(value) {
            const normalized = normalizeText(value);
            if (normalized === '') {
                return categories;
            }

            return categories.filter((category) => normalizeText(category.nombre).includes(normalized));
        }

        function updateCategoryState(forceOpen) {
            const value = categoryInput.value.trim();
            const normalized = normalizeText(value);
            categoryIdInput.value = '';
            categoryInput.setCustomValidity('');

            if (value === '') {
                renderSuggestions(categories, forceOpen);
                setStatus(categories.length > 0 ? 'Selecciona una categoria existente.' : 'No hay categorias registradas.', categories.length > 0 ? '' : 'warning');
                return;
            }

            const exact = categories.find((category) => normalizeText(category.nombre) === normalized);
            if (exact) {
                categoryIdInput.value = exact.id;
                renderSuggestions(getMatches(value), forceOpen);
                setStatus('Categoria seleccionada: ' + exact.nombre + '.', 'success');
                return;
            }

            const matches = getMatches(value);
            if (matches.length > 0) {
                renderSuggestions(matches, forceOpen);
                setStatus('Selecciona una categoria existente o guarda para crear "' + value + '".', 'warning');
                return;
            }

            clearSuggestions();
            setStatus('Se creara una nueva categoria: ' + value + '.', 'warning');
        }

        categoryInput.addEventListener('input', () => updateCategoryState(true));
        categoryInput.addEventListener('focus', () => updateCategoryState(true));
        categoryInput.addEventListener('blur', function () {
            window.setTimeout(() => {
                updateCategoryState(false);
                closeSuggestions();
            }, 120);
        });

        categoryInput.addEventListener('keydown', function (event) {
            if (!combobox.classList.contains('is-open') && ['ArrowDown', 'ArrowUp'].includes(event.key)) {
                updateCategoryState(true);
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                activeIndex = Math.min(activeIndex + 1, visibleMatches.length - 1);
                updateActiveOption();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                updateActiveOption();
            } else if (event.key === 'Enter' && activeIndex >= 0 && visibleMatches[activeIndex]) {
                event.preventDefault();
                selectCategory(visibleMatches[activeIndex]);
            } else if (event.key === 'Escape') {
                closeSuggestions();
            }
        });

        if (toggleButton) {
            toggleButton.addEventListener('click', function () {
                if (combobox.classList.contains('is-open')) {
                    closeSuggestions();
                    return;
                }

                categoryInput.focus();
                updateCategoryState(true);
            });
        }

        form.addEventListener('submit', function () {
            updateCategoryState(false);
        });

        updateCategoryState(false);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-product-form]').forEach((form) => {
            initProductNameCheck(form);
            initCategoryInput(form);
        });
    });
})();
