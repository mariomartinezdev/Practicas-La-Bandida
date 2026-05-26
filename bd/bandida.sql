drop database if exists bandida;

create database bandida;

use bandida;

-- Tabla de Categorías
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

-- Tabla de Ingredientes
CREATE TABLE ingredientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

-- Tabla de Alérgenos
CREATE TABLE alergenos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

-- Tabla de Hamburguesas
CREATE TABLE hamburguesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    precio DECIMAL(6,2) NOT NULL,
    categoria_id INT,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);

-- Relación Hamburguesas - Ingredientes
CREATE TABLE hamburguesa_ingredientes (
    hamburguesa_id INT,
    ingrediente_id INT,
    PRIMARY KEY (hamburguesa_id, ingrediente_id),
    FOREIGN KEY (hamburguesa_id) REFERENCES hamburguesas(id) ON DELETE CASCADE,
    FOREIGN KEY (ingrediente_id) REFERENCES ingredientes(id) ON DELETE CASCADE
);

-- Relación Hamburguesas - Alérgenos
CREATE TABLE hamburguesa_alergenos (
    hamburguesa_id INT,
    alergeno_id INT,
    PRIMARY KEY (hamburguesa_id, alergeno_id),
    FOREIGN KEY (hamburguesa_id) REFERENCES hamburguesas(id) ON DELETE CASCADE,
    FOREIGN KEY (alergeno_id) REFERENCES alergenos(id) ON DELETE CASCADE
);

CREATE TABLE usuarios (

    id INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario INT(6) UNIQUE,
    
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
	grado TINYINT(1) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
	
);

-- Inserción de Datos Iniciales
INSERT INTO categorias (nombre) VALUES ('Clásicas'), ('Premium'), ('Veganas'), ('Especiales')
ON DUPLICATE KEY UPDATE nombre=nombre;

INSERT INTO ingredientes (nombre) VALUES ('Carne de Vacuno'), ('Queso Cheddar'), ('Lechuga'), ('Tomate'), ('Cebolla Caramelizada'), ('Bacon'), ('Pan Brioche'), ('Medallón de Lentejas')
ON DUPLICATE KEY UPDATE nombre=nombre;

INSERT INTO alergenos (nombre) VALUES ('Gluten'), ('Lácteos'), ('Huevo'), ('Mostaza'), ('Sésamo')
ON DUPLICATE KEY UPDATE nombre=nombre;

INSERT INTO hamburguesas (nombre, precio, categoria_id) VALUES ('Hamburguesa Cheeseburger', 8.50, 1)
ON DUPLICATE KEY UPDATE nombre=nombre;

INSERT INTO hamburguesa_ingredientes (hamburguesa_id, ingrediente_id) VALUES (1, 1), (1, 2), (1, 7)
ON DUPLICATE KEY UPDATE hamburguesa_id=hamburguesa_id;

INSERT INTO hamburguesa_alergenos (hamburguesa_id, alergeno_id) VALUES (1, 1), (1, 2)
ON DUPLICATE KEY UPDATE hamburguesa_id=hamburguesa_id;

-- Usuario de prueba (Usuario: 101010 / Contraseña: 123456 privilegios de adm) 

INSERT INTO usuarios (idUsuario,nombre,password,grado) 
VALUES (101010, 'admin', '$2y$10$4URnPE1nuKXjE.X4H8weL.QIfqu5YyPZXnTU0boWRtExBTdrbCS9G',1);
