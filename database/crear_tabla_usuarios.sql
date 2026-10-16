-- ========================================================
-- TABLA DEDICADA: usuarios EN SUPABASE CON ESTRUCTURA INSTITUCIONAL
-- Ejecutar en Supabase -> SQL Editor -> New Query -> Run
-- Formato Solicitado:
--   - Residente Samanes PB-A:       aptopba@resdlossamanes.com
--   - Residente Chichi Suggar 8-A:  apto8a@resdchichisuggar.com
--   - Admin Los Samanes:            admin@resdlossamanes.com
--   - Admin Chichi Suggar:          admin@resdchichisuggar.com
-- ========================================================

-- 1. CREAR LA TABLA DE USUARIOS
CREATE TABLE IF NOT EXISTS public.usuarios (
    id BIGSERIAL PRIMARY KEY,
    condominio_id BIGINT REFERENCES public.condominios(id) ON DELETE CASCADE,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL DEFAULT '123456',
    nombre_completo VARCHAR(150),
    cedula VARCHAR(50),
    telefono VARCHAR(50),
    telefono_secundario VARCHAR(50),
    cargo VARCHAR(100) DEFAULT 'Administrador de Condominio',
    rol VARCHAR(50) NOT NULL DEFAULT 'residente', -- 'superadmin', 'admin', 'junta', 'residente'
    apto_codigo VARCHAR(20),
    activo BOOLEAN DEFAULT true,
    ultimo_acceso TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 2. POLÍTICA DE SEGURIDAD (RLS) Y PERMISOS DE ACCESO
ALTER TABLE public.usuarios ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "Public full access usuarios" ON public.usuarios;
CREATE POLICY "Public full access usuarios" ON public.usuarios FOR ALL TO public USING (true) WITH CHECK (true);

GRANT ALL ON TABLE public.usuarios TO anon, authenticated;
GRANT ALL ON SEQUENCE public.usuarios_id_seq TO anon, authenticated;

-- 3. INSERTAR USUARIOS ADMINISTRATIVOS
-- 3.1 Superadministrador Global
INSERT INTO public.usuarios (condominio_id, username, email, password, nombre_completo, cedula, telefono, rol, apto_codigo, activo)
VALUES (NULL, 'clusterlg', 'superadmin@condohub.com', 'glretsulc', 'Superadministrador Global ClusterLG', 'V-00.000.000', '0414-1234567', 'superadmin', NULL, true)
ON CONFLICT (username) DO UPDATE 
SET password = EXCLUDED.password, nombre_completo = EXCLUDED.nombre_completo, activo = true;

-- 3.2 Administradores Oficiales de CADA Condominio Registrado (1 Admin por cada Condominio)
INSERT INTO public.usuarios (condominio_id, username, email, password, nombre_completo, cedula, telefono, rol, apto_codigo, activo)
SELECT 
    c.id AS condominio_id,
    LOWER(CONCAT('admin@', CASE 
        WHEN c.id = 1 THEN 'resdlossamanes.com'
        WHEN c.id = 2 THEN 'resdchichisuggar.com'
        ELSE CONCAT('resd', LOWER(REPLACE(REPLACE(c.slug, '-', ''), ' ', '')), '.com')
    END)) AS username,
    LOWER(CONCAT('admin@', CASE 
        WHEN c.id = 1 THEN 'resdlossamanes.com'
        WHEN c.id = 2 THEN 'resdchichisuggar.com'
        ELSE CONCAT('resd', LOWER(REPLACE(REPLACE(c.slug, '-', ''), ' ', '')), '.com')
    END)) AS email,
    'admin123' AS password, -- Clave por defecto para el administrador: admin123
    CONCAT('Administrador ', c.nombre) AS nombre_completo,
    COALESCE(c.rif, 'J-00000000-0') AS cedula,
    '0414-1234567' AS telefono,
    'admin' AS rol,
    NULL AS apto_codigo,
    true AS activo
FROM public.condominios c
ON CONFLICT (username) DO UPDATE 
SET 
    password = EXCLUDED.password,
    nombre_completo = EXCLUDED.nombre_completo,
    cedula = EXCLUDED.cedula,
    condominio_id = EXCLUDED.condominio_id,
    activo = true;

-- 4. POBLAR AUTOMÁTICAMENTE TODOS LOS APARTAMENTOS (1 Usuario por cada Apartamento):
-- Ejemplos generados:
--   Samanes PB-A -> aptopba@resdlossamanes.com
--   Samanes 1B   -> apto1b@resdlossamanes.com
--   Chichi 8-A   -> apto8a@resdchichisuggar.com
--   Chichi PB-C  -> aptopbc@resdchichisuggar.com
INSERT INTO public.usuarios (condominio_id, username, email, password, nombre_completo, cedula, telefono, rol, apto_codigo, activo)
SELECT 
    a.condominio_id,
    -- Generar username idéntico al email institucional
    LOWER(CONCAT(
        'apto',
        REPLACE(REPLACE(a.codigo, '-', ''), ' ', ''),
        '@',
        CASE 
            WHEN a.condominio_id = 1 THEN 'resdlossamanes.com'
            WHEN a.condominio_id = 2 THEN 'resdchichisuggar.com'
            ELSE CONCAT('resd', LOWER(REPLACE(REPLACE(c.slug, '-', ''), ' ', '')), '.com')
        END
    )) AS username,
    -- Email institucional
    LOWER(CONCAT(
        'apto',
        REPLACE(REPLACE(a.codigo, '-', ''), ' ', ''),
        '@',
        CASE 
            WHEN a.condominio_id = 1 THEN 'resdlossamanes.com'
            WHEN a.condominio_id = 2 THEN 'resdchichisuggar.com'
            ELSE CONCAT('resd', LOWER(REPLACE(REPLACE(c.slug, '-', ''), ' ', '')), '.com')
        END
    )) AS email,
    '123456' AS password, -- Clave por defecto inicial para residentes: 123456
    COALESCE(a.propietario, CONCAT('Copropietario Apto ', a.codigo)),
    a.cedula,
    a.telefono,
    'residente' AS rol,
    a.codigo AS apto_codigo,
    true AS activo
FROM public.apartamentos a
JOIN public.condominios c ON c.id = a.condominio_id
ON CONFLICT (username) DO UPDATE 
SET 
    password = EXCLUDED.password,
    nombre_completo = EXCLUDED.nombre_completo,
    cedula = EXCLUDED.cedula,
    telefono = EXCLUDED.telefono,
    email = EXCLUDED.email,
    condominio_id = EXCLUDED.condominio_id,
    apto_codigo = EXCLUDED.apto_codigo,
    activo = true;

-- 5. ASEGURAR QUE TODOS LOS USUARIOS RESIDENCIALES TENGAN LA CLAVE 123456 Y LOS ADMINS admin123
UPDATE public.usuarios SET password = '123456' WHERE rol = 'residente';
UPDATE public.usuarios SET password = 'admin123' WHERE rol = 'admin';
UPDATE public.usuarios SET password = 'glretsulc' WHERE rol = 'superadmin';
