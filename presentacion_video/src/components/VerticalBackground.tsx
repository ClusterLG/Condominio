import React from 'react';
import { interpolate, useCurrentFrame } from 'remotion';
import { THEME } from '../theme';

export const VerticalBackground: React.FC = () => {
  const frame = useCurrentFrame();

  const glow1Y = interpolate(Math.sin(frame / 60), [-1, 1], [15, 35]);
  const glow2Y = interpolate(Math.cos(frame / 70), [-1, 1], [65, 85]);

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
      {/* Mesh glow orb 1 (Top) */}
      <div
        style={{
          position: 'absolute',
          left: '50%',
          top: `${glow1Y}%`,
          width: 750,
          height: 750,
          transform: 'translate(-50%, -50%)',
          borderRadius: '50%',
          background: 'radial-gradient(circle, rgba(59, 130, 246, 0.25) 0%, rgba(139, 92, 246, 0.12) 45%, transparent 70%)',
          filter: 'blur(90px)',
          pointerEvents: 'none',
        }}
      />

      {/* Mesh glow orb 2 (Bottom) */}
      <div
        style={{
          position: 'absolute',
          left: '50%',
          top: `${glow2Y}%`,
          width: 800,
          height: 800,
          transform: 'translate(-50%, -50%)',
          borderRadius: '50%',
          background: 'radial-gradient(circle, rgba(16, 185, 129, 0.22) 0%, rgba(6, 182, 212, 0.12) 45%, transparent 70%)',
          filter: 'blur(100px)',
          pointerEvents: 'none',
        }}
      />

      {/* Grid Pattern */}
      <div
        style={{
          position: 'absolute',
          inset: 0,
          backgroundImage: `
            linear-gradient(to right, rgba(255, 255, 255, 0.035) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(255, 255, 255, 0.035) 1px, transparent 1px)
          `,
          backgroundSize: '48px 48px',
        }}
      />

      {/* Top Header Pill for Reels/TikTok/Status (Safe from notch) */}
      <div
        style={{
          position: 'absolute',
          top: 70,
          left: '50%',
          transform: 'translateX(-50%)',
          display: 'flex',
          alignItems: 'center',
          gap: 12,
          background: 'rgba(15, 23, 42, 0.85)',
          backdropFilter: 'blur(12px)',
          padding: '12px 28px',
          borderRadius: 9999,
          border: '1px solid rgba(255, 255, 255, 0.15)',
          boxShadow: '0 10px 25px rgba(0,0,0,0.3)',
          zIndex: 10,
        }}
      >
        <div
          style={{
            width: 12,
            height: 12,
            borderRadius: '50%',
            backgroundColor: THEME.colors.success,
            boxShadow: `0 0 12px ${THEME.colors.success}`,
          }}
        />
        <span
          style={{
            fontSize: 18,
            fontWeight: 800,
            letterSpacing: '0.06em',
            color: '#fff',
            textTransform: 'uppercase',
          }}
        >
          CondoHub • Sistema Residencial
        </span>
      </div>

      {/* Bottom Subtitle Pill (Safe from TikTok/Reels caption bar) */}
      <div
        style={{
          position: 'absolute',
          bottom: 45,
          left: '50%',
          transform: 'translateX(-50%)',
          fontSize: 16,
          fontWeight: 700,
          color: THEME.colors.textMuted,
          letterSpacing: '0.04em',
          zIndex: 10,
        }}
      >
        Cluster LG • La Guaira, Venezuela
      </div>
    </div>
  );
};
