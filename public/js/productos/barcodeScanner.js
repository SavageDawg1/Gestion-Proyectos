(function () {
    const input = document.querySelector('[data-barcode-input]');
    const button = document.querySelector('[data-barcode-scan]');

    if (!input || !button) return;

    let stream = null;
    let scanning = false;
    let animationFrame = null;

    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
        }
    });

    function setInputValue(value) {
        input.value = value;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
        input.focus();
    }

    function getScannerModal() {
        let modal = document.getElementById('barcodeScannerModal');
        if (modal) return modal;

        modal = document.createElement('div');
        modal.id = 'barcodeScannerModal';
        modal.className = 'barcode-scanner-modal';
        modal.innerHTML = [
            '<div class="barcode-scanner-box">',
            '  <div class="barcode-scanner-header">',
            '    <h3>Escanear codigo</h3>',
            '    <button type="button" class="barcode-scanner-close" data-barcode-close>X</button>',
            '  </div>',
            '  <video class="barcode-scanner-video" playsinline muted></video>',
            '  <p class="barcode-scanner-status">Apunta la camara al codigo de barra.</p>',
            '</div>'
        ].join('');

        document.body.appendChild(modal);
        modal.querySelector('[data-barcode-close]').addEventListener('click', stopScanner);
        modal.addEventListener('click', function (event) {
            if (event.target === modal) stopScanner();
        });
        return modal;
    }

    function stopScanner() {
        scanning = false;
        if (animationFrame) {
            cancelAnimationFrame(animationFrame);
            animationFrame = null;
        }
        if (stream) {
            stream.getTracks().forEach(function (track) { track.stop(); });
            stream = null;
        }
        const modal = document.getElementById('barcodeScannerModal');
        if (modal) {
            modal.classList.remove('is-open');
            const video = modal.querySelector('video');
            if (video) video.srcObject = null;
        }
    }

    async function startScanner() {
        if (!('BarcodeDetector' in window)) {
            alert('Este navegador no soporta escaneo por camara. Puedes usar un lector USB o escribir el codigo manualmente.');
            input.focus();
            return;
        }

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('No se pudo acceder a la camara desde este navegador.');
            input.focus();
            return;
        }

        const modal = getScannerModal();
        const video = modal.querySelector('video');
        const status = modal.querySelector('.barcode-scanner-status');
        modal.classList.add('is-open');
        status.textContent = 'Solicitando permiso de camara...';

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' } },
                audio: false
            });
            video.srcObject = stream;
            await video.play();

            let detector;
            try {
                detector = new BarcodeDetector({
                    formats: ['code_128', 'code_39', 'ean_13', 'ean_8', 'upc_a', 'upc_e', 'itf', 'codabar']
                });
            } catch (error) {
                detector = new BarcodeDetector();
            }

            scanning = true;
            status.textContent = 'Apunta la camara al codigo de barra.';

            async function scanFrame() {
                if (!scanning) return;
                try {
                    const codes = await detector.detect(video);
                    if (codes.length > 0 && codes[0].rawValue) {
                        setInputValue(codes[0].rawValue);
                        stopScanner();
                        return;
                    }
                } catch (error) {
                    status.textContent = 'No se pudo leer el codigo. Intenta acercar o mejorar la luz.';
                }
                animationFrame = requestAnimationFrame(scanFrame);
            }

            scanFrame();
        } catch (error) {
            stopScanner();
            alert('No se pudo abrir la camara. Revisa permisos del navegador o usa un lector USB.');
            input.focus();
        }
    }

    button.addEventListener('click', startScanner);
    window.addEventListener('beforeunload', stopScanner);
})();
