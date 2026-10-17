import React from 'react';
import { Audio, interpolate, spring, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const Scene1Hook: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  // Entrance spring for main alert
  const titleSpring = spring({
    frame,
    fps,
    config: { damping: 10, mass: 0.6, stiffness: 180 },
  });

  // Stamp spring for "HAY UNA MEJOR FORMA"
  const stampSpring = spring({
    frame: frame - 65,
    fps,
    config: { damping: 12, mass: 0.5, stiffness: 220 },
  });

  // Camera shake calculation around frame 10-35
  const shake = interpolate(frame, [10, 15, 20, 25, 30, 35], [0, -8, 8, -6, 6, 0], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });

  const bubbles = [
    {
      icon: '💬',
      title: 'WhatsApp saturado',
      text: '"Vecino, ¿a qué tasa BCV me toca pagar hoy?"',
      color: '#ef4444',
      delay: 15,
    },
    {
      icon: '⚠️',
      title: 'Auditoría manual',
      text: '"Capture ilegible o referencia bancaria duplicada"',
      color: '#f59e0b',
      delay: 32,
    },
    {
      icon: '📄',
      title: 'Papel extraviado',
      text: '"Recibos en físico perdidos en conserjería"',
      color: '#ec4899',
      delay: 48,
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
        padding: '120px 60px 100px',
        position: 'relative',
        zIndex: 1,
        fontFamily: THEME.fonts.main,
        transform: `translate(${shake}px, ${shake * 0.5}px)`,
      }}
    >
      {/* Audio SFX */}
      {frame === 0 && <Audio src={staticFile('riser.wav')} volume={0.85} />}
      {frame === 15 && <Audio src={staticFile('pop.wav')} volume={0.7} />}
      {frame === 32 && <Audio src={staticFile('pop.wav')} volume={0.7} />}
      {frame === 48 && <Audio src={staticFile('pop.wav')} volume={0.7} />}
      {frame === 65 && <Audio src={staticFile('ding.wav')} volume={0.8} />}
      {frame === 105 && <Audio src={staticFile('whoosh.wav')} volume={0.9} />}

      {/* Warning Top Pill */}
      <div
        style={{
          transform: `scale(${Math.max(0, titleSpring)})`,
          textAlign: 'center',
          marginBottom: 30,
        }}
      >
        <span
          style={{
            fontSize: 16,
            fontWeight: 800,
            color: '#f87171',
            textTransform: 'uppercase',
            letterSpacing: '0.14em',
            background: 'rgba(239, 68, 68, 0.15)',
            padding: '8px 22px',
            borderRadius: 999,
            border: '1px solid rgba(239, 68, 68, 0.4)',
            boxShadow: '0 0 20px rgba(239, 68, 68, 0.25)',
          }}
        >
          🚨 El Problema Tradicional
        </span>

        <h1
          style={{
            fontSize: 54,
            fontWeight: 900,
            color: '#fff',
            margin: '16px 0 0',
            letterSpacing: '-0.03em',
            lineHeight: 1.1,
          }}
        >
          ¿CAOS EN LA COBRANZA<br />
          <span style={{ color: '#ef4444', textShadow: '0 0 30px rgba(239, 68, 68, 0.6)' }}>
            DE TU EDIFICIO?
          </span>
        </h1>
      </div>

      {/* 3 Chaotic Notification Bubbles */}
      <div
        style={{
          display: 'flex',
          flexDirection: 'column',
          gap: 18,
          width: '100%',
          maxWidth: 960,
          marginBottom: 36,
        }}
      >
        {bubbles.map((b, idx) => {
          const bSpring = spring({
            frame: frame - b.delay,
            fps,
            config: { damping: 12, mass: 0.7 },
          });

          return (
            <div
              key={idx}
              style={{
                transform: `scale(${Math.max(0, bSpring)}) translateX(${idx % 2 === 0 ? '-10px' : '10px'})`,
                background: 'linear-gradient(135deg, rgba(30, 41, 59, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%)',
                border: `1.5px solid ${b.color}55`,
                borderRadius: 22,
                padding: '20px 24px',
                display: 'flex',
                alignItems: 'center',
                gap: 18,
                boxShadow: `0 14px 32px -6px ${b.color}25`,
              }}
            >
              <div
                style={{
                  width: 54,
                  height: 54,
                  borderRadius: 16,
                  background: `${b.color}20`,
                  border: `1px solid ${b.color}50`,
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: 26,
                  flexShrink: 0,
                }}
              >
                {b.icon}
              </div>
              <div>
                <span
                  style={{
                    fontSize: 12,
                    fontWeight: 800,
                    color: b.color,
                    textTransform: 'uppercase',
                    letterSpacing: '0.08em',
                  }}
                >
                  {b.title}
                </span>
                <p
                  style={{
                    fontSize: 18,
                    fontWeight: 700,
                    color: '#f1f5f9',
                    margin: '3px 0 0',
                    lineHeight: 1.3,
                  }}
                >
                  {b.text}
                </p>
              </div>
            </div>
          );
        })}
      </div>

      {/* Solution Stamp at frame 65 */}
      {frame >= 60 && (
        <div
          style={{
            transform: `scale(${Math.max(0, stampSpring)}) rotate(-3deg)`,
            background: 'linear-gradient(135deg, #059669 0%, #10b981 100%)',
            padding: '16px 36px',
            borderRadius: 18,
            boxShadow: '0 0 45px rgba(16, 185, 129, 0.7), 0 16px 32px rgba(0,0,0,0.5)',
            border: '2px solid #6ee7b7',
            textAlign: 'center',
          }}
        >
          <span
            style={{
              fontSize: 28,
              fontWeight: 900,
              color: '#ffffff',
              letterSpacing: '0.04em',
              textTransform: 'uppercase',
              display: 'block',
            }}
          >
            ✨ HAY UNA MEJOR FORMA
          </span>
          <span
            style={{
              fontSize: 15,
              fontWeight: 700,
              color: '#d1fae5',
              letterSpacing: '0.02em',
            }}
          >
            100% Digital • Cero Estrés • En la Nube
          </span>
        </div>
      )}
    </div>
  );
};
