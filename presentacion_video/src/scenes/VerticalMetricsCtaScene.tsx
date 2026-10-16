import React from 'react';
import { Audio, spring, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const VerticalMetricsCtaScene: React.FC = () => {
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
      {frame === 26 && <Audio src={staticFile('ding.wav')} volume={0.9} />}

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
          Impacto Real en tu Edificio
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
          Resultados Comprobados
        </h2>
      </div>

      {/* 2x2 Metrics Grid */}
      <div
        style={{
          display: 'grid',
          gridTemplateColumns: '1fr 1fr',
          gap: 20,
          width: '100%',
          maxWidth: 920,
          marginBottom: 36,
        }}
      >
        {metrics.map((m, idx) => {
          const delay = 6 + idx * 5;
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
                background: 'rgba(15, 23, 42, 0.85)',
                border: `1px solid ${m.color}40`,
                borderRadius: 22,
                padding: '24px 20px',
                textAlign: 'center',
                boxShadow: `0 12px 30px -5px ${m.color}20`,
              }}
            >
              <div style={{ fontSize: 32, marginBottom: 6 }}>{m.icon}</div>
              <div
                style={{
                  fontSize: 54,
                  fontWeight: 900,
                  color: m.color,
                  letterSpacing: '-0.02em',
                }}
              >
                {m.value}
              </div>
              <div
                style={{
                  fontSize: 17,
                  fontWeight: 700,
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

      {/* CTA Box */}
      <div
        style={{
          transform: `scale(${Math.max(0, ctaScale)})`,
          width: '100%',
          maxWidth: 920,
          background: 'linear-gradient(135deg, rgba(37, 99, 235, 0.3) 0%, rgba(124, 58, 237, 0.3) 100%)',
          border: '1px solid rgba(139, 92, 246, 0.55)',
          borderRadius: 26,
          padding: '32px 36px',
          textAlign: 'center',
          boxShadow: '0 25px 60px rgba(124, 58, 237, 0.3)',
        }}
      >
        <h3 style={{ fontSize: 32, fontWeight: 900, color: '#fff', margin: 0 }}>
          ¿Listo para Modernizar tu Condominio?
        </h3>
        <p style={{ fontSize: 18, color: '#cbd5e1', margin: '10px 0 24px' }}>
          Solicita una demostración guiada sin compromiso para tu comunidad.
        </p>

        <div
          style={{
            display: 'inline-flex',
            alignItems: 'center',
            gap: 12,
            background: '#10b981',
            color: '#fff',
            fontSize: 24,
            fontWeight: 900,
            padding: '16px 36px',
            borderRadius: 999,
            boxShadow: '0 12px 30px rgba(16, 185, 129, 0.45)',
          }}
        >
          <span>💬 WhatsApp:</span>
          <span>+58 412-9031966</span>
        </div>

        <div style={{ fontSize: 15, color: '#94a3b8', fontWeight: 600, marginTop: 16 }}>
          Cluster LG • 100% hecho en La Guaira, Venezuela
        </div>
      </div>
    </div>
  );
};
