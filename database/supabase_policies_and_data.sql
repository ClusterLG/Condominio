-- ========================================================
-- HABILITAR POLÍTICAS DE ACCESO PÚBLICO (ANON) E INSERTAR DATOS
-- Ejecutar en Supabase -> SQL Editor -> New Query -> Run
-- ========================================================

ALTER TABLE condominios ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "Public full access" ON condominios;
CREATE POLICY "Public full access" ON condominios FOR ALL TO public USING (true) WITH CHECK (true);
ALTER TABLE apartamentos ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "Public full access" ON apartamentos;
CREATE POLICY "Public full access" ON apartamentos FOR ALL TO public USING (true) WITH CHECK (true);
ALTER TABLE cuotas ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "Public full access" ON cuotas;
CREATE POLICY "Public full access" ON cuotas FOR ALL TO public USING (true) WITH CHECK (true);
ALTER TABLE pagos ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "Public full access" ON pagos;
CREATE POLICY "Public full access" ON pagos FOR ALL TO public USING (true) WITH CHECK (true);
ALTER TABLE comunicados ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "Public full access" ON comunicados;
CREATE POLICY "Public full access" ON comunicados FOR ALL TO public USING (true) WITH CHECK (true);
ALTER TABLE tickets ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "Public full access" ON tickets;
CREATE POLICY "Public full access" ON tickets FOR ALL TO public USING (true) WITH CHECK (true);

GRANT ALL ON ALL TABLES IN SCHEMA public TO anon, authenticated;
GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO anon, authenticated;

-- DATOS INICIALES: RESIDENCIAS LOS SAMANES
DELETE FROM condominios WHERE id = 1;
INSERT INTO condominios (id, slug, nombre, rif, direccion, pisos_cant, tiene_pb, aptos_pb_cant, aptos_por_piso, tasa_dolar, banco_pago_movil, banco_transferencia, cuenta_zelle)
VALUES (1, 'los-samanes', 'Residencias Los Samanes', 'J-12345678-0', 'Av. Principal Los Samanes, Caracas', 12, 1, 2, 4, 877.90,
 'Banco Banesco (0134) | RIF: J-12345678-0 | Teléfono: 0414-1234567',
 'Banesco Cta Corriente: 0134-0000-00-0000000000 | RIF: J-12345678-0',
 'condominio.samanes@gmail.com (Titular: Junta de Condominio)');

-- 50 APARTAMENTOS (70% ADJUDICADOS, 30% DISPONIBLES, 0 PAGOS)
DELETE FROM apartamentos WHERE condominio_id = 1;
INSERT INTO apartamentos (condominio_id, piso, codigo, alicuota, propietario, cedula, telefono, email, vehiculo) VALUES
(1, 0, 'PB-A', 2, NULL, NULL, NULL, NULL, NULL),
(1, 0, 'PB-B', 2, 'Carlos Eduardo Mendoza', 'V-25.354.750', '0414-7826637', 'carlos.mendoza@gmail.com', NULL),
(1, 1, '1A', 2, 'María Alejandra Rodríguez', 'V-24.621.465', '0414-5617462', 'maria.rodriguez@gmail.com', NULL),
(1, 1, '1B', 2, NULL, NULL, NULL, NULL, NULL),
(1, 1, '1C', 2, 'Alejandro José Blanco', 'V-17.393.428', '0414-6237764', 'alejandro.blanco@gmail.com', NULL),
(1, 1, '1D', 2, 'Valentina Sofía Gómez', 'V-14.654.424', '0414-4131745', 'valentina.gomez@gmail.com', NULL),
(1, 2, '2A', 2, NULL, NULL, NULL, NULL, NULL),
(1, 2, '2B', 2, NULL, NULL, NULL, NULL, NULL),
(1, 2, '2C', 2, NULL, NULL, NULL, NULL, NULL),
(1, 2, '2D', 2, NULL, NULL, NULL, NULL, NULL),
(1, 3, '3A', 2, 'Luis Alberto Herrera', 'V-27.346.533', '0414-2168004', 'luis.herrera@gmail.com', NULL),
(1, 3, '3B', 2, NULL, NULL, NULL, NULL, NULL),
(1, 3, '3C', 2, 'Ana Karina Morales', 'V-24.451.484', '0414-9927869', 'ana.morales@gmail.com', NULL),
(1, 3, '3D', 2, 'Andrés Guillermo Castillo', 'V-15.506.202', '0414-3922806', 'andres.castillo@gmail.com', NULL),
(1, 4, '4A', 2, NULL, NULL, NULL, NULL, NULL),
(1, 4, '4B', 2, 'Daniela Coromoto Pérez', 'V-27.844.543', '0414-3619264', 'daniela.perez@gmail.com', NULL),
(1, 4, '4C', 2, 'Gabriel Enrique Silva', 'V-20.792.786', '0414-2778430', 'gabriel.silva@gmail.com', NULL),
(1, 4, '4D', 2, 'Patricia Elena Torres', 'V-25.683.436', '0414-5855267', 'patricia.torres@gmail.com', NULL),
(1, 5, '5A', 2, 'Fernando Javier Romero', 'V-24.295.700', '0414-2700621', 'fernando.romero@gmail.com', NULL),
(1, 5, '5B', 2, 'Gabriela Isabel Vargas', 'V-28.515.841', '0414-2974899', 'gabriela.vargas@gmail.com', NULL),
(1, 5, '5C', 2, 'Ricardo Antonio Medina', 'V-17.118.474', '0414-2654228', 'ricardo.medina@gmail.com', NULL),
(1, 5, '5D', 2, 'Mariana Carolina Rojas', 'V-20.566.303', '0414-4455361', 'mariana.rojas@gmail.com', NULL),
(1, 6, '6A', 2, 'Héctor Manuel Navarro', 'V-20.653.211', '0414-4232783', 'hector.navarro@gmail.com', NULL),
(1, 6, '6B', 2, 'Sofía Cristina Paredes', 'V-18.630.400', '0414-2510283', 'sofia.paredes@gmail.com', NULL),
(1, 6, '6C', 2, 'José Manuel Rivas', 'V-26.995.900', '0414-8502318', 'jose.rivas@gmail.com', NULL),
(1, 6, '6D', 2, NULL, NULL, NULL, NULL, NULL),
(1, 7, '7A', 2, 'Camila Andrea Delgado', 'V-22.670.608', '0414-1160818', 'camila.delgado@gmail.com', NULL),
(1, 7, '7B', 2, 'David Leonardo Salazar', 'V-26.375.407', '0414-5242080', 'david.salazar@gmail.com', NULL),
(1, 7, '7C', 2, 'Verónica Beatriz Peña', 'V-17.223.407', '0414-3376004', 'veronica.pena@gmail.com', NULL),
(1, 7, '7D', 2, 'Roberto Carlos Fuentes', 'V-14.572.393', '0414-3341271', 'roberto.fuentes@gmail.com', NULL),
(1, 8, '8A', 2, 'Elena Beatriz Quintero', 'V-26.519.965', '0414-4023562', 'elena.quintero@gmail.com', NULL),
(1, 8, '8B', 2, 'Mauricio José Aguilar', 'V-16.411.837', '0414-1078665', 'mauricio.aguilar@gmail.com', NULL),
(1, 8, '8C', 2, NULL, NULL, NULL, NULL, NULL),
(1, 8, '8D', 2, 'Natalia Valentina Ramos', 'V-18.143.173', '0414-9827073', 'natalia.ramos@gmail.com', NULL),
(1, 9, '9A', 2, 'Eduardo Luis Márquez', 'V-26.822.123', '0414-4659866', 'eduardo.marquez@gmail.com', NULL),
(1, 9, '9B', 2, 'Isabella Sofía Gil', 'V-20.246.199', '0414-9088419', 'isabella.gil@gmail.com', NULL),
(1, 9, '9C', 2, 'Gustavo Adolfo Rangel', 'V-17.510.608', '0414-6063245', 'gustavo.rangel@gmail.com', NULL),
(1, 9, '9D', 2, 'Andrea Carolina Contreras', 'V-21.667.701', '0414-4092373', 'andrea.contreras@gmail.com', NULL),
(1, 10, '10A', 2, 'Víctor Alfonso Cárdenas', 'V-21.665.453', '0414-1754321', 'victor.cardenas@gmail.com', NULL),
(1, 10, '10B', 2, NULL, NULL, NULL, NULL, NULL),
(1, 10, '10C', 2, NULL, NULL, NULL, NULL, NULL),
(1, 10, '10D', 2, 'Claudia Marcela Benítez', 'V-13.745.191', '0414-2255134', 'claudia.benitez@gmail.com', NULL),
(1, 11, '11A', 2, 'Álvaro José Escalona', 'V-21.928.620', '0414-9457114', 'Álvaro.escalona@gmail.com', NULL),
(1, 11, '11B', 2, 'Paola Andrea Colmenares', 'V-19.794.483', '0414-7308521', 'paola.colmenares@gmail.com', NULL),
(1, 11, '11C', 2, NULL, NULL, NULL, NULL, NULL),
(1, 11, '11D', 2, 'Javier Ignacio Zambrano', 'V-23.171.627', '0414-8363451', 'javier.zambrano@gmail.com', NULL),
(1, 12, '12A', 2, 'Rosa María Alvarado', 'V-26.614.863', '0414-5744089', 'rosa.alvarado@gmail.com', NULL),
(1, 12, '12B', 2, NULL, NULL, NULL, NULL, NULL),
(1, 12, '12C', 2, 'Diego Armando Cordero', 'V-16.627.769', '0414-2386011', 'diego.cordero@gmail.com', NULL),
(1, 12, '12D', 2, NULL, NULL, NULL, NULL, NULL);
