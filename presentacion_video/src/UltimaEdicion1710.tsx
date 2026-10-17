import React from 'react';
import { Audio, Composition, Series, staticFile, useCurrentFrame } from 'remotion';
import { VerticalBackground } from './components/VerticalBackground';
import { Scene1Hook } from './scenes_1710/Scene1Hook';
import { Scene2Reveal } from './scenes_1710/Scene2Reveal';
import { Scene3IndividualPayments } from './scenes_1710/Scene3IndividualPayments';
import { Scene4AdminConsole } from './scenes_1710/Scene4AdminConsole';
import { Scene5FloorMap } from './scenes_1710/Scene5FloorMap';
import { Scene6DrawerSecurity } from './scenes_1710/Scene6DrawerSecurity';
import { Scene7MetricsImpact } from './scenes_1710/Scene7MetricsImpact';
import { Scene8ViralCta } from './scenes_1710/Scene8ViralCta';

export const TOTAL_FRAMES_1710 = 1080; // 36 seconds at 30 FPS

export const MainUltimaEdicion1710: React.FC = () => {
  const frame = useCurrentFrame();
  const progressPercent = Math.min(100, (frame / TOTAL_FRAMES_1710) * 100);

  return (
    <div
      style={{
        width: 1080,
        height: 1920,
        position: 'relative',
        backgroundColor: '#0b0f19',
        overflow: 'hidden',
      }}
    >
      {/* Dynamic Background Music Track with Ducking */}
      <Audio src={staticFile('tech_ambient.wav')} volume={0.35} />

      {/* Modern Mesh Grid Background */}
      <VerticalBackground />

      {/* 8 Sequential TikTok/Reels Scenes */}
      <Series>
        {/* Escena 1: El Gancho (4.0s / 120 frames) */}
        <Series.Sequence durationInFrames={120}>
          <Scene1Hook />
        </Series.Sequence>

        {/* Escena 2: Revelación CondoHub SaaS (4.0s / 120 frames) */}
        <Series.Sequence durationInFrames={120}>
          <Scene2Reveal />
        </Series.Sequence>

        {/* Escena 3: Pagos Individuales & Autogestión (5.0s / 150 frames) */}
        <Series.Sequence durationInFrames={150}>
          <Scene3IndividualPayments />
        </Series.Sequence>

        {/* Escena 4: Consola de Conciliación en 1 Clic (5.0s / 150 frames) */}
        <Series.Sequence durationInFrames={150}>
          <Scene4AdminConsole />
        </Series.Sequence>

        {/* Escena 5: Mapa Interactivo de Pisos 2D (5.0s / 150 frames) */}
        <Series.Sequence durationInFrames={150}>
          <Scene5FloorMap />
        </Series.Sequence>

        {/* Escena 6: Menú Retráctil & Blindaje Legal (5.0s / 150 frames) */}
        <Series.Sequence durationInFrames={150}>
          <Scene6DrawerSecurity />
        </Series.Sequence>

        {/* Escena 7: Métricas e Impacto (4.0s / 120 frames) */}
        <Series.Sequence durationInFrames={120}>
          <Scene7MetricsImpact />
        </Series.Sequence>

        {/* Escena 8: Llamado a la Acción Viral (4.0s / 120 frames) */}
        <Series.Sequence durationInFrames={120}>
          <Scene8ViralCta />
        </Series.Sequence>
      </Series>

      {/* Persistent Bottom Neon Progress Bar */}
      <div
        style={{
          position: 'absolute',
          bottom: 0,
          left: 0,
          width: '100%',
          height: 8,
          backgroundColor: 'rgba(255, 255, 255, 0.08)',
          zIndex: 99,
        }}
      >
        <div
          style={{
            height: '100%',
            width: `${progressPercent}%`,
            background: 'linear-gradient(to right, #ef4444 0%, #3b82f6 35%, #8b5cf6 70%, #10b981 100%)',
            boxShadow: '0 0 16px rgba(59, 130, 246, 0.9)',
          }}
        />
      </div>
    </div>
  );
};

export const UltimaEdicion1710Composition: React.FC = () => {
  return (
    <Composition
      id="UltimaEdicion1710"
      component={MainUltimaEdicion1710}
      durationInFrames={TOTAL_FRAMES_1710}
      fps={30}
      width={1080}
      height={1920}
    />
  );
};
