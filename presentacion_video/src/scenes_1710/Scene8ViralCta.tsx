import React from 'react';
import { Audio, spring, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const Scene8ViralCta: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const titleSpring = spring({
    frame,
    fps,
    config: { damping: 14 },
  });

  const buttonSpring = spring({
    frame: frame - 20,
    fps,
    config: { damping: 10, mass: 0.6, stiffness: 200 },
  });

  const cardSpring = spring({
    frame: frame - 40,
    fps,
    config: { damping: 13, mass: 0.8 },
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
        padding: '110px 50px 90px',
        position: 'relative',
        zIndex: 1,
        fontFamily: THEME.fonts.main,
      }}
    >
      {/* Audio SFX */}
      {frame === 0 && <Audio src={staticFile('whoosh.wav')} volume={0.8} />}
      {frame === 20 && <Audio src={staticFile('ding.wav')} volume={0.85} />}
      {frame === 40 && <Audio src={staticFile('pop.wav')} volume={0.7} />}

      {/* Header */}
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
            color: '#38bdf8',
            textTransform: 'uppercase',
            letterSpacing: '0.14em',
            background: 'rgba(56, 189, 248, 0.15)',
            padding: '8px 24px',
            borderRadius: 999,
            border: '1px solid rgba(56, 189, 248, 0.4)',
            boxShadow: '0 0 25px rgba(56, 189, 248, 0.25)',
          }}
        >
          🚀 El Futuro es Ahora
        </span>
        <h1
          style={{
            fontSize: 54,
            fontWeight: 900,
            color: '#fff',
            margin: '14px 0 0',
            letterSpacing: '-0.03em',
            lineHeight: 1.15,
          }}
        >
          Moderniza tu Condominio<br />
          <span style={{ color: '#10b981', textShadow: '0 0 35px rgba(16, 185, 129, 0.6)' }}>
            con CondoHub SaaS
          </span>
        </h1>
        <p style={{ fontSize: 20, color: '#94a3b8', margin: '8px 0 0' }}>
          Solicita tu demostración o ingresa al piloto en vivo
        </p>
      </div>

      {/* Big Viral Action Button */}
      <div
        style={{
          transform: `scale(${Math.max(0, buttonSpring)})`,
          width: '100%',
          maxWidth: 900,
          background: 'linear-gradient(135deg, #2563eb, #8b5cf6)',
          border: '2px solid rgba(255, 255, 255, 0.3)',
          borderRadius: 22,
          padding: '24px 32px',
          textAlign: 'center',
          boxShadow: '0 0 50px rgba(59, 130, 246, 0.6), 0 20px 40px rgba(0,0,0,0.4)',
          marginBottom: 24,
          cursor: 'pointer',
        }}
      >
        <span
          style={{
            fontSize: 30,
            fontWeight: 900,
            color: '#fff',
            letterSpacing: '0.04em',
            display: 'block',
          }}
        >
          🔥 PROBAR DEMOSTRACIÓN GRATIS
        </span>
        <span style={{ fontSize: 16, color: '#dbeafe', fontWeight: 600 }}>
          Acceso instantáneo para Juntas y Administradores
        </span>
      </div>

      {/* Info & Contact Box */}
      <div
        style={{
          transform: `scale(${Math.max(0, cardSpring)})`,
          width: '100%',
          maxWidth: 900,
          background: 'linear-gradient(180deg, rgba(30, 41, 59, 0.8) 0%, rgba(15, 23, 42, 0.95) 100%)',
          borderRadius: 22,
          border: '1.5px solid rgba(255, 255, 255, 0.12)',
          padding: '22px 28px',
          boxShadow: '0 16px 36px rgba(0,0,0,0.4)',
          display: 'flex',
          flexDirection: 'column',
          gap: 12,
        }}
      >
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <span style={{ fontSize: 22 }}>🏢</span>
            <span style={{ fontSize: 16, fontWeight: 800, color: '#fff' }}>
              Directorio Piloto: Residencias Los Samanes (50 Unidades)
            </span>
          </div>
          <span
            style={{
              fontSize: 12,
              fontWeight: 800,
              color: '#34d399',
              background: 'rgba(16, 185, 129, 0.15)',
              padding: '4px 10px',
              borderRadius: 6,
            }}
          >
            ACTIVO
          </span>
        </div>

        <div style={{ height: 1, background: 'rgba(255, 255, 255, 0.08)' }} />

        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', fontSize: 15 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 8, color: '#93c5fd', fontWeight: 700 }}>
            <span>🌐</span>
            <span>github.com/ClusterLG/Condominio</span>
          </div>
          <div style={{ display: 'flex', alignItems: 'center', gap: 8, color: '#34d399', fontWeight: 800 }}>
            <span>📲 WhatsApp:</span>
            <span>0412-9031966</span>
          </div>
        </div>
      </div>

      {/* Final Signature */}
      <div
        style={{
          marginTop: 20,
          fontSize: 14,
          fontWeight: 700,
          color: '#64748b',
          letterSpacing: '0.06em',
          textTransform: 'uppercase',
        }}
      >
        Cluster LG • 100% Hecho en La Guaira
      </div>
    </div>
  );
};
