import React from 'react';
import { Audio, interpolate, spring, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const Scene2Reveal: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const logoSpring = spring({
    frame,
    fps,
    config: { damping: 11, mass: 0.7, stiffness: 140 },
  });

  const phoneSpring = spring({
    frame: frame - 25,
    fps,
    config: { damping: 14, mass: 0.8 },
  });

  const glowRotate = interpolate(frame, [0, 120], [0, 360]);

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
      {frame === 0 && <Audio src={staticFile('whoosh.wav')} volume={0.85} />}
      {frame === 20 && <Audio src={staticFile('ding.wav')} volume={0.8} />}
      {frame === 45 && <Audio src={staticFile('tick.wav')} volume={0.65} />}
      {frame === 105 && <Audio src={staticFile('whoosh.wav')} volume={0.8} />}

      {/* Rotating Background Glow */}
      <div
        style={{
          position: 'absolute',
          width: 600,
          height: 600,
          borderRadius: '50%',
          background: 'conic-gradient(from 0deg, #3b82f6, #8b5cf6, #10b981, #3b82f6)',
          filter: 'blur(120px)',
          opacity: 0.28,
          transform: `rotate(${glowRotate}deg)`,
          pointerEvents: 'none',
        }}
      />

      {/* Main Brand Logo Box */}
      <div
        style={{
          transform: `scale(${Math.max(0, logoSpring)})`,
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          textAlign: 'center',
          marginBottom: 28,
        }}
      >
        {/* Isotipo */}
        <div
          style={{
            width: 96,
            height: 96,
            borderRadius: 26,
            background: 'linear-gradient(135deg, #2563eb, #7c3aed)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 50,
            color: '#fff',
            boxShadow: '0 0 50px rgba(59, 130, 246, 0.6), 0 20px 40px rgba(0,0,0,0.4)',
            border: '2px solid rgba(255, 255, 255, 0.25)',
            marginBottom: 18,
          }}
        >
          🏢
        </div>

        <h1
          style={{
            fontSize: 56,
            fontWeight: 900,
            color: '#fff',
            margin: 0,
            letterSpacing: '-0.03em',
            textShadow: '0 4px 20px rgba(0,0,0,0.5)',
          }}
        >
          CondoHub <span style={{ color: '#38bdf8' }}>SaaS</span>
        </h1>

        <p
          style={{
            fontSize: 22,
            fontWeight: 700,
            color: '#94a3b8',
            margin: '8px 0 16px',
            maxWidth: 800,
          }}
        >
          El Ecosistema Inteligente para Edificios y Condominios
        </p>

        {/* Live BCV Pill */}
        <div
          style={{
            display: 'inline-flex',
            alignItems: 'center',
            gap: 10,
            padding: '8px 20px',
            background: 'rgba(16, 185, 129, 0.15)',
            border: '1px solid rgba(16, 185, 129, 0.4)',
            borderRadius: 999,
            color: '#34d399',
            fontSize: 16,
            fontWeight: 800,
            letterSpacing: '0.04em',
            boxShadow: '0 0 25px rgba(16, 185, 129, 0.25)',
          }}
        >
          <span style={{ fontSize: 18 }}>🇻🇪</span>
          <span>TASA BCV EN VIVO AUTOMATIZADA</span>
        </div>
      </div>

      {/* Floating Smartphone Mockup Card */}
      <div
        style={{
          transform: `scale(${Math.max(0, phoneSpring)})`,
          width: '100%',
          maxWidth: 920,
          background: 'linear-gradient(180deg, rgba(30, 41, 59, 0.75) 0%, rgba(15, 23, 42, 0.95) 100%)',
          borderRadius: 28,
          border: '1.5px solid rgba(59, 130, 246, 0.45)',
          padding: '24px 28px',
          boxShadow: '0 25px 60px rgba(0,0,0,0.5), 0 0 40px rgba(59, 130, 246, 0.2)',
        }}
      >
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 18 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <span style={{ fontSize: 24 }}>📍</span>
            <div>
              <div style={{ fontSize: 13, color: '#94a3b8', fontWeight: 600, textTransform: 'uppercase' }}>
                Edificio Piloto Activo
              </div>
              <div style={{ fontSize: 20, color: '#fff', fontWeight: 800 }}>
                Residencias Los Samanes (50 Aptos)
              </div>
            </div>
          </div>
          <span
            style={{
              fontSize: 13,
              fontWeight: 800,
              background: 'rgba(56, 189, 248, 0.15)',
              color: '#38bdf8',
              padding: '6px 14px',
              borderRadius: 8,
              border: '1px solid rgba(56, 189, 248, 0.3)',
            }}
          >
            ONLINE 24/7
          </span>
        </div>

        {/* 3 Pillars Quick Badges */}
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 12 }}>
          {[
            { label: 'Bimoneda Exacta', icon: '💵', sub: 'USD / Bs al día' },
            { label: 'Multi-Condominio', icon: '🏢', sub: 'En la Nube' },
            { label: 'Cero Pérdida', icon: '🔒', sub: 'Data Center Respaldo' },
          ].map((item, idx) => (
            <div
              key={idx}
              style={{
                background: 'rgba(255, 255, 255, 0.04)',
                border: '1px solid rgba(255, 255, 255, 0.08)',
                borderRadius: 14,
                padding: '12px 10px',
                textAlign: 'center',
              }}
            >
              <div style={{ fontSize: 22, marginBottom: 4 }}>{item.icon}</div>
              <div style={{ fontSize: 14, fontWeight: 800, color: '#fff' }}>{item.label}</div>
              <div style={{ fontSize: 11, color: '#94a3b8', marginTop: 2 }}>{item.sub}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};
