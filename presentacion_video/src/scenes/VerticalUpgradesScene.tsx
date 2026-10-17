import React from 'react';
import { Audio, spring, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const VerticalUpgradesScene: React.FC = () => {
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
      desc: 'Drawer lateral interactivo y responsive en los portales de gestión y residentes con colapso táctil y memoria local. Cero scroll innecesario.',
      badge: 'UX 2.0',
      color: '#3b82f6',
    },
    {
      icon: '🛡️',
      title: 'Blindaje de Alícuotas',
      desc: 'Protección estricta según documento de condominio y Ley de Propiedad Horizontal. Bloqueo de cambios no autorizados y validación en servidor.',
      badge: 'Seguridad Legal',
      color: '#f59e0b',
    },
    {
      icon: '👤',
      title: 'Perfil del Administrador',
      desc: 'Módulo de autogestión de datos de contacto y actualización segura de credenciales con verificación obligatoria de la clave actual.',
      badge: 'Acceso Seguro',
      color: '#8b5cf6',
    },
    {
      icon: '🏢',
      title: 'Multi-Condominio en Vivo',
      desc: 'Gestión simultánea de torres y conjuntos residenciales independientes con contabilidad aislada y tasa BCV automatizada en vivo.',
      badge: 'SaaS Escalable',
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
        padding: '110px 55px 85px',
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
          marginBottom: 30,
        }}
      >
        <span
          style={{
            fontSize: 16,
            fontWeight: 800,
            color: '#38bdf8',
            textTransform: 'uppercase',
            letterSpacing: '0.12em',
            background: 'rgba(56, 189, 248, 0.12)',
            padding: '6px 16px',
            borderRadius: 999,
            border: '1px solid rgba(56, 189, 248, 0.3)',
          }}
        >
          ✨ Evolución Continua
        </span>
        <h2
          style={{
            fontSize: 44,
            fontWeight: 900,
            color: '#fff',
            margin: '10px 0 0',
            letterSpacing: '-0.02em',
          }}
        >
          Nuevas Mejoras del Sistema
        </h2>
      </div>

      {/* 4 Cards Vertical List */}
      <div
        style={{
          display: 'flex',
          flexDirection: 'column',
          gap: 18,
          width: '100%',
          maxWidth: 920,
        }}
      >
        {upgrades.map((item, idx) => {
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
                background: 'rgba(15, 23, 42, 0.9)',
                border: `1px solid ${item.color}55`,
                borderRadius: 22,
                padding: '20px 24px',
                display: 'flex',
                alignItems: 'center',
                gap: 20,
                boxShadow: `0 14px 35px -5px ${item.color}25`,
              }}
            >
              {frame === delay && <Audio src={staticFile('pop.wav')} volume={0.65} />}

              <div
                style={{
                  width: 66,
                  height: 66,
                  borderRadius: 18,
                  background: `${item.color}20`,
                  border: `1px solid ${item.color}60`,
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: 32,
                  flexShrink: 0,
                }}
              >
                {item.icon}
              </div>

              <div style={{ flex: 1 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <h3 style={{ fontSize: 23, fontWeight: 800, color: '#fff', margin: 0 }}>
                    {item.title}
                  </h3>
                  <span
                    style={{
                      fontSize: 12,
                      fontWeight: 700,
                      color: item.color,
                      background: `${item.color}18`,
                      padding: '4px 10px',
                      borderRadius: 999,
                      border: `1px solid ${item.color}40`,
                    }}
                  >
                    {item.badge}
                  </span>
                </div>
                <p style={{ fontSize: 16, color: '#94a3b8', lineHeight: 1.45, margin: '6px 0 0' }}>
                  {item.desc}
                </p>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};
