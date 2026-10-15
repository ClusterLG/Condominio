-- ========================================================
-- TABLA DEDICADA: usuarios EN SUPABASE
-- Ejecutar en Supabase -> SQL Editor -> New Query -> Run
-- Gestión centralizada de usuarios, roles, accesos y contraseñas
-- Compatible tanto con la app directa como con Supabase Auth
-- ========================================================

-- 1. CREAR LA TABLA DE USUARIOS
CREATE TABLE IF NOT EXISTS public.usuarios (
    id BIGSERIAL PRIMARY KEY,
    condominio_id BIGINT REFERENCES public.condominios(id) ON DELETE CASCADE,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(150),
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

-- 3. INSERTAR USUARIOS ADMINISTRATIVOS PRINCIPALES
DELETE FROM public.usuarios WHERE username IN ('clusterlg', 'admin', 'admin_chichi');

INSERT INTO public.usuarios (condominio_id, username, email, password, nombre_completo, cedula, telefono, rol, apto_codigo, activo)
VALUES
-- Superadministrador Global
(NULL, 'clusterlg', 'lams210488@gmail.com', 'glretsulc', 'Superadministrador Global ClusterLG', 'V-00.000.000', '0414-1234567', 'superadmin', NULL, true),

-- Administrador Los Samanes
(1, 'admin', 'admin.samanes@gmail.com', 'admin123', 'Administrador Residencias Los Samanes', 'J-12345678-0', '0414-1234567', 'admin', NULL, true),

-- Administrador Chichi Suggar
(2, 'admin_chichi', 'chichi.suggar.condo@gmail.com', 'admin123', 'Administrador Residencia Chichi Suggar', 'J-4658975-1', '0414-9988776', 'admin', NULL, true);

-- 4. POBLAR AUTOMÁTICAMENTE RESIDENTES DESDE LA TABLA apartamentos
-- Si ya existen apartamentos registrados en Supabase con propietario asignado, se dan de alta como usuarios residentes:
INSERT INTO public.usuarios (condominio_id, username, email, password, nombre_completo, cedula, telefono, rol, apto_codigo, activo)
SELECT 
    a.condominio_id,
    LOWER(REPLACE(CONCAT('c', a.condominio_id, '_', a.codigo), '-', '')) AS username,
    COALESCE(a.email, CONCAT('apto.', LOWER(REPLACE(a.codigo, '-', '')), '@condominio.local')),
    '123456' AS password,
    COALESCE(a.propietario, CONCAT('Residente Apto ', a.codigo)),
    a.cedula,
    a.telefono,
    'residente' AS rol,
    a.codigo AS apto_codigo,
    true AS activo
FROM public.apartamentos a
WHERE a.propietario IS NOT NULL
ON CONFLICT (username) DO UPDATE 
SET 
    nombre_completo = EXCLUDED.nombre_completo,
    cedula = EXCLUDED.cedula,
    telefono = EXCLUDED.telefono,
    email = EXCLUDED.email;
