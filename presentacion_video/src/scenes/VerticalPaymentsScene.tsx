import React from 'react';
import { Audio, spring, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const VerticalPaymentsScene: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const titleSpring = spring({
    frame,
    fps,
    config: { damping: 14 },
  });

  const modules = [
    {
      icon: '🎯',
      title: 'Cuotas Individuales por Unidad',
      desc: 'Emisión puntual a un apartamento específico (reparaciones, llaves/tags, multas o áreas). No afecta alícuotas del condominio.',
      badge: 'Apto Puntual',
      color: '#38bdf8',
      tag: 'Sin alterar alícuotas',
    },
    {
      icon: '💳',
      title: 'Reporte de Pago Individual',
      desc: 'El residente reporta vía Pago Móvil, Bancos (Banesco/Mercantil), Zelle o Efectivo con capture y cálculo USD vs Bs según tasa BCV.',
      badge: 'Bimoneda BCV',
      color: '#10b981',
      tag: 'Capture & Referencia',
    },
    {
      icon: '⚡',
      title: 'Gestión y Conciliación Administrativa',
      desc: 'Bandeja centralizada para la junta con visor de comprobantes bancarios, validación en 1 clic y emisión de Recibo Digital Oficial.',
      badge: 'Conciliación Ágil',
      color: '#8b5cf6',
      tag: 'Recibo Digital Inmediato',
    },
    {
      icon: '💰',
      title: 'Saldos a Favor y Auditoría 360°',
      desc: 'Abono automático de pagos excedentes, semáforo de solvencia en tiempo real, arqueo bimoneda e historial inmutable por unidad.',
      badge: 'Trazabilidad Total',
      color: '#f59e0b',
      tag: 'Acreditación Automática',
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
        padding: '100px 55px 75px',
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
          marginBottom: 24,
        }}
      >
        <span
          style={{
            fontSize: 16,
            fontWeight: 800,
            color: '#10b981',
            textTransform: 'uppercase',
            letterSpacing: '0.12em',
            background: 'rgba(16, 185, 129, 0.12)',
            padding: '6px 16px',
            borderRadius: 999,
            border: '1px solid rgba(16, 185, 129, 0.35)',
          }}
        >
          💼 Módulo Financiero Especializado
        </span>
        <h2
          style={{
            fontSize: 42,
            fontWeight: 900,
            color: '#fff',
            margin: '10px 0 0',
            letterSpacing: '-0.02em',
          }}
        >
          Pagos Individuales & Conciliación
        </h2>
        <p
          style={{
            fontSize: 18,
            color: '#94a3b8',
            margin: '6px 0 0',
          }}
        >
          Cobranza puntual, auditoría bancaria y recibos digitales
        </p>
      </div>

      {/* 4 Cards Vertical List */}
      <div
        style={{
          display: 'flex',
          flexDirection: 'column',
          gap: 16,
          width: '100%',
          maxWidth: 960,
        }}
      >
        {modules.map((item, idx) => {
          const delay = 6 + idx * 7;
          const cardSpring = spring({
            frame: frame - delay,
            fps,
            config: { damping: 14, mass: 0.85 },
          });

          return (
            <div
              key={idx}
              style={{
                transform: `scale(${Math.max(0, cardSpring)})`,
                background: 'linear-gradient(135deg, rgba(30, 41, 59, 0.65) 0%, rgba(15, 23, 42, 0.95) 100%)',
                border: `1px solid ${item.color}45`,
                borderRadius: 20,
                padding: '20px 24px',
                display: 'flex',
                alignItems: 'center',
                gap: 20,
                boxShadow: `0 12px 28px -6px ${item.color}25`,
              }}
            >
              {frame === delay && (
                <Audio src={staticFile(idx === 2 ? 'ding.wav' : 'pop.wav')} volume={idx === 2 ? 0.75 : 0.6} />
              )}
              <div
                style={{
                  width: 58,
                  height: 58,
                  borderRadius: 16,
                  background: `${item.color}20`,
                  border: `1px solid ${item.color}50`,
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: 28,
                  flexShrink: 0,
                }}
              >
                {item.icon}
              </div>

              <div style={{ flex: 1 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 4 }}>
                  <h3 style={{ fontSize: 21, fontWeight: 800, color: '#fff', margin: 0 }}>
                    {item.title}
                  </h3>
                  <span
                    style={{
                      fontSize: 11,
                      fontWeight: 800,
                      color: item.color,
                      textTransform: 'uppercase',
                      letterSpacing: '0.06em',
                      background: `${item.color}15`,
                      padding: '3px 8px',
                      borderRadius: 6,
                      border: `1px solid ${item.color}35`,
                    }}
                  >
                    {item.badge}
                  </span>
                </div>
                <p style={{ fontSize: 14.5, color: '#cbd5e1', lineHeight: 1.4, margin: '0 0 6px' }}>
                  {item.desc}
                </p>
                <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                  <span style={{ fontSize: 13, color: '#10b981' }}>✓</span>
                  <span style={{ fontSize: 12.5, fontWeight: 700, color: '#e2e8f0' }}>
                    {item.tag}
                  </span>
                </div>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};
