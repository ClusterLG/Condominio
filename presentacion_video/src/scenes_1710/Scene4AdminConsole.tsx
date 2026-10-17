import React from 'react';
import { Audio, spring, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const Scene4AdminConsole: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const titleSpring = spring({
    frame,
    fps,
    config: { damping: 14 },
  });

  const consoleSpring = spring({
    frame: frame - 15,
    fps,
    config: { damping: 12, mass: 0.8 },
  });

  const buttonSpring = spring({
    frame: frame - 55,
    fps,
    config: { damping: 10, mass: 0.6, stiffness: 200 },
  });

  const receiptSpring = spring({
    frame: frame - 80,
    fps,
    config: { damping: 12, mass: 0.7 },
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
      {frame === 55 && <Audio src={staticFile('tick.wav')} volume={0.7} />}
      {frame === 80 && <Audio src={staticFile('ding.wav')} volume={0.85} />}

      {/* Header */}
      <div
        style={{
          transform: `scale(${Math.max(0, titleSpring)})`,
          textAlign: 'center',
          marginBottom: 20,
        }}
      >
        <span
          style={{
            fontSize: 15,
            fontWeight: 800,
            color: '#8b5cf6',
            textTransform: 'uppercase',
            letterSpacing: '0.12em',
            background: 'rgba(139, 92, 246, 0.15)',
            padding: '6px 18px',
            borderRadius: 999,
            border: '1px solid rgba(139, 92, 246, 0.4)',
          }}
        >
          ⚡ Consola de la Junta de Condominio
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
          Conciliación en 1 Solo Clic
        </h2>
        <p style={{ fontSize: 18, color: '#94a3b8', margin: '4px 0 0' }}>
          Auditoría de transferencias, saldos a favor y recibos oficiales
        </p>
      </div>

      {/* Admin Audit Console Card */}
      <div
        style={{
          transform: `scale(${Math.max(0, consoleSpring)})`,
          width: '100%',
          maxWidth: 960,
          background: 'linear-gradient(180deg, rgba(30, 41, 59, 0.8) 0%, rgba(15, 23, 42, 0.95) 100%)',
          borderRadius: 24,
          border: '1.5px solid rgba(139, 92, 246, 0.45)',
          padding: '24px 28px',
          boxShadow: '0 20px 50px rgba(0,0,0,0.5), 0 0 40px rgba(139, 92, 246, 0.2)',
          marginBottom: 16,
        }}
      >
        {/* Payment Detail Row */}
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 16 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <div
              style={{
                width: 44,
                height: 44,
                borderRadius: 12,
                background: 'rgba(139, 92, 246, 0.2)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: 22,
              }}
            >
              🔍
            </div>
            <div>
              <div style={{ fontSize: 18, fontWeight: 800, color: '#fff' }}>
                Pago Reportado: Apto 3-B (Pago Móvil)
              </div>
              <div style={{ fontSize: 13, color: '#94a3b8' }}>
                Ref: #84920194 • Banesco a Mercantil • $45.00 USD (Bs. 1.845,00)
              </div>
            </div>
          </div>
          <span
            style={{
              fontSize: 11,
              fontWeight: 800,
              background: 'rgba(245, 158, 11, 0.15)',
              color: '#f59e0b',
              padding: '5px 10px',
              borderRadius: 6,
              border: '1px solid rgba(245, 158, 11, 0.3)',
            }}
          >
            POR CONCILIAR
          </span>
        </div>

        {/* Saldos a favor automatic pill */}
        <div
          style={{
            background: 'rgba(59, 130, 246, 0.08)',
            border: '1px solid rgba(59, 130, 246, 0.25)',
            borderRadius: 12,
            padding: '10px 16px',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            marginBottom: 18,
          }}
        >
          <span style={{ fontSize: 14, color: '#93c5fd', fontWeight: 600 }}>
            💰 Manejo de Excedentes: El residente pagó $50.00 USD
          </span>
          <span style={{ fontSize: 13, color: '#38bdf8', fontWeight: 800 }}>
            +$5.00 Saldo a Favor Acreditado
          </span>
        </div>

        {/* Action Button: Conciliar */}
        <div
          style={{
            transform: `scale(${Math.max(0, buttonSpring)})`,
            background: frame >= 75
              ? 'linear-gradient(135deg, #059669, #10b981)'
              : 'linear-gradient(135deg, #2563eb, #8b5cf6)',
            padding: '16px 24px',
            borderRadius: 14,
            textAlign: 'center',
            color: '#fff',
            fontWeight: 900,
            fontSize: 19,
            letterSpacing: '0.04em',
            boxShadow: '0 8px 25px rgba(59, 130, 246, 0.4)',
            cursor: 'pointer',
            border: '1px solid rgba(255, 255, 255, 0.2)',
          }}
        >
          {frame >= 75 ? '✅ ¡PAGO CONCILIADO EXITOSAMENTE!' : '👉 VALIDAR Y APROBAR CON 1 CLIC'}
        </div>
      </div>

      {/* Generated Digital Receipt Card */}
      {frame >= 80 && (
        <div
          style={{
            transform: `scale(${Math.max(0, receiptSpring)})`,
            width: '100%',
            maxWidth: 960,
            background: 'rgba(16, 185, 129, 0.12)',
            border: '1.5px solid rgba(16, 185, 129, 0.5)',
            borderRadius: 18,
            padding: '14px 20px',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            boxShadow: '0 0 35px rgba(16, 185, 129, 0.3)',
          }}
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <span style={{ fontSize: 24 }}>📄</span>
            <div>
              <div style={{ fontSize: 15, fontWeight: 800, color: '#fff' }}>
                Recibo Digital Oficial No. 0482 Generado
              </div>
              <div style={{ fontSize: 12, color: '#a7f3d0' }}>
                Apartamento 3-B actualizado automáticamente a SOLVENTE
              </div>
            </div>
          </div>
          <span
            style={{
              fontSize: 13,
              fontWeight: 900,
              background: '#10b981',
              color: '#064e3b',
              padding: '6px 14px',
              borderRadius: 999,
            }}
          >
            SOLVENTE
          </span>
        </div>
      )}
    </div>
  );
};
