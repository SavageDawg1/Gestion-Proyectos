-- 1. ELIMINAR TABLAS SI YA EXISTEN (Para evitar errores de duplicado al importar)
DROP TABLE IF EXISTS registro;
DROP TABLE IF EXISTS roles;

-- 2. CREAR TABLA DE ROLES
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. CREAR TABLA DE REGISTRO (CON TU RUT, CORREO Y ATRIBUTOS)
CREATE TABLE registro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_apellido VARCHAR(150) NOT NULL,
    rut VARCHAR(12) NOT NULL UNIQUE,       
    correo VARCHAR(100) NOT NULL UNIQUE,   
    contrasena VARCHAR(255) NOT NULL,      
    rol_id INT NOT NULL,                   
    activo BOOLEAN DEFAULT TRUE,           
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    
    -- Relación de Clave Foránea
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. CREAR EL ÍNDICE DE RENDIMIENTO PARA EL CRUCE
CREATE INDEX idx_registro_rol_id ON registro(rol_id);


-- ========================================================
-- 5. INSERCIÓN DE DATOS DE PRUEBA (OPCIONAL PERO RECOMENDADO)
-- ========================================================

-- Insertamos los roles base
INSERT INTO roles (id, nombre) VALUES 
(1, 'Administrador'),
(2, 'Usuario');

-- Insertamos un usuario de prueba amarrado al rol de Administrador (rol_id = 1)
-- Nota: La contraseña está en texto plano para el ejemplo, recuerda encriptarla en tu app.
INSERT INTO registro (nombre_apellido, rut, correo, contrasena, rol_id) VALUES 
('Usuario de Prueba', '12.345.678-9', 'prueba@correo.com', 'clave123', 1);