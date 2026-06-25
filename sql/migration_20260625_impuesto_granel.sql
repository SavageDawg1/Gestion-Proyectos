-- Migracion: impuesto global + productos por ganancia/granel
-- Ejecutar en la base de datos proyecto_almacen

CREATE TABLE IF NOT EXISTS configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor VARCHAR(255) NOT NULL,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO configuracion (clave, valor) VALUES ('impuesto_porcentaje', '19');

ALTER TABLE productos
    ADD COLUMN IF NOT EXISTS costo DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER descripcion,
    ADD COLUMN IF NOT EXISTS porcentaje_ganancia DECIMAL(5,2) NOT NULL DEFAULT 30.00 AFTER costo,
    ADD COLUMN IF NOT EXISTS tipo_venta ENUM('unidad','granel') NOT NULL DEFAULT 'unidad' AFTER precio,
    ADD COLUMN IF NOT EXISTS unidad_granel ENUM('250g','500g','1000g') NOT NULL DEFAULT '1000g' AFTER tipo_venta;

UPDATE productos
SET costo = precio
WHERE costo = 0;
