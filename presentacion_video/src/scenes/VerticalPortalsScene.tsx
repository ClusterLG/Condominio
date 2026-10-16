import React from 'react';
import { Audio, spring, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const VerticalPortalsScene: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const titleSpring = spring({
    frame,
    fps,
    config: { damping: 14 },
  });

  const topCardSpring = spring({
    frame: frame - 10,
    fps,
    config: { damping: 14, mass: 0.8 },
  });

  const bottomCardSpring = spring({
    frame: frame - 24,
    fps,
    config: { damping: 14, mass: 0.8 },
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
      {frame === 10 && <Audio src={staticFile('pop.wav')} volume={0.7} />}
      {frame === 24 && <Audio src={staticFile('pop.wav')} volume={0.7} />}

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
          Diseñado para Administradores y Vecinos
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
          Dos Portales en Perfecta Sincronía
        </h2>
      </div>

      {/* Two Portal Cards Stacked Vertically */}
      <div
        style={{
          display: 'flex',
          flexDirection: 'column',
          gap: 26,
          width: '100%',
          maxWidth: 920,
        }}
      >
        {/* Top: Portal de la Junta */}
        <div
          style={{
            transform: `scale(${Math.max(0, topCardSpring)})`,
            background: 'linear-gradient(180deg, rgba(30, 58, 138, 0.3) 0%, rgba(15, 23, 42, 0.95) 100%)',
            border: '1px solid rgba(59, 130, 246, 0.45)',
            borderRadius: 24,
            padding: '28px 32px',
            boxShadow: '0 20px 45px rgba(59, 130, 246, 0.2)',
          }}
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 16 }}>
            <div
              style={{
                width: 52,
                height: 52,
                borderRadius: 14,
                background: 'rgba(59, 130, 246, 0.2)',
                border: '1px solid rgba(59, 130, 246, 0.5)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: 28,
              }}
            >
              🛡️
            </div>
            <div>
              <span style={{ fontSize: 13, fontWeight: 800, color: '#60a5fa', textTransform: 'uppercase' }}>
                Para la Administración
              </span>
              <h3 style={{ fontSize: 26, fontWeight: 900, color: '#fff', margin: 0 }}>
                Portal de la Junta de Condominio
              </h3>
            </div>
          </div>

          <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
            {[
              '🗺️ Mapa de pisos con semáforo de solvencias al día.',
              '💳 Emisión y reparto equitativo de cuotas ordinarias y extraordinarias.',
              '🔍 Auditoría de pagos con visor de comprobantes y validación en 1 clic.',
              '📢 Cartelera informativa digital para asambleas y comunicados oficiales.',
            ].map((text, idx) => (
              <div key={idx} style={{ fontSize: 18, color: '#e2e8f0', display: 'flex', alignItems: 'center', gap: 10 }}>
                <span>{text}</span>
              </div>
            ))}
          </div>
        </div>

        {/* Bottom: Portal del Residente */}
        <div
          style={{
            transform: `scale(${Math.max(0, bottomCardSpring)})`,
            background: 'linear-gradient(180deg, rgba(5, 150, 105, 0.25) 0%, rgba(15, 23, 42, 0.95) 100%)',
            border: '1px solid rgba(16, 185, 129, 0.45)',
            borderRadius: 24,
            padding: '28px 32px',
            boxShadow: '0 20px 45px rgba(16, 185, 129, 0.2)',
          }}
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 16 }}>
            <div
              style={{
                width: 52,
                height: 52,
                borderRadius: 14,
                background: 'rgba(16, 185, 129, 0.25)',
                border: '1px solid rgba(16, 185, 129, 0.5)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: 28,
              }}
            >
              📱
            </div>
            <div>
              <span style={{ fontSize: 13, fontWeight: 800, color: '#34d399', textTransform: 'uppercase' }}>
                Para Vecinos y Propietarios
              </span>
              <h3 style={{ fontSize: 26, fontWeight: 900, color: '#fff', margin: 0 }}>
                Portal Móvil de Autogestión 24/7
              </h3>
            </div>
          </div>

          <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
            {[
              '💵 Consulta de saldo exacto en USD y Bolívares oficiales según BCV en vivo.',
              '🏦 Cuentas autorizadas oficiales (Pago Móvil, Cuentas Corrientes, Zelle).',
              '📤 Reporte ágil de pagos con capture desde cualquier celular sin app pesada.',
              '📝 Mesa de averías para reportar filtraciones y ascensores con fotos.',
            ].map((text, idx) => (
              <div key={idx} style={{ fontSize: 18, color: '#e2e8f0', display: 'flex', alignItems: 'center', gap: 10 }}>
                <span>{text}</span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};
