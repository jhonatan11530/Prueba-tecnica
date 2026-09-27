-- =========================================================================
-- SCRIPT DE INICIALIZACIÓN DE LA BASE DE DATOS (TRAVEL PLANNER)
-- Motor: PostgreSQL
-- =========================================================================

-- IMPORTANTE: Ejecutar este script en una base de datos vacía.

-- 1. ESTRUCTURA DE TABLAS (DDL)

CREATE TABLE monedas (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    simbolo VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE paises (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    codigo VARCHAR(10) NOT NULL,
    moneda_id INTEGER REFERENCES monedas(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ciudades (
    id SERIAL PRIMARY KEY,
    pais_id INTEGER REFERENCES paises(id) ON DELETE CASCADE,
    nombre VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    correo VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    idioma VARCHAR(10) DEFAULT 'es',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE consultas (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuarios(id) ON DELETE CASCADE,
    ciudad_id INTEGER REFERENCES ciudades(id) ON DELETE CASCADE,
    presupuesto_cop NUMERIC(15,2) NOT NULL,
    clima NUMERIC(5,2),
    tasa NUMERIC(15,4),
    valor_convertido NUMERIC(15,2),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tasas_cambio (
    id SERIAL PRIMARY KEY,
    moneda_origen VARCHAR(10) NOT NULL,
    moneda_destino VARCHAR(10) NOT NULL,
    tasa NUMERIC(15,4) NOT NULL,
    fecha_consulta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tokens_revocados (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuarios(id) ON DELETE CASCADE,
    jti VARCHAR(255) UNIQUE NOT NULL,
    refresh_token_hash VARCHAR(255) NOT NULL,
    revocado BOOLEAN DEFAULT FALSE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================================================================
-- 2. INSERCIÓN DE DATOS INICIALES (DML)
-- =========================================================================

-- Monedas Base
INSERT INTO monedas (id, codigo, nombre, simbolo) VALUES
(1, 'GBP', 'Libra esterlina', '£'),
(2, 'JPY', 'Yen', '¥'),
(3, 'INR', 'Rupia india', '₹'),
(4, 'DKK', 'Corona danesa', 'kr');

-- Países Disponibles
INSERT INTO paises (id, nombre, codigo, moneda_id) VALUES
(1, 'Inglaterra', 'GB', 1),
(2, 'Japón', 'JP', 2),
(3, 'India', 'IN', 3),
(4, 'Dinamarca', 'DK', 4);

-- Ciudades Turísticas
INSERT INTO ciudades (id, pais_id, nombre) VALUES
(1, 1, 'Londres'),
(2, 1, 'Mánchester'),
(3, 2, 'Tokio'),
(4, 2, 'Osaka'),
(5, 3, 'Nueva Delhi'),
(6, 3, 'Bombay'),
(7, 4, 'Copenhague'),
(8, 4, 'Aarhus');

-- Ajustar las secuencias automáticas de Postgres porque forzamos los IDs
SELECT setval('monedas_id_seq', (SELECT MAX(id) FROM monedas));
SELECT setval('paises_id_seq', (SELECT MAX(id) FROM paises));
SELECT setval('ciudades_id_seq', (SELECT MAX(id) FROM ciudades));


-- =========================================================================
-- 3. USUARIO DE PRUEBA EXIGIDO
-- Credenciales de acceso:
-- Correo: marlon@ejemplo.com
-- Contraseña: Marlon123
-- =========================================================================

INSERT INTO usuarios (nombre, correo, password_hash, idioma) VALUES
('Marlon Torino', 'marlon@ejemplo.com', '$2y$10$7Mqmr9XgskoqOsgzo/.KnOSnMHHPicF/UdTKPlpTPlneUwfOlqW8y', 'es');
