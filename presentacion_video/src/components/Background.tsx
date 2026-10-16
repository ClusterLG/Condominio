import React from 'react';
import { interpolate, useCurrentFrame } from 'remotion';
import { THEME } from '../theme';

export const Background: React.FC = () => {
  const frame = useCurrentFrame();

  const glow1X = interpolate(Math.sin(frame / 60), [-1, 1], [15, 35]);
  const glow1Y = interpolate(Math.cos(frame / 70), [-1, 1], [15, 40]);

  const glow2X = interpolate(Math.cos(frame / 80), [-1, 1], [65, 85]);
  const glow2Y = interpolate(Math.sin(frame / 90), [-1, 1], [60, 85]);

  return (
    <div
      style={{
        position: 'absolute',
        width: '100%',
        height: '100%',
        backgroundColor: THEME.colors.bgDark,
        overflow: 'hidden',
        zIndex: 0,
        fontFamily: THEME.fonts.main,
      }}
    >
      {/* Mesh glow orb 1 */}
      <div
        style={{
          position: 'absolute',
          left: `${glow1X}%`,
          top: `${glow1Y}%`,
          width: 700,
          height: 700,
          transform: 'translate(-50%, -50%)',
          borderRadius: '50%',
          background: 'radial-gradient(circle, rgba(59, 130, 246, 0.22) 0%, rgba(139, 92, 246, 0.12) 45%, transparent 70%)',
          filter: 'blur(90px)',
          pointerEvents: 'none',
        }}
      />

      {/* Mesh glow orb 2 */}
      <div
        style={{
          position: 'absolute',
          left: `${glow2X}%`,
          top: `${glow2Y}%`,
          width: 800,
          height: 800,
          transform: 'translate(-50%, -50%)',
          borderRadius: '50%',
          background: 'radial-gradient(circle, rgba(16, 185, 129, 0.18) 0%, rgba(6, 182, 212, 0.12) 45%, transparent 70%)',
          filter: 'blur(100px)',
          pointerEvents: 'none',
        }}
      />

      {/* Subtle grid pattern */}
      <div
        style={{
          position: 'absolute',
          inset: 0,
          backgroundImage: `
            linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px)
          `,
          backgroundSize: '40px 40px',
        }}
      />

      {/* Top Header Watermark / Pill */}
      <div
        style={{
          position: 'absolute',
          top: 32,
          left: 48,
          display: 'flex',
          alignItems: 'center',
          gap: 12,
          background: 'rgba(15, 23, 42, 0.75)',
          backdropFilter: 'blur(10px)',
          padding: '8px 18px',
          borderRadius: 9999,
          border: '1px solid rgba(255, 255, 255, 0.1)',
        }}
      >
        <div
          style={{
            width: 10,
            height: 10,
            borderRadius: '50%',
            backgroundColor: THEME.colors.success,
            boxShadow: `0 0 10px ${THEME.colors.success}`,
          }}
        />
        <span
          style={{
            fontSize: 14,
            fontWeight: 700,
            letterSpacing: '0.05em',
            color: '#fff',
            textTransform: 'uppercase',
          }}
        >
          CondoHub • Sistema Residencial
        </span>
      </div>

      <div
        style={{
          position: 'absolute',
          top: 36,
          right: 48,
          fontSize: 13,
          fontWeight: 600,
          color: THEME.colors.textMuted,
        }}
      >
        Desarrollado por Cluster LG • La Guaira
      </div>
    </div>
  );
};
