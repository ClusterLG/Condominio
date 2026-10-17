import React from 'react';
import { interpolate, spring, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const StepByStepScene: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const titleSpring = spring({
    frame,
    fps,
    config: { damping: 14 },
  });

  const steps = [
    {
      step: '01',
      title: 'Configuración Bancaria',
      desc: 'Carga de canales oficiales: Pago Móvil, Cuentas Corrientes (Banesco/Mercantil), Zelle y recepción de efectivo.',
      tag: 'Canales Multimoneda',
      icon: '🏦',
    },
    {
      step: '02',
      title: 'Emisión Global e Individual',
      desc: 'Generación de cuotas ordinarias/extraordinarias por alícuota o cuotas exclusivas para un apartamento específico.',
      tag: 'Cuotas Puntuales y Generales',
      icon: '📄',
    },
    {
      step: '03',
      title: 'Pagos Individuales 24/7',
      desc: 'Cada copropietario consulta su desglose, reporta su comprobante bancario y calcula el contravalor exacto al BCV.',
      tag: 'Capture & Referencia',
      icon: '📲',
    },
    {
      step: '04',
      title: 'Gestión y Conciliación Ágil',
      desc: 'La junta audita captures, aprueba en 1 clic, acredita saldos a favor y emite el Recibo Digital con pase a Solvente.',
      tag: 'Recibo Digital & Solvencia',
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
        padding: '0 80px',
        position: 'relative',
        zIndex: 1,
        fontFamily: THEME.fonts.main,
      }}
    >
      {/* Title */}
      <div
        style={{
          transform: `scale(${titleSpring})`,
          textAlign: 'center',
          marginBottom: 44,
        }}
      >
        <span
          style={{
            fontSize: 16,
            fontWeight: 700,
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
            fontWeight: 800,
            color: '#fff',
            margin: '8px 0 0',
            letterSpacing: '-0.02em',
          }}
        >
          El Ciclo Mensual de Cobranza en 4 Pasos
        </h2>
      </div>

      {/* Steps Timeline Grid */}
      <div
        style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(4, 1fr)',
          gap: 24,
          width: '100%',
          maxWidth: 1500,
          position: 'relative',
        }}
      >
        {/* Connecting line behind steps */}
        <div
          style={{
            position: 'absolute',
            top: 48,
            left: '12%',
            right: '12%',
            height: 3,
            background: 'linear-gradient(to right, #3b82f6, #8b5cf6, #10b981)',
            zIndex: 0,
            opacity: 0.4,
          }}
        />

        {steps.map((st, idx) => {
          const delay = 10 + idx * 8;
          const cardSpring = spring({
            frame: frame - delay,
            fps,
            config: { damping: 14, mass: 0.9 },
          });

          const opacity = interpolate(frame, [delay, delay + 15], [0, 1], {
            extrapolateLeft: 'clamp',
            extrapolateRight: 'clamp',
          });

          const y = interpolate(frame, [delay, delay + 15], [30, 0], {
            extrapolateLeft: 'clamp',
            extrapolateRight: 'clamp',
          });

          return (
            <div
              key={idx}
              style={{
                transform: `scale(${Math.max(0, cardSpring)}) translateY(${y}px)`,
                opacity,
                background: 'rgba(15, 23, 42, 0.85)',
                border: '1px solid rgba(255, 255, 255, 0.12)',
                borderRadius: 20,
                padding: 28,
                position: 'relative',
                zIndex: 1,
                boxShadow: '0 20px 40px rgba(0,0,0,0.35)',
              }}
            >
              {/* Step Number & Icon */}
              <div
                style={{
                  display: 'flex',
                  justifyContent: 'space-between',
                  alignItems: 'center',
                  marginBottom: 20,
                }}
              >
                <div
                  style={{
                    width: 48,
                    height: 48,
                    borderRadius: 14,
                    background: 'linear-gradient(135deg, #3b82f6, #8b5cf6)',
                    color: '#fff',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontWeight: 900,
                    fontSize: 20,
                    boxShadow: '0 6px 16px rgba(59, 130, 246, 0.4)',
                  }}
                >
                  {st.step}
                </div>
                <span style={{ fontSize: 32 }}>{st.icon}</span>
              </div>

              <span
                style={{
                  fontSize: 12,
                  fontWeight: 700,
                  color: '#38bdf8',
                  textTransform: 'uppercase',
                  letterSpacing: '0.05em',
                }}
              >
                {st.tag}
              </span>

              <h3 style={{ fontSize: 22, fontWeight: 800, color: '#fff', margin: '8px 0 12px' }}>
                {st.title}
              </h3>

              <p style={{ fontSize: 15, color: '#94a3b8', lineHeight: 1.55, margin: 0 }}>
                {st.desc}
              </p>
            </div>
          );
        })}
      </div>
    </div>
  );
};
