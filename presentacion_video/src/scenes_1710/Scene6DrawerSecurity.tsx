import React from 'react';
import { Audio, spring, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const Scene6DrawerSecurity: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const titleSpring = spring({
    frame,
    fps,
    config: { damping: 14 },
  });

  const cards = [
    {
      icon: '📐',
      title: 'Menú Vertical Retráctil',
      subtitle: 'Sidebar Drawer Responsive',
      desc: 'Navegación colapsable con memoria en localStorage para los 3 roles (Admin, Superadmin y Residentes). Cero scroll molesto.',
      badge: 'UX 2.0',
      color: '#3b82f6',
      delay: 15,
    },
    {
      icon: '🛡️',
      title: 'Blindaje de Alícuotas',
      subtitle: 'Seguridad Legal y Financiera',
      desc: 'Protección estricta según Ley de Propiedad Horizontal. Edición exclusiva para Superadmin y solo lectura para el administrador.',
      badge: 'Ley de Propiedad',
      color: '#f59e0b',
      delay: 45,
    },
    {
      icon: '👤',
      title: 'Perfil del Administrador',
      subtitle: 'Autogestión de Seguridad',
      desc: 'Actualización de teléfonos y datos de contacto de la junta con cambio seguro de contraseña que valida la clave actual.',
      badge: 'Acceso Seguro',
      color: '#8b5cf6',
      delay: 75,
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
        padding: '110px 50px 90px',
        position: 'relative',
        zIndex: 1,
        fontFamily: THEME.fonts.main,
      }}
    >
      {/* Audio SFX */}
      {frame === 0 && <Audio src={staticFile('whoosh.wav')} volume={0.8} />}
      {frame === 15 && <Audio src={staticFile('pop.wav')} volume={0.7} />}
      {frame === 45 && <Audio src={staticFile('tick.wav')} volume={0.7} />}
      {frame === 75 && <Audio src={staticFile('ding.wav')} volume={0.75} />}

      {/* Header */}
      <div
        style={{
          transform: `scale(${Math.max(0, titleSpring)})`,
          textAlign: 'center',
          marginBottom: 24,
        }}
      >
        <span
          style={{
            fontSize: 15,
            fontWeight: 800,
            color: '#f59e0b',
            textTransform: 'uppercase',
            letterSpacing: '0.12em',
            background: 'rgba(245, 158, 11, 0.12)',
            padding: '6px 18px',
            borderRadius: 999,
            border: '1px solid rgba(245, 158, 11, 0.35)',
          }}
        >
          🔒 Seguridad y Usabilidad
        </span>
        <h2
          style={{
            fontSize: 44,
            fontWeight: 900,
            color: '#fff',
            margin: '8px 0 0',
            letterSpacing: '-0.02em',
          }}
        >
          Navegación Ágil y Blindaje Legal
        </h2>
        <p style={{ fontSize: 18, color: '#94a3b8', margin: '4px 0 0' }}>
          Tecnología pensada para resolver los vacíos de las apps antiguas
        </p>
      </div>

      {/* 3 Vertical Feature Cards */}
      <div
        style={{
          display: 'flex',
          flexDirection: 'column',
          gap: 16,
          width: '100%',
          maxWidth: 960,
        }}
      >
        {cards.map((card, idx) => {
          const cardSpring = spring({
            frame: frame - card.delay,
            fps,
            config: { damping: 13, mass: 0.8 },
          });

          return (
            <div
              key={idx}
              style={{
                transform: `scale(${Math.max(0, cardSpring)})`,
                background: 'linear-gradient(135deg, rgba(30, 41, 59, 0.7) 0%, rgba(15, 23, 42, 0.95) 100%)',
                border: `1.5px solid ${card.color}45`,
                borderRadius: 20,
                padding: '20px 24px',
                display: 'flex',
                alignItems: 'center',
                gap: 20,
                boxShadow: `0 12px 28px -6px ${card.color}25`,
              }}
            >
              <div
                style={{
                  width: 56,
                  height: 56,
                  borderRadius: 16,
                  background: `${card.color}20`,
                  border: `1px solid ${card.color}50`,
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: 28,
                  flexShrink: 0,
                }}
              >
                {card.icon}
              </div>

              <div style={{ flex: 1 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 4 }}>
                  <h3 style={{ fontSize: 21, fontWeight: 800, color: '#fff', margin: 0 }}>
                    {card.title}
                  </h3>
                  <span
                    style={{
                      fontSize: 11,
                      fontWeight: 800,
                      color: card.color,
                      textTransform: 'uppercase',
                      letterSpacing: '0.06em',
                      background: `${card.color}15`,
                      padding: '3px 8px',
                      borderRadius: 6,
                      border: `1px solid ${card.color}35`,
                    }}
                  >
                    {card.badge}
                  </span>
                </div>
                <p style={{ fontSize: 14.5, color: '#cbd5e1', lineHeight: 1.4, margin: 0 }}>
                  {card.desc}
                </p>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};
