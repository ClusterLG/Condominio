import React from 'react';
import { Audio, spring, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import { THEME } from '../theme';

export const Scene5FloorMap: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const titleSpring = spring({
    frame,
    fps,
    config: { damping: 14 },
  });

  const towerSpring = spring({
    frame: frame - 15,
    fps,
    config: { damping: 13, mass: 0.8 },
  });

  const modalSpring = spring({
    frame: frame - 65,
    fps,
    config: { damping: 11, mass: 0.7, stiffness: 180 },
  });

  // Simulated 6 floors sample for fast mobile rendering
  const sampleFloors = [
    { floor: 'Piso 12', apts: [{ code: '12A', status: 'solvente' }, { code: '12B', status: 'solvente' }, { code: '12C', status: 'favor' }, { code: '12D', status: 'solvente' }] },
    { floor: 'Piso 10', apts: [{ code: '10A', status: 'solvente', active: true }, { code: '10B', status: 'solvente' }, { code: '10C', status: 'solvente' }, { code: '10D', status: 'pendiente' }] },
    { floor: 'Piso 7', apts: [{ code: '7A', status: 'solvente' }, { code: '7B', status: 'pendiente' }, { code: '7C', status: 'solvente' }, { code: '7D', status: 'solvente' }] },
    { floor: 'Piso 4', apts: [{ code: '4A', status: 'favor' }, { code: '4B', status: 'solvente' }, { code: '4C', status: 'solvente' }, { code: '4D', status: 'solvente' }] },
    { floor: 'Piso 1', apts: [{ code: '1A', status: 'solvente' }, { code: '1B', status: 'solvente' }, { code: '1C', status: 'solvente' }, { code: '1D', status: 'solvente' }] },
    { floor: 'Planta Baja', apts: [{ code: 'PB-A', status: 'solvente' }, { code: 'PB-B', status: 'solvente' }] },
  ];

  const getStatusColor = (status: string) => {
    if (status === 'solvente') return { bg: 'rgba(16, 185, 129, 0.25)', border: '#10b981', text: '#34d399' };
    if (status === 'pendiente') return { bg: 'rgba(239, 68, 68, 0.25)', border: '#ef4444', text: '#f87171' };
    return { bg: 'rgba(56, 189, 248, 0.25)', border: '#38bdf8', text: '#7dd3fc' }; // saldo a favor
  };

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
      {frame === 20 && <Audio src={staticFile('tick.wav')} volume={0.65} />}
      {frame === 35 && <Audio src={staticFile('tick.wav')} volume={0.65} />}
      {frame === 65 && <Audio src={staticFile('pop.wav')} volume={0.75} />}
      {frame === 90 && <Audio src={staticFile('ding.wav')} volume={0.7} />}

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
            color: '#10b981',
            textTransform: 'uppercase',
            letterSpacing: '0.12em',
            background: 'rgba(16, 185, 129, 0.12)',
            padding: '6px 18px',
            borderRadius: 999,
            border: '1px solid rgba(16, 185, 129, 0.35)',
          }}
        >
          🗺️ Monitoreo Visual 2D en Vivo
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
          Mapa Interactivo de Pisos
        </h2>
        <p style={{ fontSize: 18, color: '#94a3b8', margin: '4px 0 0' }}>
          Semáforo de solvencia en tiempo real para cada apartamento
        </p>
      </div>

      {/* Simulated Tower Container */}
      <div
        style={{
          transform: `scale(${Math.max(0, towerSpring)})`,
          width: '100%',
          maxWidth: 960,
          background: 'linear-gradient(180deg, rgba(30, 41, 59, 0.75) 0%, rgba(15, 23, 42, 0.95) 100%)',
          borderRadius: 24,
          border: '1.5px solid rgba(59, 130, 246, 0.4)',
          padding: '20px 24px',
          boxShadow: '0 20px 50px rgba(0,0,0,0.5)',
          marginBottom: 16,
        }}
      >
        {/* Legend */}
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 20, marginBottom: 14 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 13, color: '#34d399', fontWeight: 700 }}>
            <span style={{ width: 10, height: 10, borderRadius: '50%', background: '#10b981' }} /> Solvente
          </div>
          <div style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 13, color: '#f87171', fontWeight: 700 }}>
            <span style={{ width: 10, height: 10, borderRadius: '50%', background: '#ef4444' }} /> Moroso
          </div>
          <div style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 13, color: '#7dd3fc', fontWeight: 700 }}>
            <span style={{ width: 10, height: 10, borderRadius: '50%', background: '#38bdf8' }} /> Saldo a Favor
          </div>
        </div>

        {/* Floors Grid */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
          {sampleFloors.map((f, fIdx) => (
            <div key={fIdx} style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
              <span style={{ width: 90, fontSize: 13, fontWeight: 700, color: '#94a3b8' }}>
                {f.floor}
              </span>
              <div style={{ display: 'flex', gap: 8, flex: 1 }}>
                {f.apts.map((apt, aIdx) => {
                  const style = getStatusColor(apt.status);
                  const isHighlighted = apt.active && frame >= 60;
                  return (
                    <div
                      key={aIdx}
                      style={{
                        flex: 1,
                        padding: '8px 4px',
                        borderRadius: 8,
                        background: isHighlighted ? 'rgba(56, 189, 248, 0.45)' : style.bg,
                        border: `1.5px solid ${isHighlighted ? '#38bdf8' : style.border}`,
                        color: isHighlighted ? '#fff' : style.text,
                        fontSize: 13,
                        fontWeight: 800,
                        textAlign: 'center',
                        boxShadow: isHighlighted ? '0 0 16px rgba(56, 189, 248, 0.8)' : 'none',
                        transform: isHighlighted ? 'scale(1.08)' : 'scale(1)',
                        transition: 'all 0.2s',
                      }}
                    >
                      {apt.code}
                    </div>
                  );
                })}
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Pop-up detail card on touch */}
      {frame >= 65 && (
        <div
          style={{
            transform: `scale(${Math.max(0, modalSpring)})`,
            width: '100%',
            maxWidth: 960,
            background: 'linear-gradient(135deg, rgba(30, 58, 138, 0.85) 0%, rgba(15, 23, 42, 0.98) 100%)',
            border: '2px solid #38bdf8',
            borderRadius: 18,
            padding: '16px 22px',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            boxShadow: '0 0 35px rgba(56, 189, 248, 0.4)',
          }}
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
            <span style={{ fontSize: 26 }}>🏢</span>
            <div>
              <div style={{ fontSize: 17, fontWeight: 900, color: '#fff' }}>
                Ficha Apto 10-A • Residencias Los Samanes
              </div>
              <div style={{ fontSize: 13, color: '#93c5fd' }}>
                Alícuota: 2.00% • Deuda: $0.00 USD (Solvente) • Propietario Verificado
              </div>
            </div>
          </div>
          <span
            style={{
              fontSize: 12,
              fontWeight: 800,
              background: '#10b981',
              color: '#064e3b',
              padding: '6px 14px',
              borderRadius: 6,
            }}
          >
            AL DÍA
          </span>
        </div>
      )}
    </div>
  );
};
