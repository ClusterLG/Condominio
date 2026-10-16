import React from 'react';
import { Audio, spring, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const VerticalStepByStepScene: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const titleSpring = spring({
    frame,
    fps,
    config: { damping: 14 },
  });

  const steps = [
    {
      num: '01',
      title: 'Configuración de Canales',
      desc: 'Carga de cuentas autorizadas: Pago Móvil, Cuentas Corrientes y Zelle para recaudación.',
      tag: 'Canales Claros',
      icon: '🏦',
    },
    {
      num: '02',
      title: 'Emisión de la Cuota',
      desc: 'Emisión en USD calculada automáticamente por alícuota y convertida a Bs. oficiales al BCV.',
      tag: 'Cálculo Exacto',
      icon: '📄',
    },
    {
      num: '03',
      title: 'Reporte del Copropietario',
      desc: 'El residente consulta su saldo, transfiere desde su banco y reporta con capture en el portal.',
      tag: 'Autogestión 24/7',
      icon: '📲',
    },
    {
      num: '04',
      title: 'Conciliación en 1 Clic',
      desc: 'La junta audita el comprobante, aprueba con 1 clic y el apartamento pasa a Solvente al instante.',
      tag: 'Cuentas Claras',
      icon: '✨',
    },
  ];

  return (
    <div
      style={{
        width: '100%',
        height: '100%',
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        padding: '120px 60px 90px',
        position: 'relative',
        zIndex: 1,
        fontFamily: THEME.fonts.main,
      }}
    >
      <Audio src={staticFile('whoosh.wav')} volume={0.8} />

      {/* Title */}
      <div
        style={{
          transform: `scale(${titleSpring})`,
          textAlign: 'center',
          marginBottom: 32,
        }}
      >
        <span
          style={{
            fontSize: 18,
            fontWeight: 800,
            color: THEME.colors.primaryLight,
            textTransform: 'uppercase',
            letterSpacing: '0.12em',
          }}
        >
          Flujo Operativo Simple y Transparente
        </span>
        <h2
          style={{
            fontSize: 48,
            fontWeight: 900,
            color: '#fff',
            margin: '8px 0 0',
            letterSpacing: '-0.02em',
          }}
        >
          El Ciclo Mensual en 4 Pasos
        </h2>
      </div>

      {/* 4 Steps Vertical Timeline */}
      <div
        style={{
          display: 'flex',
          flexDirection: 'column',
          gap: 20,
          width: '100%',
          maxWidth: 920,
          position: 'relative',
        }}
      >
        {steps.map((st, idx) => {
          const delay = 8 + idx * 7;
          const cardSpring = spring({
            frame: frame - delay,
            fps,
            config: { damping: 14, mass: 0.8 },
          });

          return (
            <div
              key={idx}
              style={{
                transform: `scale(${Math.max(0, cardSpring)})`,
                background: 'rgba(15, 23, 42, 0.85)',
                border: '1px solid rgba(255, 255, 255, 0.12)',
                borderRadius: 22,
                padding: '22px 28px',
                display: 'flex',
                alignItems: 'center',
                gap: 22,
                boxShadow: '0 15px 35px rgba(0,0,0,0.3)',
              }}
            >
              {frame === delay && <Audio src={staticFile('tick.wav')} volume={0.7} />}

              {/* Step Badge */}
              <div
                style={{
                  width: 64,
                  height: 64,
                  borderRadius: 18,
                  background: 'linear-gradient(135deg, #3b82f6, #8b5cf6)',
                  color: '#fff',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontWeight: 900,
                  fontSize: 26,
                  boxShadow: '0 8px 20px rgba(59, 130, 246, 0.4)',
                  flexShrink: 0,
                }}
              >
                {st.num}
              </div>

              <div style={{ flex: 1 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <h3 style={{ fontSize: 24, fontWeight: 800, color: '#fff', margin: 0 }}>
                    {st.title}
                  </h3>
                  <span style={{ fontSize: 26 }}>{st.icon}</span>
                </div>
                <div style={{ fontSize: 13, fontWeight: 700, color: '#38bdf8', textTransform: 'uppercase', marginTop: 2 }}>
                  {st.tag}
                </div>
                <p style={{ fontSize: 17, color: '#94a3b8', lineHeight: 1.45, margin: '6px 0 0' }}>
                  {st.desc}
                </p>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};
