import React from 'react';
import { Audio, spring, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const VerticalCoreFeaturesScene: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const titleSpring = spring({
    frame,
    fps,
    config: { damping: 14 },
  });

  const cards = [
    {
      icon: '🗺️',
      title: 'Mapa Interactivo de Pisos',
      desc: 'Semáforo en vivo de solvencias. Apartamentos Verdes (al día) y Rojos (con saldo adeudado y alícuota).',
      color: '#3b82f6',
      badge: 'Visual 2D',
    },
    {
      icon: '📈',
      title: 'Cobranza Bimoneda & BCV',
      desc: 'Presupuesto seguro en USD calculado automáticamente en Bolívares a la tasa oficial del día del BCV.',
      color: '#10b981',
      badge: 'BCV Oficial',
    },
    {
      icon: '📱',
      title: 'Autogestión del Residente',
      desc: 'Portal móvil 24/7 para consultar deuda, reportar pagos con capture y actualizar ficha familiar y vehículos.',
      color: '#8b5cf6',
      badge: 'Acceso 24/7',
    },
    {
      icon: '🛠️',
      title: 'Mesa de Averías y Cartelera',
      desc: 'Reporte de filtraciones y ascensores con fotos y seguimiento oficial. Cartelera digital para asambleas.',
      color: '#06b6d4',
      badge: 'Comunidad',
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
        padding: '120px 60px 90px',
        position: 'relative',
        zIndex: 1,
        fontFamily: THEME.fonts.main,
      }}
    >
      <Audio src={staticFile('whoosh.wav')} volume={0.8} />

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
          Ecosistema Residencial Inteligente
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
          4 Columnas de una Gestión Exitosa
        </h2>
      </div>

      {/* 4 Cards Vertical List */}
      <div
        style={{
          display: 'flex',
          flexDirection: 'column',
          gap: 20,
          width: '100%',
          maxWidth: 920,
        }}
      >
        {cards.map((c, idx) => {
          const delay = 8 + idx * 7;
          const cardSpring = spring({
            frame: frame - delay,
            fps,
            config: { damping: 14, mass: 0.8 },
          });

          return (
            <div
              key={idx}
              style={{
                transform: `scale(${Math.max(0, cardSpring)})`,
                background: 'rgba(15, 23, 42, 0.85)',
                border: `1px solid ${c.color}50`,
                borderRadius: 22,
                padding: '22px 28px',
                display: 'flex',
                alignItems: 'center',
                gap: 22,
                boxShadow: `0 12px 35px -5px ${c.color}20`,
              }}
            >
              {frame === delay && <Audio src={staticFile('pop.wav')} volume={0.65} />}

              <div
                style={{
                  width: 72,
                  height: 72,
                  borderRadius: 20,
                  background: `${c.color}20`,
                  border: `1px solid ${c.color}60`,
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: 36,
                  flexShrink: 0,
                }}
              >
                {c.icon}
              </div>

              <div style={{ flex: 1 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <h3 style={{ fontSize: 24, fontWeight: 800, color: '#fff', margin: 0 }}>
                    {c.title}
                  </h3>
                  <span
                    style={{
                      fontSize: 13,
                      fontWeight: 700,
                      color: c.color,
                      background: `${c.color}18`,
                      padding: '4px 12px',
                      borderRadius: 999,
                      border: `1px solid ${c.color}40`,
                    }}
                  >
                    {c.badge}
                  </span>
                </div>
                <p style={{ fontSize: 17, color: '#94a3b8', lineHeight: 1.45, margin: '8px 0 0' }}>
                  {c.desc}
                </p>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};
