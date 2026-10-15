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
DELETE FROM public.usuarios WHERE username IN (
    'clusterlg', 
    'admin@resdlossamanes.com', 
    'admin@resdchichisuggar.com', 
    'admin', 
    'admin_chichi'
);

INSERT INTO public.usuarios (condominio_id, username, email, password, nombre_completo, cedula, telefono, rol, apto_codigo, activo)
VALUES
-- Superadministrador Global
(NULL, 'clusterlg', 'superadmin@condohub.com', 'glretsulc', 'Superadministrador Global ClusterLG', 'V-00.000.000', '0414-1234567', 'superadmin', NULL, true),

-- Administrador Los Samanes (Email Oficial)
(1, 'admin@resdlossamanes.com', 'admin@resdlossamanes.com', 'admin123', 'Administrador Residencias Los Samanes', 'J-12345678-0', '0414-1234567', 'admin', NULL, true),

-- Administrador Chichi Suggar (Email Oficial)
(2, 'admin@resdchichisuggar.com', 'admin@resdchichisuggar.com', 'admin123', 'Administrador Residencia Chichi Suggar', 'J-4658975-1', '0414-9988776', 'admin', NULL, true);

-- 4. POBLAR AUTOMÁTICAMENTE TODOS LOS APARTAMENTOS CON EL FORMATO INSTITUCIONAL EXACTO:
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
            ELSE CONCAT('resd', LOWER(REPLACE(c.slug, '-', '')), '.com')
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
            ELSE CONCAT('resd', LOWER(REPLACE(c.slug, '-', '')), '.com')
        END
    )) AS email,
    COALESCE(NULLIF(a.cedula, ''), '123456') AS password, -- Clave por defecto inicial 123456
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
    nombre_completo = EXCLUDED.nombre_completo,
    cedula = EXCLUDED.cedula,
    telefono = EXCLUDED.telefono,
    email = EXCLUDED.email;
