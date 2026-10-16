import React from 'react';
import { interpolate, spring, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const CoreFeaturesScene: React.FC = () => {
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
      subtitle: 'Semáforo de Solvencias',
      desc: 'Monitoreo visual piso por piso. Cada apartamento refleja en tiempo real su estatus: Solvente (Verde) o Moroso (Rojo) con ficha de deuda y alícuota calculada.',
      color: '#3b82f6',
      badge: 'Visual 2D',
    },
    {
      icon: '📈',
      title: 'Cobranza Bimoneda & Tasa BCV',
      subtitle: 'USD / Bs. Automatizados',
      desc: 'Presupuesto blindado en divisas con cálculo automático en Bolívares usando la tasa oficial del día del BCV. Canales de Pago Móvil, Cuentas Corrientes y Zelle.',
      color: '#10b981',
      badge: 'BCV Oficial',
    },
    {
      icon: '📱',
      title: 'Autogestión del Residente',
      subtitle: 'Portal Web Móvil 24/7',
      desc: 'Cada propietario consulta su balance exacto, reporta transferencias con captura bancaria y gestiona los datos de habitantes, mascotas y vehículos de su unidad.',
      color: '#8b5cf6',
      badge: 'Acceso 24/7',
    },
    {
      icon: '🛠️',
      title: 'Mesa de Averías y Cartelera',
      subtitle: 'Comunicación Institucional',
      desc: 'Reporte de filtraciones, ascensores y áreas comunes con fotografías y seguimiento oficial. Cartelera informativa digital para asambleas y comunicados.',
      color: '#06b6d4',
      badge: 'Transparencia',
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
        padding: '0 80px',
        position: 'relative',
        zIndex: 1,
        fontFamily: THEME.fonts.main,
      }}
    >
      {/* Title */}
      <div
        style={{
          transform: `scale(${titleSpring})`,
          textAlign: 'center',
          marginBottom: 44,
        }}
      >
        <span
          style={{
            fontSize: 16,
            fontWeight: 700,
            color: THEME.colors.primaryLight,
            textTransform: 'uppercase',
            letterSpacing: '0.12em',
          }}
        >
          Ecosistema Tecnológico CondoHub
        </span>
        <h2
          style={{
            fontSize: 48,
            fontWeight: 800,
            color: '#fff',
            margin: '8px 0 0',
            letterSpacing: '-0.02em',
          }}
        >
          Las 4 Columnas de una Gestión Exitosa
        </h2>
      </div>

      {/* 4 Cards Grid */}
      <div
        style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(4, 1fr)',
          gap: 24,
          width: '100%',
          maxWidth: 1500,
        }}
      >
        {cards.map((card, idx) => {
          const delay = 10 + idx * 8;
          const cardScale = spring({
            frame: frame - delay,
            fps,
            config: { damping: 14, mass: 0.9 },
          });

          const opacity = interpolate(frame, [delay, delay + 15], [0, 1], {
            extrapolateLeft: 'clamp',
            extrapolateRight: 'clamp',
          });

          return (
            <div
              key={idx}
              style={{
                transform: `scale(${Math.max(0, cardScale)})`,
                opacity,
                background: 'rgba(15, 23, 42, 0.75)',
                border: `1px solid ${card.color}40`,
                borderRadius: 20,
                padding: 28,
                display: 'flex',
                flexDirection: 'column',
                justifyContent: 'space-between',
                boxShadow: `0 15px 35px -5px ${card.color}25`,
                position: 'relative',
                overflow: 'hidden',
              }}
            >
              {/* Header inside card */}
              <div>
                <div
                  style={{
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                    marginBottom: 20,
                  }}
                >
                  <div
                    style={{
                      width: 52,
                      height: 52,
                      borderRadius: 14,
                      background: `${card.color}20`,
                      border: `1px solid ${card.color}50`,
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      fontSize: 26,
                    }}
                  >
                    {card.icon}
                  </div>
                  <span
                    style={{
                      fontSize: 12,
                      fontWeight: 700,
                      color: card.color,
                      background: `${card.color}15`,
                      padding: '4px 10px',
                      borderRadius: 999,
                      border: `1px solid ${card.color}35`,
                    }}
                  >
                    {card.badge}
                  </span>
                </div>

                <h3 style={{ fontSize: 22, fontWeight: 800, color: '#fff', margin: 0 }}>
                  {card.title}
                </h3>
                <div style={{ fontSize: 14, fontWeight: 600, color: card.color, marginTop: 4 }}>
                  {card.subtitle}
                </div>

                <p
                  style={{
                    fontSize: 15,
                    color: '#94a3b8',
                    lineHeight: 1.55,
                    marginTop: 16,
                  }}
                >
                  {card.desc}
                </p>
              </div>

              {/* Bottom accent glow bar */}
              <div
                style={{
                  height: 4,
                  width: '100%',
                  background: card.color,
                  borderRadius: 2,
                  marginTop: 20,
                  boxShadow: `0 0 12px ${card.color}`,
                }}
              />
            </div>
          );
        })}
      </div>
    </div>
  );
};
