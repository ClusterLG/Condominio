import React from 'react';
import { interpolate, spring, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const PaymentsScene: React.FC = () => {
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
      title: 'Cuotas Individuales',
      subtitle: 'Cargos Puntuales por Unidad',
      desc: 'Emisión de cobros dirigidos exclusivamente a un apartamento (reparaciones particulares, llaves/tags, multas o salones). No altera la alícuota general del edificio.',
      badge: 'Apto Específico',
      color: '#38bdf8',
      tag: 'Sin alterar alícuotas',
    },
    {
      icon: '💳',
      title: 'Reporte de Pago Individual',
      subtitle: 'Autogestión Multicanal 24/7',
      desc: 'El residente reporta pagos en Pago Móvil, Transferencia (Banesco/Mercantil), Zelle o Divisas, adjunta comprobante y calcula automáticamente USD vs Bs a tasa BCV.',
      badge: 'Bimoneda BCV',
      color: '#10b981',
      tag: 'Capture & Referencia',
    },
    {
      icon: '⚡',
      title: 'Gestión y Conciliación',
      subtitle: 'Aprobación Administrativa en 1 Clic',
      desc: 'Bandeja centralizada para la junta con visor de comprobantes bancarios, validación inmediata, aprobación con un clic y emisión automática de Recibo Digital Oficial.',
      badge: 'Conciliación Ágil',
      color: '#8b5cf6',
      tag: 'Recibo Digital Inmediato',
    },
    {
      icon: '💰',
      title: 'Saldos a Favor y Auditoría',
      subtitle: 'Control Contable y Mora Cero',
      desc: 'Acreditación automática de excedentes y pagos anticipados, semáforo de solvencia en tiempo real, arqueo bimoneda e historial inmutable por cada copropietario.',
      badge: 'Trazabilidad 360°',
      color: '#f59e0b',
      tag: 'Abono Automático',
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
      {/* Title Header */}
      <div
        style={{
          transform: `scale(${titleSpring})`,
          textAlign: 'center',
          marginBottom: 36,
        }}
      >
        <span
          style={{
            fontSize: 16,
            fontWeight: 800,
            color: '#10b981',
            textTransform: 'uppercase',
            letterSpacing: '0.14em',
            background: 'rgba(16, 185, 129, 0.12)',
            padding: '6px 18px',
            borderRadius: 999,
            border: '1px solid rgba(16, 185, 129, 0.35)',
          }}
        >
          💼 Módulo Financiero Especializado
        </span>
        <h2
          style={{
            fontSize: 46,
            fontWeight: 900,
            color: '#fff',
            margin: '12px 0 0',
            letterSpacing: '-0.02em',
          }}
        >
          Pagos Individuales y Gestión Administrativa de Cobranza
        </h2>
        <p
          style={{
            fontSize: 18,
            color: '#94a3b8',
            margin: '8px 0 0',
            maxWidth: 1000,
          }}
        >
          Control milimétrico desde la emisión de cuotas exclusivas hasta la conciliación bancaria y recibo digital
        </p>
      </div>

      {/* 4 Cards Grid */}
      <div
        style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(4, 1fr)',
          gap: 24,
          width: '100%',
          maxWidth: 1560,
        }}
      >
        {modules.map((item, idx) => {
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
                background: 'linear-gradient(180deg, rgba(30, 41, 59, 0.6) 0%, rgba(15, 23, 42, 0.95) 100%)',
                border: `1px solid ${item.color}40`,
                borderRadius: 22,
                padding: '28px 24px',
                display: 'flex',
                flexDirection: 'column',
                justifyContent: 'space-between',
                boxShadow: `0 16px 36px -8px ${item.color}25`,
                position: 'relative',
                overflow: 'hidden',
              }}
            >
              {/* Top ambient highlight */}
              <div
                style={{
                  position: 'absolute',
                  top: 0,
                  left: 0,
                  right: 0,
                  height: 4,
                  background: `linear-gradient(to right, ${item.color}, transparent)`,
                }}
              />

              <div>
                <div
                  style={{
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between',
                    marginBottom: 16,
                  }}
                >
                  <div
                    style={{
                      width: 52,
                      height: 52,
                      borderRadius: 14,
                      background: `${item.color}20`,
                      border: `1px solid ${item.color}50`,
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      fontSize: 26,
                    }}
                  >
                    {item.icon}
                  </div>
                  <span
                    style={{
                      fontSize: 11,
                      fontWeight: 800,
                      color: item.color,
                      textTransform: 'uppercase',
                      letterSpacing: '0.08em',
                      background: `${item.color}15`,
                      padding: '4px 10px',
                      borderRadius: 6,
                      border: `1px solid ${item.color}35`,
                    }}
                  >
                    {item.badge}
                  </span>
                </div>

                <h3
                  style={{
                    fontSize: 22,
                    fontWeight: 800,
                    color: '#fff',
                    margin: '0 0 4px',
                  }}
                >
                  {item.title}
                </h3>
                <h4
                  style={{
                    fontSize: 13,
                    fontWeight: 600,
                    color: item.color,
                    margin: '0 0 12px',
                  }}
                >
                  {item.subtitle}
                </h4>

                <p
                  style={{
                    fontSize: 14,
                    color: '#cbd5e1',
                    lineHeight: 1.5,
                    margin: 0,
                  }}
                >
                  {item.desc}
                </p>
              </div>

              {/* Bottom Feature Pill */}
              <div
                style={{
                  marginTop: 20,
                  padding: '8px 12px',
                  background: 'rgba(255, 255, 255, 0.04)',
                  borderRadius: 10,
                  border: '1px solid rgba(255, 255, 255, 0.08)',
                  display: 'flex',
                  alignItems: 'center',
                  gap: 8,
                }}
              >
                <span style={{ fontSize: 13, color: '#10b981' }}>✓</span>
                <span
                  style={{
                    fontSize: 12,
                    fontWeight: 700,
                    color: '#e2e8f0',
                  }}
                >
                  {item.tag}
                </span>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};
