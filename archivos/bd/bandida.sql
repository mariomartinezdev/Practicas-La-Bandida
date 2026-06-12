



-- he quitado los 3 primeros comandos para probarlo en elhost gratuito

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

-- Tabla de platos
CREATE TABLE platos(
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    precio DECIMAL(6,2) NOT NULL,
    categoria_id INT,
    imagen varchar(120) default null,
	disponible tinyint(1) default 1,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);

-- Relación platos- Ingredientes
CREATE TABLE plato_ingredientes (
    plato_id INT,
    ingrediente_id INT,
    PRIMARY KEY (plato_id, ingrediente_id),
    FOREIGN KEY (plato_id) REFERENCES platos(id) ON DELETE CASCADE,
    FOREIGN KEY (ingrediente_id) REFERENCES ingredientes(id) ON DELETE CASCADE
);

-- Relación platos- Alérgenos
CREATE TABLE plato_alergenos (
    plato_id INT,
    alergeno_id INT,
    PRIMARY KEY (plato_id, alergeno_id),
    FOREIGN KEY (plato_id) REFERENCES platos(id) ON DELETE CASCADE,
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
INSERT INTO categorias (nombre) VALUES ('Burgers'), ('Para Compartir'), ('Postres'), ('Agregados')
ON DUPLICATE KEY UPDATE nombre=nombre;

INSERT INTO ingredientes (nombre) VALUES 
('Bacon'),
('Berenjena'),
('Carne de vaca madurada 21 dias'),
('Carne de vaca madurada 45 dias'),
('Carrillera'),
('Cebolla a la plancha'),
('Cebolla caramelizada'),
('Cebolla morada'),
('Cheddar'),
('Cheddar ahumado'),
('Contramuslo de pollo crujiente'),
('Crema suave de sobrasada'),
('Guanciale italiano'),
('Hamburguesa vegetal Beyond'),
('Lechuga'),
('Mantequilla tostada'),
('Mayo kimichi'),
('Mayonesa'),
('Mayonesa trufada'),
('Mermelada de pimientos del piquillo asados'),
('Miel'),
('Pepinillos dulces'),
('Pimientos asados'),
('Queso cheddar'),
('Queso de cabra'),
('Queso edam'),
('Queso gouda'),
('Queso mahon'),
('Queso Monterrey Jack'),
('Queso vegano'),
('Salsa cheddar'),
('Tomate'),
('Guanciale italiano') 
ON DUPLICATE KEY UPDATE nombre=nombre;

INSERT INTO alergenos (nombre) VALUES ('Gluten'), ('Lácteos'), ('Huevo'), ('Mostaza'), ('Sésamo')
ON DUPLICATE KEY UPDATE nombre=nombre;

INSERT INTO platos (nombre, precio, categoria_id,imagen) VALUES 
('La Bandida', 12.90, 1,"Labandida.jpg"),
('La trufada', 13.90, 1,"la_trufada.jpg"),
('La cabramelizada', 13.00, 1,"cabramelizada.jpg"),
('Bandida Crispy', 11.00, 1,"la_crispy.jpg"),
('La clasica', 13.90, 1,"la_clasica.jpg"),
('La mallorquina', 13.90, 1,"la_mallorquina.jpg"),
('La veggie', 13.90, 1,"veggie.jpg"),
('Croquetas de jamon iberico', 1.50, 2,"croquetas_jiberico.jpg"),
('Croquetas de carrillera', 1.50, 2,"placeHolderEntrantes.png"),
('Croqueta de cecina', 1.50, 2,"placeHolderEntrantes.png"),
('Alitas de pollo crujientes con salsa bourbon', 9.00, 2,"Alitas.jpg"),
('Taco de oreja de cerdo con mayonesa', 3.90, 2,"placeHolderEntrantes.png"),
('Patatas Bacon Cheese', 9.00, 2,"patatas_bacon.jpg"),
('Torreznos', 8.90, 2,"torreznos.jpg"),
('Nachos Bandidos', 11.90, 2,"placeHolderEntrantes.png"),
('Costillar de cerdo a baja temperatura con salsa bbq', 15.90, 2,"placeHolderEntrantes.png"),
('Torrija de brioche caramelizada con helado', 5.90, 3,"la_torrija.jpg"),
('Tarta de queso tradicional al horno', 5.50, 3,"tarta_queso.jpg"),
('Tarta de queso de Lotus', 6.50, 3,"postrePlace.png"), 
('Patatas fritas',1,4,"patatas_fritas.jpg"),
('Sweet potatos',2,4,"sweet_potatoes.jpg")
ON DUPLICATE KEY UPDATE nombre=nombre;

INSERT INTO plato_ingredientes (plato_id, ingrediente_id) VALUES 
(1, 4),
(5, 4),
(1, 24),
(4, 11),
(5, 15),
(7, 14),
(4, 9),
(5, 4),
(7, 2),
(2, 3),
(3, 3),
(6, 3),
(1, 27),
(1, 17),
(1, 13),
(2, 5),
(3, 25),
(6, 12),
(2, 19),
(3, 20),
(4, 32),
(4, 15),
(4, 31),
(5, 32),
(4, 18),
(5, 27),
(5, 26),
(6, 7),
(6, 28),
(6, 21),
(2, 10),
(3, 16),
(3, 29),
(3, 7),
(7, 8),
(7, 23),
(7, 15),
(7, 30),
(5, 6)
ON DUPLICATE KEY UPDATE plato_id=plato_id;

INSERT INTO plato_alergenos (plato_id, alergeno_id) 
VALUES (1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(1, 2),
(2, 2),
(3, 2),
(5, 2),
(6, 2)
ON DUPLICATE KEY UPDATE plato_id=plato_id;

-- Usuario de prueba (Usuario: 101010 / Contraseña: 123456 privilegios de adm) 

INSERT INTO usuarios (idUsuario,nombre,password,grado) 
VALUES (101010, 'admin', '$2y$10$4URnPE1nuKXjE.X4H8weL.QIfqu5YyPZXnTU0boWRtExBTdrbCS9G',1);
