import React from 'react';
import { interpolate, spring, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const MetricsCtaScene: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const titleSpring = spring({
    frame,
    fps,
    config: { damping: 14 },
  });

  const metrics = [
    { value: '85%', label: 'Menos Tiempo en Cobranza', color: '#3b82f6', icon: '⏱️' },
    { value: '95%', label: 'Cero Conflictos de Tasa', color: '#10b981', icon: '🤝' },
    { value: '78%', label: 'Mayor Puntualidad en Pagos', color: '#8b5cf6', icon: '📈' },
    { value: '100%', label: 'Transparencia Digital', color: '#06b6d4', icon: '📄' },
  ];

  const ctaScale = spring({
    frame: frame - 25,
    fps,
    config: { damping: 12, mass: 0.8 },
  });

  const ctaOpacity = interpolate(frame, [25, 45], [0, 1], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });

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
          marginBottom: 36,
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
          Impacto Real en tu Comunidad
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
          Resultados Comprobables con CondoHub
        </h2>
      </div>

      {/* Metrics Row */}
      <div
        style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(4, 1fr)',
          gap: 24,
          width: '100%',
          maxWidth: 1300,
          marginBottom: 44,
        }}
      >
        {metrics.map((m, idx) => {
          const delay = 5 + idx * 6;
          const cardSpring = spring({
            frame: frame - delay,
            fps,
            config: { damping: 14 },
          });

          return (
            <div
              key={idx}
              style={{
                transform: `scale(${Math.max(0, cardSpring)})`,
                background: 'rgba(15, 23, 42, 0.8)',
                border: `1px solid ${m.color}35`,
                borderRadius: 20,
                padding: '24px 20px',
                textAlign: 'center',
                boxShadow: `0 12px 30px -5px ${m.color}20`,
              }}
            >
              <div style={{ fontSize: 28, marginBottom: 8 }}>{m.icon}</div>
              <div
                style={{
                  fontSize: 48,
                  fontWeight: 900,
                  color: m.color,
                  letterSpacing: '-0.02em',
                }}
              >
                {m.value}
              </div>
              <div
                style={{
                  fontSize: 16,
                  fontWeight: 600,
                  color: '#e2e8f0',
                  marginTop: 6,
                }}
              >
                {m.label}
              </div>
            </div>
          );
        })}
      </div>

      {/* Call To Action Box */}
      <div
        style={{
          transform: `scale(${Math.max(0, ctaScale)})`,
          opacity: ctaOpacity,
          width: '100%',
          maxWidth: 1100,
          background: 'linear-gradient(135deg, rgba(37, 99, 235, 0.25) 0%, rgba(124, 58, 237, 0.25) 100%)',
          border: '1px solid rgba(139, 92, 246, 0.45)',
          borderRadius: 24,
          padding: '28px 48px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          boxShadow: '0 20px 50px rgba(124, 58, 237, 0.25)',
        }}
      >
        <div>
          <h3 style={{ fontSize: 28, fontWeight: 800, color: '#fff', margin: 0 }}>
            ¿Listo para Transformar la Gestión de tu Edificio?
          </h3>
          <p style={{ fontSize: 16, color: '#cbd5e1', margin: '8px 0 0' }}>
            Solicita una demostración guiada sin compromiso para tu Junta de Condominio.
          </p>
        </div>

        <div
          style={{
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'flex-end',
            gap: 6,
          }}
        >
          <div
            style={{
              background: '#10b981',
              color: '#fff',
              fontSize: 18,
              fontWeight: 800,
              padding: '12px 28px',
              borderRadius: 999,
              display: 'flex',
              alignItems: 'center',
              gap: 10,
              boxShadow: '0 10px 25px rgba(16, 185, 129, 0.4)',
            }}
          >
            <span>💬 WhatsApp:</span>
            <span>+58 412-9031966</span>
          </div>
          <span style={{ fontSize: 13, color: '#94a3b8', fontWeight: 600 }}>
            Cluster LG • La Guaira, Venezuela
          </span>
        </div>
      </div>
    </div>
  );
};
