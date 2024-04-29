CREATE DATABASE eventor;

USE DATABASE eventor;

CREATE TABLE rol(
	id TINYINT AUTO_INCREMENT,
    rol VARCHAR(30) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (rol)
);

CREATE TABLE administrador(
	id TINYINT AUTO_INCREMENT,
    id_rol TINYINT NOT NULL,
    nombre VARCHAR(30) NOT NULL,
    correo VARCHAR(40) NOT NULL,
    clave VARCHAR(30) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (id_rol) REFERENCES rol(id),
    UNIQUE (correo)
);

CREATE TABLE provincia(
	id TINYINT AUTO_INCREMENT,
    nombre VARCHAR(10) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (nombre)
);

CREATE TABLE canton(
	id TINYINT AUTO_INCREMENT,
    id_provincia TINYINT NOT NULL,
    nombre VARCHAR(20) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (id_provincia) REFERENCES provincia(id),
    UNIQUE (nombre)
);

CREATE TABLE cliente(
	id INT AUTO_INCREMENT,
    id_canton TINYINT NOT NULL,
    nombreEmpresa VARCHAR(30) NOT NULL,
    detalleEmpresa VARCHAR(255) NOT NULL,
    nombre VARCHAR(30) NOT NULL,
    telefono VARCHAR(8) NOT NULL,
    correo VARCHAR(40) NOT NULL,
    clave VARCHAR(30) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (id_canton) REFERENCES canton(id),
    UNIQUE (correo)
);

CREATE TABLE estado_evento(
	id TINYINT AUTO_INCREMENT,
    estado VARCHAR(10) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (estado)
);

CREATE TABLE modalidad(
	id TINYINT AUTO_INCREMENT,
    modalidad VARCHAR(10) NOT NULL,
    precio DECIMAL(7,2) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (modalidad)
);

CREATE TABLE tipo_evento(
	id TINYINT AUTO_INCREMENT,
    tipo_evento VARCHAR(25) NOT NULL,
    icono VARCHAR(50) DEFAULT NULL,
    precio DECIMAL(7,2) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (tipo_evento)
);

CREATE TABLE servicio(
	id TINYINT AUTO_INCREMENT,
    servicio VARCHAR(15) NOT NULL,
    icono VARCHAR(50) DEFAULT NULL,
    precio DECIMAL(7,2) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (servicio)
);

CREATE TABLE evento(
	id INT AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    id_modalidad TINYINT NOT NULL,
    id_canton TINYINT NOT NULL,
    id_tipo_evento TINYINT NOT NULL,
    id_estado TINYINT NOT NULL,
    nombre VARCHAR(40) NOT NULL,
    fecha_hora DATETIME NOT NULL,
    detalles VARCHAR(255) NOT NULL,
    duracion TINYINT NOT NULL,
    cupos INT NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    precio_total DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (id_cliente) REFERENCES cliente(id),
    FOREIGN KEY (id_modalidad) REFERENCES modalidad(id),
    FOREIGN KEY (id_canton) REFERENCES canton(id),
    FOREIGN KEY (id_tipo_evento) REFERENCES tipo_evento(id),
    FOREIGN KEY (id_estado) REFERENCES estado_evento(id),
    UNIQUE (nombre)
);

CREATE TABLE servicios_x_evento(
    id_evento INT,
    id_servicio TINYINT,
    FOREIGN KEY (id_evento) REFERENCES evento(id),
    FOREIGN KEY (id_servicio) REFERENCES servicio(id)
);