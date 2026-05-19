CREATE DATABASE IF NOT EXISTS aulas_magicas
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE aulas_magicas;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin','teacher','student') NOT NULL DEFAULT 'student',
    nivel ENUM('low','high') NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME NULL,
    INDEX idx_rol (rol)
);

INSERT INTO usuarios (nombre, apellido, email, password, rol, activo)
SELECT 'Admin', 'Sistema', 'admin@aulasmagicas.mx',
       '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios WHERE email = 'admin@aulasmagicas.mx'
);

INSERT INTO usuarios (nombre, apellido, email, password, rol, activo)
SELECT 'María', 'González', 'maestra@aulasmagicas.mx',
       '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 1
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios WHERE email = 'maestra@aulasmagicas.mx'
);

INSERT INTO usuarios (nombre, apellido, email, password, rol, nivel, activo)
SELECT 'Juanito', 'Pérez', 'alumno@aulasmagicas.mx',
       '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'low', 1
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios WHERE email = 'alumno@aulasmagicas.mx'
);

SELECT id, nombre, apellido, email, rol, nivel, activo 
FROM usuarios 
ORDER BY id;