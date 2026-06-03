# Sistema de Gestión de Almacén

Aplicación web para la gestión de inventario con login y dashboard.

## 🛠️ Requisitos

- **XAMPP** (Apache + MySQL + PHP)
- **PHPMyAdmin** (incluido en XAMPP)
- **Navegador web moderno**
- **VS Code u otro editor** (recomendado)

## 📁 Estructura del Proyecto

```
Software_Almacen/
├── config/          # Configuración (base de datos)
├── public/          # Archivos públicos (CSS, JS, imágenes)
├── pages/           # Páginas PHP (vistas principales)
├── api/             # Endpoints PHP (lógica de negocio)
├── includes/        # Funciones reutilizables
├── templates/       # Componentes HTML reutilizables
├── sql/             # Scripts SQL
├── assets/          # Media (imágenes, íconos)
├── index.php        # Punto de entrada
└── .htaccess        # Configuración Apache
```

## ⚡ Instalación Rápida

### 1. Clonar/Descargar el proyecto
Coloca la carpeta en: `C:\xampp\htdocs\Software_Almacen`

### 2. Crear la base de datos
- Abre **PHPMyAdmin**: http://localhost/phpmyadmin
- Ve a "SQL" y ejecuta el contenido de: `sql/schema.sql`

### 3. Configurar conexión (si es necesario)
- Edita: `config/database.php`
- Ajusta `DB_USER`, `DB_PASS`, `DB_NAME` según tu configuración

### 4. Acceder a la aplicación
- URL: `http://localhost/Software_Almacen`
- **Email**: `admin@almacen.com`
- **Contraseña**: (Verifica en `sql/schema.sql`)

## 📂 Descripción de Carpetas

### `config/`
Configuración central del proyecto
- `database.php` - Conexión a MySQL

### `public/`
Recursos estáticos accesibles públicamente
- `css/` - Hojas de estilos
- `js/` - Scripts JavaScript
- `images/` - Imágenes y media

### `pages/`
Páginas principales de la aplicación
- `login.php` - Página de login/registro
- `dashboard.php` - Panel de control

### `api/`
Endpoints para procesar datos (AJAX)
- `auth.php` - Autenticación y registro
- `response.php` - Helpers para respuestas JSON

### `includes/`
Funciones reutilizables en todo el proyecto
- `session.php` - Gestión de sesiones
- `functions.php` - Funciones generales
- `validation.php` - Validaciones

### `templates/`
Componentes HTML reutilizables
- `header.php` - Encabezado/navbar
- `footer.php` - Pie de página

### `sql/`
Scripts SQL para base de datos
- `schema.sql` - Estructura de tablas e inserts iniciales

### `assets/`
Media y recursos del proyecto
- `images/` - Imágenes, logos, iconos

## 🔐 Seguridad

- Las contraseñas están hasheadas con `password_hash()`
- Validación de entradas con `sanitizeInput()`
- Escape de caracteres especiales en BD
- Control de acceso por sesiones
- Archivos sensibles protegidos en `.htaccess`

## 🎯 Flujo de Uso

1. **Usuario no autenticado** → Redirecciona a `login.php`
2. **Login/Registro** → Valida en `api/auth.php`
3. **Sesión activa** → Acceso a `dashboard.php`
4. **Logout** → Destruye sesión y vuelve a login

## 🔄 Flujo de Comunicación (AJAX)

Frontend → `pages/login.php` (JS) → `api/auth.php` → Respuesta JSON → Renderizar resultado

## 📝 Próximas Funcionalidades

- [ ] CRUD de Productos
- [ ] CRUD de Categorías
- [ ] Movimientos de Inventario
- [ ] Reportes
- [ ] Gráficas de estadísticas
- [ ] Exportar a PDF/Excel
- [ ] Sistema de permisos por rol

## 🐛 Troubleshooting

### Error de conexión a BD
- Verificar que MySQL esté corriendo en XAMPP
- Revisar credenciales en `config/database.php`

### Error 404 en API
- Verificar que `.htaccess` esté habilitado
- Asegurar que `mod_rewrite` está activo en Apache

### Sesión no persiste
- Verificar que `session_start()` está al inicio de cada página
- Revisar permisos de carpeta `tmp` en XAMPP

## 📚 Recursos

- [PHP Manual](https://www.php.net/manual/es/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [HTML5 Guide](https://developer.mozilla.org/es/docs/Web/HTML)
- [CSS3 Guide](https://developer.mozilla.org/es/docs/Web/CSS)
- [JavaScript ES6+](https://developer.mozilla.org/es/docs/Web/JavaScript)

## 📄 Licencia

Este proyecto es de código abierto y puede ser utilizado libremente.

---

**Autor**: Tu Nombre  
**Fecha**: 2026  
**Versión**: 1.0.0
