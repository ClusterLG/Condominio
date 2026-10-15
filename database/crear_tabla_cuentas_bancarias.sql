-- ========================================================
-- TABLA DEDICADA: cuentas_bancarias EN SUPABASE
-- Ejecutar en Supabase -> SQL Editor -> New Query -> Run
-- Garantiza persistencia total multi-cuenta e inmune a pérdidas de datos
-- ========================================================

-- 1. CREAR LA TABLA DE CUENTAS BANCARIAS
CREATE TABLE IF NOT EXISTS public.cuentas_bancarias (
    id VARCHAR(100) PRIMARY KEY,
    condominio_id BIGINT NOT NULL REFERENCES public.condominios(id) ON DELETE CASCADE,
    tipo VARCHAR(50) NOT NULL, -- 'pago_movil', 'transferencia', 'zelle', 'internacional', 'binance'
    banco_codigo VARCHAR(20),
    banco_nombre VARCHAR(150),
    tipo_cuenta VARCHAR(50),
    numero_cuenta VARCHAR(50),
    prefijo VARCHAR(10),
    telefono VARCHAR(30),
    doc_tipo VARCHAR(10),
    doc_numero VARCHAR(50),
    titular VARCHAR(150),
    identificador VARCHAR(150),
    nota TEXT,
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 2. POLÍTICA DE SEGURIDAD (RLS) Y PERMISOS DE ACCESO PÚBLICO
ALTER TABLE public.cuentas_bancarias ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "Public full access cuentas_bancarias" ON public.cuentas_bancarias;
CREATE POLICY "Public full access cuentas_bancarias" ON public.cuentas_bancarias FOR ALL TO public USING (true) WITH CHECK (true);

GRANT ALL ON TABLE public.cuentas_bancarias TO anon, authenticated;

-- 3. DATOS INICIALES PARA LOS CONDOMINIOS PILOTO

-- PILOTO 1: Residencias Los Samanes (ID: 1)
DELETE FROM public.cuentas_bancarias WHERE condominio_id = 1;
INSERT INTO public.cuentas_bancarias 
(id, condominio_id, tipo, banco_codigo, banco_nombre, tipo_cuenta, numero_cuenta, prefijo, telefono, doc_tipo, doc_numero, titular, identificador, nota, activo)
VALUES
('acc_samanes_pm', 1, 'pago_movil', '0134', 'Banesco Banco Universal', NULL, NULL, '0414', '1234567', 'J', '12345678-0', 'Junta de Condominio Residencias Los Samanes', NULL, 'Reportar referencia exacta de 6 a 8 dígitos', true),
('acc_samanes_tr', 1, 'transferencia', '0134', 'Banesco Banco Universal', 'Cuenta Corriente', '01340000000000000000', NULL, NULL, 'J', '12345678-0', 'Junta de Condominio Residencias Los Samanes', NULL, 'Mismo banco se acredita de inmediato', true),
('acc_samanes_zl', 1, 'zelle', 'ZELLE', 'Zelle (EE.UU. USD)', NULL, NULL, NULL, NULL, NULL, NULL, 'Junta de Condominio', 'condominio.samanes@gmail.com', 'Indicar número de apartamento en el concepto / memo', true);

-- PILOTO 2: Residencia Chichi Suggar (ID: 2)
DELETE FROM public.cuentas_bancarias WHERE condominio_id = 2;
INSERT INTO public.cuentas_bancarias 
(id, condominio_id, tipo, banco_codigo, banco_nombre, tipo_cuenta, numero_cuenta, prefijo, telefono, doc_tipo, doc_numero, titular, identificador, nota, activo)
VALUES
('acc_chichi_pm', 2, 'pago_movil', '0105', 'Banco Mercantil', NULL, NULL, '0414', '9988776', 'J', '4658975-1', 'Condominio Chichi Suggar', NULL, 'Pago móvil verificado automáticamente', true),
('acc_chichi_tr', 2, 'transferencia', '0105', 'Banco Mercantil', 'Cuenta Corriente', '01051234567890123456', NULL, NULL, 'J', '4658975-1', 'Condominio Chichi Suggar', NULL, 'Cuenta corriente jurídica Mercantil', true),
('acc_chichi_zl', 2, 'zelle', 'ZELLE', 'Zelle (EE.UU. USD)', NULL, NULL, NULL, NULL, NULL, NULL, 'Condominio Chichi Suggar', 'chichi.suggar.condo@gmail.com', 'Colocar número de apartamento en el memo de Zelle', true);
