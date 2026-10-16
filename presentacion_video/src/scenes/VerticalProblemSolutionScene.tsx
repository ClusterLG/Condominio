import React from 'react';
import { Audio, interpolate, spring, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const VerticalProblemSolutionScene: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const titleSpring = spring({
    frame,
    fps,
    config: { damping: 14 },
  });

  const card1Y = interpolate(frame, [10, 26], [40, 0], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });
  const card1Opacity = interpolate(frame, [10, 26], [0, 1], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });

  const card2Y = interpolate(frame, [35, 52], [40, 0], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });
  const card2Opacity = interpolate(frame, [35, 52], [0, 1], {
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
        padding: '130px 60px 100px',
        position: 'relative',
        zIndex: 1,
        fontFamily: THEME.fonts.main,
      }}
    >
      {/* Sound Effects */}
      <Audio src={staticFile('whoosh.wav')} volume={0.8} />
      {frame === 10 && <Audio src={staticFile('pop.wav')} volume={0.7} />}
      {frame === 36 && <Audio src={staticFile('ding.wav')} volume={0.9} />}

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
            fontSize: 18,
            fontWeight: 800,
            color: THEME.colors.primaryLight,
            textTransform: 'uppercase',
            letterSpacing: '0.12em',
          }}
        >
          Transformación Digital Comunitaria
        </span>
        <h2
          style={{
            fontSize: 50,
            fontWeight: 900,
            color: '#fff',
            margin: '10px 0 0',
            letterSpacing: '-0.02em',
          }}
        >
          ¿Por Qué Cambiar el Modelo?
        </h2>
      </div>

      {/* Vertical Stack: Problem on Top, Solution on Bottom */}
      <div
        style={{
          display: 'flex',
          flexDirection: 'column',
          gap: 28,
          width: '100%',
          maxWidth: 920,
        }}
      >
        {/* The Problem (Red) */}
        <div
          style={{
            transform: `translateY(${card1Y}px)`,
            opacity: card1Opacity,
            background: 'rgba(239, 68, 68, 0.08)',
            border: '1px solid rgba(239, 68, 68, 0.35)',
            borderRadius: 24,
            padding: '30px 32px',
            boxShadow: '0 20px 45px rgba(0,0,0,0.3)',
          }}
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 18 }}>
            <div
              style={{
                width: 46,
                height: 46,
                borderRadius: 14,
                background: 'rgba(239, 68, 68, 0.2)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: 24,
              }}
            >
              ❌
            </div>
            <h3 style={{ fontSize: 28, fontWeight: 800, color: '#fca5a5', margin: 0 }}>
              El Caos Tradicional en Condominios
            </h3>
          </div>

          <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
            {[
              { t: 'Caos en WhatsApp', d: 'Comprobantes extraviados y reclamos constantes a deshoras.' },
              { t: 'Discusiones por Tasa', d: 'Fricción vecinal por la tasa de cambio aplicable a los pagos.' },
              { t: 'Morosidad y Desconfianza', d: 'Falta de transparencia sobre el estado real de los fondos.' },
              { t: 'Averías Comunales Olvidadas', d: 'Quejas y filtraciones que quedan sin seguimiento oficial.' },
            ].map((item, idx) => (
              <div key={idx} style={{ display: 'flex', gap: 12, alignItems: 'center' }}>
                <span style={{ color: '#ef4444', fontSize: 20 }}>•</span>
                <span style={{ fontSize: 20, color: '#fecaca' }}>
                  <strong>{item.t}:</strong> <span style={{ color: '#cbd5e1' }}>{item.d}</span>
                </span>
              </div>
            ))}
          </div>
        </div>

        {/* The Solution (Green) */}
        <div
          style={{
            transform: `translateY(${card2Y}px)`,
            opacity: card2Opacity,
            background: 'rgba(16, 185, 129, 0.1)',
            border: '1px solid rgba(16, 185, 129, 0.45)',
            borderRadius: 24,
            padding: '30px 32px',
            boxShadow: '0 25px 50px rgba(16, 185, 129, 0.18)',
          }}
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 18 }}>
            <div
              style={{
                width: 46,
                height: 46,
                borderRadius: 14,
                background: 'rgba(16, 185, 129, 0.25)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: 24,
              }}
            >
              ✅
            </div>
            <h3 style={{ fontSize: 28, fontWeight: 800, color: '#6ee7b7', margin: 0 }}>
              La Solución Integral CondoHub
            </h3>
          </div>

          <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
            {[
              { t: 'Bandeja Centralizada', d: 'Cada pago con capture bancario y aprobación en 1 clic.' },
              { t: 'Tasa BCV Automatizada', d: 'Conversión al segundo con la fecha valor oficial.' },
              { t: 'Mapa Visual de Pisos', d: 'Semáforo de solvencia en tiempo real piso por piso.' },
              { t: 'Mesa de Averías Digital', d: 'Tickets oficiales con estatus en vivo para toda la comunidad.' },
            ].map((item, idx) => (
              <div key={idx} style={{ display: 'flex', gap: 12, alignItems: 'center' }}>
                <span style={{ color: '#10b981', fontSize: 20 }}>✔</span>
                <span style={{ fontSize: 20, color: '#d1fae5' }}>
                  <strong>{item.t}:</strong> <span style={{ color: '#e2e8f0' }}>{item.d}</span>
                </span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};
