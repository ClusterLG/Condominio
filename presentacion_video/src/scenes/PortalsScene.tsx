import React from 'react';
import { spring, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const PortalsScene: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const titleSpring = spring({
    frame,
    fps,
    config: { damping: 14 },
  });

  const leftSpring = spring({
    frame: frame - 10,
    fps,
    config: { damping: 14, mass: 0.9 },
  });

  const rightSpring = spring({
    frame: frame - 20,
    fps,
    config: { damping: 14, mass: 0.9 },
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
            color: THEME.colors.primaryLight,
            textTransform: 'uppercase',
            letterSpacing: '0.12em',
          }}
        >
          Experiencia Diseñada para Cada Actor • Superadmin • Junta • Residentes
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
          Portales Especializados con Menú Vertical Retráctil
        </h2>
      </div>

      {/* Two Big Portal Cards */}
      <div
        style={{
          display: 'grid',
          gridTemplateColumns: '1fr 1fr',
          gap: 36,
          width: '100%',
          maxWidth: 1400,
        }}
      >
        {/* Left Portal: Junta / Administración */}
        <div
          style={{
            transform: `scale(${Math.max(0, leftSpring)})`,
            background: 'linear-gradient(180deg, rgba(30, 58, 138, 0.25) 0%, rgba(15, 23, 42, 0.9) 100%)',
            border: '1px solid rgba(59, 130, 246, 0.4)',
            borderRadius: 24,
            padding: 36,
            boxShadow: '0 20px 45px rgba(59, 130, 246, 0.15)',
            display: 'flex',
            flexDirection: 'column',
            justifyContent: 'space-between',
          }}
        >
          <div>
            <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 20 }}>
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
                  fontSize: 26,
                }}
              >
                🛡️
              </div>
              <div>
                <span
                  style={{
                    fontSize: 12,
                    fontWeight: 700,
                    color: '#60a5fa',
                    textTransform: 'uppercase',
                    letterSpacing: '0.08em',
                  }}
                >
                  Para la Gestión del Edificio
                </span>
                <h3 style={{ fontSize: 26, fontWeight: 800, color: '#fff', margin: 0 }}>
                  Portal de la Junta y Administración
                </h3>
              </div>
            </div>

            <div style={{ display: 'flex', flexDirection: 'column', gap: 14, marginTop: 10 }}>
              {[
                '🗺️ Mapa de Pisos Interactivo con semáforo de solvencias al día.',
                '💳 Emisión y reparto equitativo de cuotas ordinarias y extraordinarias.',
                '🔍 Bandeja de auditoría con visor de captures bancarios y aprobación en 1 clic.',
                '👤 Módulo de Perfil con datos personales y cambio seguro de contraseña.',
                '📂 Menú Vertical Retráctil con colapso animado, sin scroll horizontal.',
              ].map((item, idx) => (
                <div
                  key={idx}
                  style={{
                    fontSize: 16,
                    color: '#e2e8f0',
                    lineHeight: 1.45,
                    display: 'flex',
                    alignItems: 'center',
                    gap: 10,
                  }}
                >
                  <span>{item}</span>
                </div>
              ))}
            </div>
          </div>

          <div
            style={{
              marginTop: 24,
              padding: '12px 18px',
              background: 'rgba(59, 130, 246, 0.12)',
              border: '1px solid rgba(59, 130, 246, 0.3)',
              borderRadius: 12,
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'space-between',
            }}
          >
            <span style={{ fontSize: 14, color: '#93c5fd', fontWeight: 600 }}>
              Control Total de Finanzas, Perfil y Mantenimiento
            </span>
            <span style={{ fontSize: 13, color: '#60a5fa', fontWeight: 700 }}>
              100% Digital
            </span>
          </div>
        </div>

        {/* Right Portal: Copropietario / Residente */}
        <div
          style={{
            transform: `scale(${Math.max(0, rightSpring)})`,
            background: 'linear-gradient(180deg, rgba(5, 150, 105, 0.2) 0%, rgba(15, 23, 42, 0.9) 100%)',
            border: '1px solid rgba(16, 185, 129, 0.4)',
            borderRadius: 24,
            padding: 36,
            boxShadow: '0 20px 45px rgba(16, 185, 129, 0.15)',
            display: 'flex',
            flexDirection: 'column',
            justifyContent: 'space-between',
          }}
        >
          <div>
            <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 20 }}>
              <div
                style={{
                  width: 52,
                  height: 52,
                  borderRadius: 14,
                  background: 'rgba(16, 185, 129, 0.2)',
                  border: '1px solid rgba(16, 185, 129, 0.5)',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: 26,
                }}
              >
                📱
              </div>
              <div>
                <span
                  style={{
                    fontSize: 12,
                    fontWeight: 700,
                    color: '#34d399',
                    textTransform: 'uppercase',
                    letterSpacing: '0.08em',
                  }}
                >
                  Para Vecinos y Propietarios
                </span>
                <h3 style={{ fontSize: 26, fontWeight: 800, color: '#fff', margin: 0 }}>
                  Portal Móvil de Autogestión 24/7
                </h3>
              </div>
            </div>

            <div style={{ display: 'flex', flexDirection: 'column', gap: 14, marginTop: 10 }}>
              {[
                '💵 Consulta de saldo exacto en USD y Bolívares oficiales según BCV en vivo.',
                '🏦 Acceso directo a las cuentas bancarias oficiales (Pago Móvil, Cuentas, Zelle).',
                '📤 Reporte ágil de transferencias adjuntando el comprobante desde el celular.',
                '📂 Menú Lateral Retráctil adaptable a teléfonos, tablets y computadoras.',
                '📝 Reporte de averías comunitarias con fotos y respuesta directa de la junta.',
              ].map((item, idx) => (
                <div
                  key={idx}
                  style={{
                    fontSize: 16,
                    color: '#e2e8f0',
                    lineHeight: 1.45,
                    display: 'flex',
                    alignItems: 'center',
                    gap: 10,
                  }}
                >
                  <span>{item}</span>
                </div>
              ))}
            </div>
          </div>

          <div
            style={{
              marginTop: 24,
              padding: '12px 18px',
              background: 'rgba(16, 185, 129, 0.12)',
              border: '1px solid rgba(16, 185, 129, 0.3)',
              borderRadius: 12,
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'space-between',
            }}
          >
            <span style={{ fontSize: 14, color: '#a7f3d0', fontWeight: 600 }}>
              Sin Aplicaciones Pesadas • Navegación Lateral Ágil
            </span>
            <span style={{ fontSize: 13, color: '#34d399', fontWeight: 700 }}>
              Inmediato
            </span>
          </div>
        </div>
      </div>
    </div>
  );
};
