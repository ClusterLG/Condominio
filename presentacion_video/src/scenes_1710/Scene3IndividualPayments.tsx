import React from 'react';
import { Audio, spring, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const Scene3IndividualPayments: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const titleSpring = spring({
    frame,
    fps,
    config: { damping: 14 },
  });

  const card1Spring = spring({
    frame: frame - 15,
    fps,
    config: { damping: 13, mass: 0.8 },
  });

  const card2Spring = spring({
    frame: frame - 45,
    fps,
    config: { damping: 13, mass: 0.8 },
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
        padding: '110px 50px 90px',
        position: 'relative',
        zIndex: 1,
        fontFamily: THEME.fonts.main,
      }}
    >
      {/* Audio SFX */}
      {frame === 0 && <Audio src={staticFile('whoosh.wav')} volume={0.8} />}
      {frame === 15 && <Audio src={staticFile('pop.wav')} volume={0.7} />}
      {frame === 45 && <Audio src={staticFile('pop.wav')} volume={0.7} />}
      {frame === 75 && <Audio src={staticFile('tick.wav')} volume={0.65} />}
      {frame === 105 && <Audio src={staticFile('ding.wav')} volume={0.75} />}

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
            color: '#38bdf8',
            textTransform: 'uppercase',
            letterSpacing: '0.12em',
            background: 'rgba(56, 189, 248, 0.12)',
            padding: '6px 16px',
            borderRadius: 999,
            border: '1px solid rgba(56, 189, 248, 0.3)',
          }}
        >
          🎯 Flexibilidad Financiera Total
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
          Pagos y Cuotas Individuales
        </h2>
        <p style={{ fontSize: 18, color: '#94a3b8', margin: '4px 0 0' }}>
          Cargos exclusivos sin alterar la alícuota general del edificio
        </p>
      </div>

      {/* 2 Main Stacked Feature Cards */}
      <div
        style={{
          display: 'flex',
          flexDirection: 'column',
          gap: 20,
          width: '100%',
          maxWidth: 960,
        }}
      >
        {/* Card 1: Emisión de Cuota Individual */}
        <div
          style={{
            transform: `scale(${Math.max(0, card1Spring)})`,
            background: 'linear-gradient(135deg, rgba(30, 41, 59, 0.75) 0%, rgba(15, 23, 42, 0.95) 100%)',
            border: '1.5px solid rgba(56, 189, 248, 0.45)',
            borderRadius: 22,
            padding: '22px 26px',
            boxShadow: '0 16px 36px -8px rgba(56, 189, 248, 0.25)',
          }}
        >
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 12 }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
              <div
                style={{
                  width: 48,
                  height: 48,
                  borderRadius: 14,
                  background: 'rgba(56, 189, 248, 0.2)',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: 24,
                }}
              >
                🎯
              </div>
              <div>
                <span style={{ fontSize: 12, fontWeight: 800, color: '#38bdf8', textTransform: 'uppercase' }}>
                  Emisión Exclusiva por Unidad
                </span>
                <h3 style={{ fontSize: 22, fontWeight: 800, color: '#fff', margin: 0 }}>
                  Apto 4B: Reparación Particular ($15.00 USD)
                </h3>
              </div>
            </div>
            <span
              style={{
                fontSize: 12,
                fontWeight: 800,
                color: '#10b981',
                background: 'rgba(16, 185, 129, 0.15)',
                border: '1px solid rgba(16, 185, 129, 0.35)',
                padding: '4px 10px',
                borderRadius: 6,
              }}
            >
              ALÍCUOTAS PROTEGIDAS
            </span>
          </div>
          <p style={{ fontSize: 15, color: '#cbd5e1', lineHeight: 1.45, margin: 0 }}>
            Asigna cobros puntuales por llaves, salones de fiesta, multas o averías privadas sin alterar el prorrateo de los demás 49 copropietarios.
          </p>
        </div>

        {/* Card 2: Autogestión y Métodos de Pago */}
        <div
          style={{
            transform: `scale(${Math.max(0, card2Spring)})`,
            background: 'linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(15, 23, 42, 0.95) 100%)',
            border: '1.5px solid rgba(16, 185, 129, 0.45)',
            borderRadius: 22,
            padding: '22px 26px',
            boxShadow: '0 16px 36px -8px rgba(16, 185, 129, 0.25)',
          }}
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 14 }}>
            <div
              style={{
                width: 48,
                height: 48,
                borderRadius: 14,
                background: 'rgba(16, 185, 129, 0.25)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: 24,
              }}
            >
              📱
            </div>
            <div>
              <span style={{ fontSize: 12, fontWeight: 800, color: '#34d399', textTransform: 'uppercase' }}>
                Portal del Residente
              </span>
              <h3 style={{ fontSize: 22, fontWeight: 800, color: '#fff', margin: 0 }}>
                Reporte de Pago Multicanal 24/7
              </h3>
            </div>
          </div>

          {/* 4 Payment Methods Badges */}
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 10, marginBottom: 12 }}>
            {[
              { name: 'Pago Móvil', icon: '📲', tag: 'Banesco/Mercantil' },
              { name: 'Transferencia', icon: '🏦', tag: 'Bolívares BCV' },
              { name: 'Zelle', icon: '⚡', tag: 'Divisas USD' },
              { name: 'Efectivo', icon: '💵', tag: 'Registro Directo' },
            ].map((m, idx) => (
              <div
                key={idx}
                style={{
                  background: 'rgba(255, 255, 255, 0.05)',
                  border: '1px solid rgba(255, 255, 255, 0.1)',
                  borderRadius: 10,
                  padding: '8px 6px',
                  textAlign: 'center',
                }}
              >
                <div style={{ fontSize: 18 }}>{m.icon}</div>
                <div style={{ fontSize: 12, fontWeight: 800, color: '#fff', marginTop: 2 }}>{m.name}</div>
                <div style={{ fontSize: 9.5, color: '#94a3b8' }}>{m.tag}</div>
              </div>
            ))}
          </div>

          <div
            style={{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'space-between',
              background: 'rgba(16, 185, 129, 0.1)',
              padding: '8px 14px',
              borderRadius: 8,
              border: '1px solid rgba(16, 185, 129, 0.25)',
            }}
          >
            <span style={{ fontSize: 13, color: '#a7f3d0', fontWeight: 600 }}>
              📸 Subida de capture bancario + cálculo de tasa BCV en 1 paso
            </span>
            <span style={{ fontSize: 13, color: '#34d399', fontWeight: 800 }}>
              INMEDIATO
            </span>
          </div>
        </div>
      </div>
    </div>
  );
};
