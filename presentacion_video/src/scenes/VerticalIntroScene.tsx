import React from 'react';
import { Audio, interpolate, spring, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const VerticalIntroScene: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const iconScale = spring({
    frame,
    fps,
    config: { damping: 12, mass: 0.8 },
  });

  const titleSpring = spring({
    frame: frame - 6,
    fps,
    config: { damping: 14 },
  });

  const tagTranslate = interpolate(frame, [14, 30], [35, 0], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });
  const tagOpacity = interpolate(frame, [14, 30], [0, 1], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });

  const pills = [
    { label: 'Tasa BCV Oficial en Tiempo Real', color: '#10b981', icon: '📈' },
    { label: 'Mapa Interactivo de Pisos y Solvencias', color: '#3b82f6', icon: '🗺️' },
    { label: 'Cobranza Bimoneda USD y Bolívares', color: '#8b5cf6', icon: '💳' },
    { label: 'Portal Móvil para Copropietarios 24/7', color: '#06b6d4', icon: '📱' },
    { label: 'Atención de Averías e Incidencias', color: '#f59e0b', icon: '🛠️' },
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
        padding: '140px 60px 100px',
        position: 'relative',
        zIndex: 1,
        fontFamily: THEME.fonts.main,
      }}
    >
      {/* Sound Effect: Intro Whoosh */}
      <Audio src={staticFile('whoosh.wav')} volume={0.8} />

      {/* Brand Icon */}
      <div
        style={{
          transform: `scale(${Math.max(0, iconScale)})`,
          width: 130,
          height: 130,
          borderRadius: 32,
          background: THEME.gradients.hero,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          boxShadow: '0 25px 60px -10px rgba(59, 130, 246, 0.75)',
          marginBottom: 32,
        }}
      >
        <svg
          width="74"
          height="74"
          viewBox="0 0 24 24"
          fill="none"
          stroke="#ffffff"
          strokeWidth="2.2"
          strokeLinecap="round"
          strokeLinejoin="round"
        >
          <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z" />
          <path d="M6 12H4a2 2 0 0 0-2 2v8" />
          <path d="M18 9h2a2 2 0 0 1 2 2v11" />
          <path d="M10 6h4" />
          <path d="M10 10h4" />
          <path d="M10 14h4" />
          <path d="M10 18h4" />
        </svg>
      </div>

      {/* Main Brand Title */}
      <h1
        style={{
          transform: `scale(${Math.max(0, titleSpring)})`,
          fontSize: 88,
          fontWeight: 900,
          color: '#ffffff',
          margin: 0,
          letterSpacing: '-0.03em',
          textAlign: 'center',
        }}
      >
        Condo<span style={{ color: THEME.colors.primaryLight }}>Hub</span>
      </h1>

      {/* Tagline */}
      <div
        style={{
          opacity: tagOpacity,
          transform: `translateY(${tagTranslate}px)`,
          fontSize: 36,
          fontWeight: 800,
          background: THEME.gradients.hero,
          WebkitBackgroundClip: 'text',
          WebkitTextFillColor: 'transparent',
          marginTop: 16,
          textAlign: 'center',
          lineHeight: 1.25,
        }}
      >
        La Plataforma Inteligente de Gestión Residencial
      </div>

      {/* Subtitle Description */}
      <p
        style={{
          opacity: tagOpacity,
          transform: `translateY(${tagTranslate}px)`,
          fontSize: 24,
          color: THEME.colors.textSecondary,
          textAlign: 'center',
          lineHeight: 1.5,
          marginTop: 24,
          marginBottom: 48,
          maxWidth: 900,
        }}
      >
        Transparencia Absoluta • Cobranza Bimoneda • Conciliación en 1 Clic y Autogestión 24/7
      </p>

      {/* Vertical Feature Badges Stack */}
      <div
        style={{
          display: 'flex',
          flexDirection: 'column',
          gap: 16,
          width: '100%',
          maxWidth: 820,
        }}
      >
        {pills.map((p, idx) => {
          const delay = 30 + idx * 7;
          const popSpring = spring({
            frame: frame - delay,
            fps,
            config: { damping: 14, mass: 0.8 },
          });

          return (
            <div
              key={idx}
              style={{
                transform: `scale(${Math.max(0, popSpring)})`,
                display: 'flex',
                alignItems: 'center',
                gap: 18,
                background: 'rgba(15, 23, 42, 0.85)',
                border: `1px solid ${p.color}50`,
                borderRadius: 20,
                padding: '16px 26px',
                boxShadow: `0 10px 30px -5px ${p.color}20`,
              }}
            >
              {/* Play pop sound on reveal */}
              {frame === delay && <Audio src={staticFile('pop.wav')} volume={0.6} />}

              <div
                style={{
                  width: 48,
                  height: 48,
                  borderRadius: 14,
                  background: `${p.color}20`,
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: 26,
                }}
              >
                {p.icon}
              </div>
              <span
                style={{
                  fontSize: 22,
                  fontWeight: 700,
                  color: '#ffffff',
                }}
              >
                {p.label}
              </span>
            </div>
          );
        })}
      </div>
    </div>
  );
};
