import React from 'react';
import { interpolate, spring, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const ProblemSolutionScene: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const titleSpring = spring({
    frame,
    fps,
    config: { damping: 14 },
  });

  const card1X = interpolate(frame, [10, 30], [-100, 0], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });
  const card1Opacity = interpolate(frame, [10, 30], [0, 1], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });

  const card2X = interpolate(frame, [25, 45], [100, 0], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });
  const card2Opacity = interpolate(frame, [25, 45], [0, 1], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
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
          Transformación Digital para tu Comunidad
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
          ¿Por Qué Cambiar el Modelo Tradicional?
        </h2>
      </div>

      {/* Two Column Cards Comparison */}
      <div
        style={{
          display: 'grid',
          gridTemplateColumns: '1fr 1fr',
          gap: 36,
          width: '100%',
          maxWidth: 1400,
        }}
      >
        {/* The Pain Points (Red / Danger) */}
        <div
          style={{
            transform: `translateX(${card1X}px)`,
            opacity: card1Opacity,
            background: 'rgba(239, 68, 68, 0.05)',
            border: '1px solid rgba(239, 68, 68, 0.25)',
            borderRadius: 24,
            padding: 36,
            boxShadow: '0 20px 40px rgba(0,0,0,0.3)',
          }}
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 24 }}>
            <div
              style={{
                width: 44,
                height: 44,
                borderRadius: 12,
                background: 'rgba(239, 68, 68, 0.15)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: 22,
              }}
            >
              ❌
            </div>
            <h3 style={{ fontSize: 26, fontWeight: 700, color: '#fca5a5', margin: 0 }}>
              Gestión Tradicional y Caótica
            </h3>
          </div>

          <div style={{ display: 'flex', flexDirection: 'column', gap: 18 }}>
            {[
              {
                title: 'Caos en Grupos de WhatsApp',
                desc: 'Comprobantes perdidos entre cientos de mensajes, reclamos y audios a deshoras.',
              },
              {
                title: 'Discusión Diaria por la Tasa de Cambio',
                desc: 'Desacuerdos constantes entre vecinos y junta por la tasa aplicable al pago.',
              },
              {
                title: 'Morosidad Oculta y Desconfianza',
                desc: 'Cuentas poco claras que generan dudas sobre el destino real de los fondos.',
              },
              {
                title: 'Averías Comunales Olvidadas',
                desc: 'Filtraciones y quejas en ascensores que quedan sin respuesta ni seguimiento.',
              },
            ].map((item, i) => (
              <div key={i} style={{ display: 'flex', gap: 14, alignItems: 'flex-start' }}>
                <span style={{ color: '#ef4444', fontSize: 20, marginTop: 2 }}>•</span>
                <div>
                  <strong style={{ color: '#fecaca', fontSize: 18 }}>{item.title}:</strong>
                  <div style={{ color: '#94a3b8', fontSize: 15, marginTop: 4 }}>{item.desc}</div>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* The CondoHub Solution (Green / Blue) */}
        <div
          style={{
            transform: `translateX(${card2X}px)`,
            opacity: card2Opacity,
            background: 'rgba(16, 185, 129, 0.06)',
            border: '1px solid rgba(16, 185, 129, 0.35)',
            borderRadius: 24,
            padding: 36,
            boxShadow: '0 20px 45px rgba(16, 185, 129, 0.12)',
          }}
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 24 }}>
            <div
              style={{
                width: 44,
                height: 44,
                borderRadius: 12,
                background: 'rgba(16, 185, 129, 0.2)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: 22,
              }}
            >
              ✅
            </div>
            <h3 style={{ fontSize: 26, fontWeight: 700, color: '#6ee7b7', margin: 0 }}>
              La Solución Integral CondoHub
            </h3>
          </div>

          <div style={{ display: 'flex', flexDirection: 'column', gap: 18 }}>
            {[
              {
                title: 'Bandeja Centralizada de Cobranza',
                desc: 'Cada pago con capture bancario, referencia y conciliación en 1 solo clic.',
              },
              {
                title: 'Tasa BCV Oficial Automatizada',
                desc: 'Cálculo al segundo en Bolívares con la fecha valor oficial del Banco Central.',
              },
              {
                title: 'Mapa Interactivo de Pisos y Solvencia',
                desc: 'Visualización clara piso por piso del estado del edificio con total transparencia.',
              },
              {
                title: 'Mesa Digital de Averías y Cartelera',
                desc: 'Tickets oficiales con estatus en vivo y comunicados directos a toda la comunidad.',
              },
            ].map((item, i) => (
              <div key={i} style={{ display: 'flex', gap: 14, alignItems: 'flex-start' }}>
                <span style={{ color: '#10b981', fontSize: 20, marginTop: 2 }}>✔</span>
                <div>
                  <strong style={{ color: '#d1fae5', fontSize: 18 }}>{item.title}:</strong>
                  <div style={{ color: '#cbd5e1', fontSize: 15, marginTop: 4 }}>{item.desc}</div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};
