import React from 'react';
import { interpolate, spring, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const IntroScene: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  // Entrance spring
  const scale = spring({
    frame,
    fps,
    config: { damping: 12, mass: 0.8 },
  });

  const opacity = interpolate(frame, [0, 20], [0, 1], {
    extrapolateRight: 'clamp',
  });

  const subtitleTranslate = interpolate(frame, [15, 35], [30, 0], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });

  const badgesOpacity = interpolate(frame, [30, 50], [0, 1], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });

  const badgesY = interpolate(frame, [30, 50], [20, 0], {
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
        position: 'relative',
        zIndex: 1,
        fontFamily: THEME.fonts.main,
        padding: '0 80px',
      }}
    >
      {/* Brand Icon */}
      <div
        style={{
          transform: `scale(${scale})`,
          opacity,
          width: 96,
          height: 96,
          borderRadius: 24,
          background: THEME.gradients.hero,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          boxShadow: '0 20px 45px -10px rgba(59, 130, 246, 0.65)',
          marginBottom: 28,
        }}
      >
        <svg
          width="54"
          height="54"
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

      {/* Main Title */}
      <h1
        style={{
          opacity,
          transform: `scale(${scale})`,
          fontSize: 76,
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
          opacity,
          transform: `translateY(${subtitleTranslate}px)`,
          fontSize: 34,
          fontWeight: 700,
          background: THEME.gradients.hero,
          WebkitBackgroundClip: 'text',
          WebkitTextFillColor: 'transparent',
          marginTop: 12,
          textAlign: 'center',
          letterSpacing: '-0.01em',
        }}
      >
        La Plataforma Inteligente de Gestión Residencial
      </div>

      {/* Value Statement */}
      <p
        style={{
          opacity,
          transform: `translateY(${subtitleTranslate}px)`,
          fontSize: 22,
          fontWeight: 500,
          color: THEME.colors.textSecondary,
          maxWidth: 980,
          textAlign: 'center',
          lineHeight: 1.5,
          marginTop: 20,
          marginBottom: 40,
        }}
      >
        Transparencia Absoluta • Cobranza Bimoneda en USD y Bolívares Oficial BCV •
        Conciliación en 1 Clic y Autogestión Comunitaria 24/7
      </p>

      {/* Feature Pills */}
      <div
        style={{
          opacity: badgesOpacity,
          transform: `translateY(${badgesY}px)`,
          display: 'flex',
          gap: 16,
          flexWrap: 'wrap',
          justifyContent: 'center',
        }}
      >
        {[
          { label: 'Tasa BCV en Tiempo Real', color: '#10b981', icon: '📈' },
          { label: 'Mapa Visual de Pisos y Solvencias', color: '#3b82f6', icon: '🗺️' },
          { label: 'Cobranza Bimoneda y Auditoría 1-Clic', color: '#8b5cf6', icon: '💳' },
          { label: 'Portal Móvil para Residentes', color: '#06b6d4', icon: '📱' },
          { label: 'Atención de Averías e Incidencias', color: '#f59e0b', icon: '🛠️' },
        ].map((badge, idx) => (
          <div
            key={idx}
            style={{
              display: 'flex',
              alignItems: 'center',
              gap: 8,
              background: 'rgba(30, 41, 59, 0.75)',
              border: '1px solid rgba(255, 255, 255, 0.12)',
              borderRadius: 9999,
              padding: '10px 22px',
              fontSize: 16,
              fontWeight: 600,
              color: '#ffffff',
              boxShadow: '0 8px 20px rgba(0,0,0,0.25)',
            }}
          >
            <span>{badge.icon}</span>
            <span>{badge.label}</span>
          </div>
        ))}
      </div>
    </div>
  );
};
