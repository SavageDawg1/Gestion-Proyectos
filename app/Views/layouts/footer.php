<?php
/**
 * Footer - Se incluye en todas las páginas.
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
            <p>&copy; 2026 Sistema de Almacén. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>
</html>
