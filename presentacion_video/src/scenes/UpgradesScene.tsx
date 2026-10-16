import React from 'react';
import { interpolate, spring, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const UpgradesScene: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const titleSpring = spring({
    frame,
    fps,
    config: { damping: 14 },
  });

  const upgrades = [
    {
      icon: '📐',
      title: 'Menú Vertical Retráctil',
      subtitle: 'Sidebar Drawer Responsive',
      desc: 'Navegación lateral colapsable con memoria en localStorage para los 3 roles (Admin, Superadmin y Residentes). Adiós al scroll horizontal.',
      badge: 'UX 2.0 Unificada',
      color: '#3b82f6',
    },
    {
      icon: '🛡️',
      title: 'Blindaje de Alícuotas',
      subtitle: 'Seguridad Legal y Financiera',
      desc: 'Edición restringida exclusivamente al Superadmin según documento de condominio. Bloqueada en modo lectura para admins y validada en backend.',
      badge: 'Ley de Propiedad',
      color: '#f59e0b',
    },
    {
      icon: '👤',
      title: 'Perfil de Administrador',
      subtitle: 'Gestión y Cambio de Clave',
      desc: 'Módulo de autogestión de datos de contacto (C.I., teléfonos, cargo) y cambio de contraseña protegido que verifica la clave actual.',
      badge: 'Seguridad de Acceso',
      color: '#8b5cf6',
    },
    {
      icon: '🏢',
      title: 'Multi-Condominio en Vivo',
      subtitle: 'Aislamiento y Conmutación',
      desc: 'Soporte de múltiples edificios simultáneos con finanzas independientes, switch instantáneo en Superadmin y tasa BCV oficial automatizada.',
      badge: 'Arquitectura SaaS',
      color: '#10b981',
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
          marginBottom: 40,
        }}
      >
        <span
          style={{
            fontSize: 16,
            fontWeight: 700,
            color: '#38bdf8',
            textTransform: 'uppercase',
            letterSpacing: '0.14em',
            background: 'rgba(56, 189, 248, 0.12)',
            padding: '6px 16px',
            borderRadius: 999,
            border: '1px solid rgba(56, 189, 248, 0.3)',
          }}
        >
          ✨ Evolución Continua del Sistema
        </span>
        <h2
          style={{
            fontSize: 48,
            fontWeight: 800,
            color: '#fff',
            margin: '12px 0 0',
            letterSpacing: '-0.02em',
          }}
        >
          Nuevas Mejoras de Seguridad y Experiencia de Usuario
        </h2>
      </div>

      {/* 4 Upgrades Cards */}
      <div
        style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(4, 1fr)',
          gap: 24,
          width: '100%',
          maxWidth: 1540,
        }}
      >
        {upgrades.map((item, idx) => {
          const delay = 8 + idx * 8;
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
                background: 'linear-gradient(180deg, rgba(15, 23, 42, 0.9) 0%, rgba(11, 15, 25, 0.95) 100%)',
                border: `1px solid ${item.color}50`,
                borderRadius: 22,
                padding: 28,
                display: 'flex',
                flexDirection: 'column',
                justifyContent: 'space-between',
                boxShadow: `0 18px 40px -10px ${item.color}30`,
                position: 'relative',
                overflow: 'hidden',
              }}
            >
              {/* Top Card Section */}
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
                      width: 54,
                      height: 54,
                      borderRadius: 16,
                      background: `${item.color}20`,
                      border: `1px solid ${item.color}60`,
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      fontSize: 28,
                    }}
                  >
                    {item.icon}
                  </div>
                  <span
                    style={{
                      fontSize: 12,
                      fontWeight: 700,
                      color: item.color,
                      background: `${item.color}15`,
                      padding: '4px 12px',
                      borderRadius: 999,
                      border: `1px solid ${item.color}40`,
                    }}
                  >
                    {item.badge}
                  </span>
                </div>

                <h3 style={{ fontSize: 22, fontWeight: 800, color: '#fff', margin: 0 }}>
                  {item.title}
                </h3>
                <div style={{ fontSize: 14, fontWeight: 600, color: item.color, marginTop: 4 }}>
                  {item.subtitle}
                </div>

                <p
                  style={{
                    fontSize: 15,
                    color: '#94a3b8',
                    lineHeight: 1.55,
                    marginTop: 16,
                  }}
                >
                  {item.desc}
                </p>
              </div>

              {/* Bottom decorative glow accent */}
              <div
                style={{
                  height: 4,
                  width: '100%',
                  background: item.color,
                  borderRadius: 2,
                  marginTop: 20,
                  boxShadow: `0 0 14px ${item.color}`,
                }}
              />
            </div>
          );
        })}
      </div>
    </div>
  );
};
