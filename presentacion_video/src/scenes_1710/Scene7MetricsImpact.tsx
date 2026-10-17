import React from 'react';
import { Audio, spring, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const Scene7MetricsImpact: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const titleSpring = spring({
    frame,
    fps,
    config: { damping: 14 },
  });

  const metrics = [
    { value: '-85%', label: 'Menos Tiempo en Cobranza', icon: '⏱️', color: '#38bdf8', delay: 10 },
    { value: '100%', label: 'Cero Conflictos de Tasa', icon: '🤝', color: '#10b981', delay: 25 },
    { value: '24/7', label: 'Autogestión del Residente', icon: '📱', color: '#8b5cf6', delay: 40 },
    { value: 'Multi', label: 'Condominios en la Nube', icon: '🏢', color: '#f59e0b', delay: 55 },
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
        padding: '110px 50px 90px',
        position: 'relative',
        zIndex: 1,
        fontFamily: THEME.fonts.main,
      }}
    >
      {/* Audio SFX */}
      {frame === 0 && <Audio src={staticFile('riser.wav')} volume={0.8} />}
      {frame === 10 && <Audio src={staticFile('pop.wav')} volume={0.7} />}
      {frame === 25 && <Audio src={staticFile('tick.wav')} volume={0.7} />}
      {frame === 40 && <Audio src={staticFile('pop.wav')} volume={0.7} />}
      {frame === 55 && <Audio src={staticFile('ding.wav')} volume={0.8} />}

      {/* Header */}
      <div
        style={{
          transform: `scale(${Math.max(0, titleSpring)})`,
          textAlign: 'center',
          marginBottom: 32,
        }}
      >
        <span
          style={{
            fontSize: 15,
            fontWeight: 800,
            color: '#10b981',
            textTransform: 'uppercase',
            letterSpacing: '0.12em',
            background: 'rgba(16, 185, 129, 0.12)',
            padding: '6px 18px',
            borderRadius: 999,
            border: '1px solid rgba(16, 185, 129, 0.35)',
          }}
        >
          📈 Resultados Comprobables
        </span>
        <h2
          style={{
            fontSize: 46,
            fontWeight: 900,
            color: '#fff',
            margin: '8px 0 0',
            letterSpacing: '-0.02em',
          }}
        >
          El Impacto en tu Edificio
        </h2>
        <p style={{ fontSize: 18, color: '#94a3b8', margin: '4px 0 0' }}>
          De la desorganización manual al control financiero total
        </p>
      </div>

      {/* 2x2 Big Stat Cards Grid */}
      <div
        style={{
          display: 'grid',
          gridTemplateColumns: '1fr 1fr',
          gap: 20,
          width: '100%',
          maxWidth: 960,
        }}
      >
        {metrics.map((m, idx) => {
          const cardSpring = spring({
            frame: frame - m.delay,
            fps,
            config: { damping: 11, mass: 0.7, stiffness: 180 },
          });

          return (
            <div
              key={idx}
              style={{
                transform: `scale(${Math.max(0, cardSpring)})`,
                background: 'linear-gradient(135deg, rgba(30, 41, 59, 0.75) 0%, rgba(15, 23, 42, 0.95) 100%)',
                border: `1.5px solid ${m.color}45`,
                borderRadius: 24,
                padding: '30px 24px',
                textAlign: 'center',
                boxShadow: `0 16px 36px -8px ${m.color}25`,
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center',
              }}
            >
              <div style={{ fontSize: 34, marginBottom: 8 }}>{m.icon}</div>
              <div
                style={{
                  fontSize: 52,
                  fontWeight: 900,
                  color: m.color,
                  letterSpacing: '-0.03em',
                  textShadow: `0 0 25px ${m.color}60`,
                  lineHeight: 1,
                }}
              >
                {m.value}
              </div>
              <div
                style={{
                  fontSize: 16,
                  fontWeight: 800,
                  color: '#fff',
                  marginTop: 10,
                  lineHeight: 1.3,
                }}
              >
                {m.label}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};
