-- ============================================
-- ELIMINAR Y CREAR BASE DE DATOS
-- ============================================
DROP DATABASE IF EXISTS libreria_virtual;
CREATE DATABASE libreria_virtual CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE libreria_virtual;

SET NAMES utf8mb4;

-- ============================================
-- TABLA USUARIO
-- ============================================
CREATE TABLE Usuario (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  apellido1 VARCHAR(50) NOT NULL,
  apellido2 VARCHAR(50),
  dni VARCHAR(15) UNIQUE NOT NULL,
  direccion VARCHAR(100),
  fecha_nacimiento DATE,
  email VARCHAR(100) UNIQUE NOT NULL,
  usuario VARCHAR(50) UNIQUE NOT NULL,
  clave VARCHAR(255) NOT NULL
);

-- ============================================
-- TABLA AUTOR
-- ============================================
CREATE TABLE Autor (
  id_autor INT AUTO_INCREMENT PRIMARY KEY,
  nombre_autor VARCHAR(100) NOT NULL
);

-- ============================================
-- TABLA CATEGORIA
-- ============================================
CREATE TABLE Categoria (
  id_categoria INT AUTO_INCREMENT PRIMARY KEY,
  nombre_categoria VARCHAR(100) NOT NULL
);

-- ============================================
-- TABLA EDITORIAL
-- ============================================
CREATE TABLE Editorial (
  id_editorial INT AUTO_INCREMENT PRIMARY KEY,
  nombre_editorial VARCHAR(100) NOT NULL
);

-- ============================================
-- TABLA LIBRO
-- ============================================
CREATE TABLE Libro (
  isbn VARCHAR(13) PRIMARY KEY,
  titulo VARCHAR(200) NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  id_autor INT NOT NULL,
  id_categoria INT NOT NULL,
  id_editorial INT NOT NULL,

  FOREIGN KEY (id_autor) REFERENCES Autor(id_autor)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,

  FOREIGN KEY (id_categoria) REFERENCES Categoria(id_categoria)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,

  FOREIGN KEY (id_editorial) REFERENCES Editorial(id_editorial)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
);

-- ============================================
-- TABLA PEDIDO
-- ============================================
CREATE TABLE Pedido (
  id_pedido INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  fecha_pedido DATE NOT NULL,
  estado VARCHAR(20) NOT NULL,

  FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
);

-- ============================================
-- TABLA DETALLE PEDIDO
-- ============================================
CREATE TABLE DetallePedido (
  id_detalle INT AUTO_INCREMENT PRIMARY KEY,
  id_pedido INT NOT NULL,
  isbn VARCHAR(13) NOT NULL,
  cantidad INT NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,

  FOREIGN KEY (id_pedido) REFERENCES Pedido(id_pedido)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

  FOREIGN KEY (isbn) REFERENCES Libro(isbn)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
);

-- ============================================
-- DATOS DE PRUEBA
-- ============================================

INSERT INTO Usuario (nombre, apellido1, apellido2, dni, direccion, fecha_nacimiento, email, usuario, clave)
VALUES
('Juan', 'Pérez', 'García', '12345678A', 'Calle Mayor 1, Madrid', '1990-05-12', 'juan@example.com', 'juanp', 'clave123'),
('María', 'López', 'Martín', '23456789B', 'Av. Andalucía 45, Sevilla', '1985-09-20', 'maria@example.com', 'marial', 'clave456'),
('Carlos', 'Sánchez', 'Ruiz', '34567890C', 'Gran Vía 100, Bilbao', '1992-01-15', 'carlos@example.com', 'carloss', 'clave789'),
('Ana', 'Fernández', 'Torres', '45678901D', 'Calle Valencia 23, Barcelona', '1995-07-30', 'ana@example.com', 'anaft', 'clave321'),
('Luis', 'Gómez', 'Hernández', '56789012E', 'Av. Galicia 12, Vigo', '1988-03-10', 'luis@example.com', 'luisgh', 'clave654');

INSERT INTO Autor (nombre_autor)
VALUES
('Gabriel García Márquez'),
('J.K. Rowling'),
('George Orwell'),
('Miguel de Cervantes'),
('Isabel Allende');

INSERT INTO Categoria (nombre_categoria)
VALUES
('Novela'),
('Fantasía'),
('Ciencia Ficción'),
('Clásico'),
('Ensayo');

INSERT INTO Editorial (nombre_editorial)
VALUES
('Planeta'),
('Penguin Random House'),
('Anagrama'),
('Alfaguara'),
('Tusquets');

INSERT INTO Libro (isbn, titulo, precio_unitario, stock, id_autor, id_categoria, id_editorial)
VALUES
('9780307474728', 'Cien años de soledad', 19.95, 10, 1, 1, 4),
('9780747532743', 'Harry Potter y la piedra filosofal', 15.50, 20, 2, 2, 2),
('9780451524935', '1984', 12.99, 15, 3, 3, 2),
('9788491050295', 'Don Quijote de la Mancha', 25.00, 8, 4, 4, 1),
('9789505115755', 'La casa de los espíritus', 18.75, 12, 5, 1, 5),
('9788497592208', 'Harry Potter y la cámara secreta', 16.00, 18, 2, 2, 2),
('9788497592215', 'Harry Potter y el prisionero de Azkaban', 16.50, 15, 2, 2, 2),
('9788497592222', 'Harry Potter y el cáliz de fuego', 17.00, 10, 2, 2, 2),
('9788497592239', 'Harry Potter y la orden del fénix', 18.00, 12, 2, 2, 2),
('9788497592246', 'Harry Potter y el misterio del príncipe', 18.50, 10, 2, 2, 2);

INSERT INTO Pedido (id_usuario, fecha_pedido, estado)
VALUES
(1, '2025-12-01', 'Pendiente'),
(2, '2025-12-02', 'Procesando'),
(3, '2025-12-03', 'Enviado'),
(4, '2025-12-04', 'Entregado'),
(5, '2025-12-05', 'Cancelado');

INSERT INTO DetallePedido (id_pedido, isbn, cantidad, precio_unitario, subtotal)
VALUES
(1, '9780307474728', 2, 19.95, 39.90),
(1, '9780747532743', 1, 15.50, 15.50),
(2, '9780451524935', 3, 12.99, 38.97),
(2, '9788491050295', 1, 25.00, 25.00),
(3, '9789505115755', 2, 18.75, 37.50),
(3, '9788497592208', 1, 16.00, 16.00),
(3, '9788497592215', 1, 16.50, 16.50),
(4, '9788497592222', 2, 17.00, 34.00),
(4, '9788497592239', 1, 18.00, 18.00),
(4, '9788497592246', 1, 18.50, 18.50),
(5, '9780747532743', 2, 15.50, 31.00),
(5, '9788497592215', 1, 16.50, 16.50),
(5, '9788497592239', 1, 18.00, 18.00),
(5, '9780307474728', 1, 19.95, 19.95),
(5, '9788491050295', 1, 25.00, 25.00);

