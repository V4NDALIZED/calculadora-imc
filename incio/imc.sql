-- Base de datos para Calculadora IMC
DROP DATABASE IF EXISTS imc;
CREATE DATABASE IF NOT EXISTS imc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE imc;

-- Tabla de Tipos de Documento
CREATE TABLE TipoDocumento (
    id_tipo_documento INT PRIMARY KEY AUTO_INCREMENT,
    nombre_documento VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS registros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    edad INT NOT NULL,
    peso DECIMAL(5,2) NOT NULL,
    altura DECIMAL(5,2) NOT NULL,
    imc DECIMAL(5,2) NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Carreras Específicas
CREATE TABLE Carreras (
    id_carrera INT PRIMARY KEY AUTO_INCREMENT,
    nombre_carrera VARCHAR(100) NOT NULL,
    codigo_carrera VARCHAR(10) UNIQUE
);

-- Tabla de Usuarios
CREATE TABLE Usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    id_tipo_documento INT,
    numero_documento VARCHAR(20) UNIQUE NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    genero ENUM('M', 'F', 'Otro') NOT NULL,
    correo_electronico VARCHAR(100) UNIQUE,
    id_carrera INT,
    tipo_usuario ENUM('Estudiante', 'Personal', 'Administrativo') NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    salt VARCHAR(50) NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tipo_documento) REFERENCES TipoDocumento(id_tipo_documento),
    FOREIGN KEY (id_carrera) REFERENCES Carreras(id_carrera)
);

-- Tabla de Registros IMC
CREATE TABLE Registros_IMC (
    id_registro INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    fecha_medicion DATETIME DEFAULT CURRENT_TIMESTAMP,
    peso DECIMAL(5,2) NOT NULL,
    altura DECIMAL(5,2) NOT NULL,
    imc DECIMAL(5,2) GENERATED ALWAYS AS (peso / (altura * altura)) STORED,
    categoria_imc VARCHAR(50) GENERATED ALWAYS AS (
        CASE 
            WHEN (peso / (altura * altura)) < 18.5 THEN 'Bajo peso'
            WHEN (peso / (altura * altura)) >= 18.5 AND (peso / (altura * altura)) < 25 THEN 'Peso normal'
            WHEN (peso / (altura * altura)) >= 25 AND (peso / (altura * altura)) < 30 THEN 'Sobrepeso'
            ELSE 'Obesidad'
        END
    ) STORED,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario)
);

-- Vista de Resumen IMC por Carrera
CREATE VIEW ResumenIMCCarrera AS
SELECT 
    c.nombre_carrera,
    COUNT(DISTINCT u.id_usuario) as total_usuarios,
    ROUND(AVG(r.imc), 2) as promedio_imc,
    COUNT(CASE WHEN r.categoria_imc = 'Bajo peso' THEN 1 END) as cantidad_bajo_peso,
    COUNT(CASE WHEN r.categoria_imc = 'Peso normal' THEN 1 END) as cantidad_peso_normal,
    COUNT(CASE WHEN r.categoria_imc = 'Sobrepeso' THEN 1 END) as cantidad_sobrepeso,
    COUNT(CASE WHEN r.categoria_imc = 'Obesidad' THEN 1 END) as cantidad_obesidad
FROM 
    Carreras c
    LEFT JOIN Usuarios u ON c.id_carrera = u.id_carrera
    LEFT JOIN Registros_IMC r ON u.id_usuario = r.id_usuario
GROUP BY 
    c.id_carrera, c.nombre_carrera;

-- Tabla de Recomendaciones IMC
CREATE TABLE RecomendacionesIMC (
    id_recomendacion INT PRIMARY KEY AUTO_INCREMENT,
    categoria_imc VARCHAR(50) NOT NULL,
    descripcion TEXT NOT NULL,
    recomendacion_dieta TEXT,
    recomendacion_ejercicio TEXT
);

-- Insertar Carreras
INSERT INTO Carreras (nombre_carrera, codigo_carrera) VALUES 
('Ingeniería en Sistemas', 'SIS'),
('Ingeniería Industrial', 'IND'),
('Psicología', 'PSI'),
('Administración', 'ADM');

-- Insertar Tipos de Documento
INSERT INTO TipoDocumento (nombre_documento) VALUES 
('Cédula de Ciudadanía'),
('Tarjeta de Identidad'),
('Pasaporte');

-- Insertar Recomendaciones IMC
INSERT INTO RecomendacionesIMC (categoria_imc, descripcion, recomendacion_dieta, recomendacion_ejercicio) VALUES 
('Bajo peso', 'Peso por debajo del rango saludable', 
'Aumentar ingesta calórica con alimentos nutritivos', 
'Entrenamiento con pesas, proteínas'),

('Peso normal', 'Peso dentro del rango saludable', 
'Dieta balanceada y variada', 
'Ejercicio regular, 30 min diarios'),

('Sobrepeso', 'Peso por encima del rango saludable', 
'Reducir carbohidratos, aumentar proteínas', 
'Cardio, entrenamiento de alta intensidad'),

('Obesidad', 'Peso significativamente alto', 
'Dieta hipocalórica, control de porciones', 
'Ejercicio diario, rutinas de pérdida de peso');