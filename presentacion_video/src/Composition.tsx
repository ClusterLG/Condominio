import React from 'react';
import { Composition, Series, useCurrentFrame } from 'remotion';
import { Background } from './components/Background';
import { IntroScene } from './scenes/IntroScene';
import { ProblemSolutionScene } from './scenes/ProblemSolutionScene';
import { CoreFeaturesScene } from './scenes/CoreFeaturesScene';
import { UpgradesScene } from './scenes/UpgradesScene';
import { StepByStepScene } from './scenes/StepByStepScene';
import { PortalsScene } from './scenes/PortalsScene';
import { MetricsCtaScene } from './scenes/MetricsCtaScene';

const TOTAL_FRAMES = 1530; // 51 seconds at 30 FPS

export const MainPresentation: React.FC = () => {
  const frame = useCurrentFrame();
  const progressPercent = Math.min(100, (frame / TOTAL_FRAMES) * 100);

  return (
    <div
      style={{
        width: 1920,
        height: 1080,
        position: 'relative',
        backgroundColor: '#0b0f19',
        overflow: 'hidden',
      }}
    >
      {/* Background ambient animations */}
      <Background />

      {/* Main Sequential Scenes */}
      <Series>
        {/* Scene 1: Intro / Título y Propuesta (6 segundos) */}
        <Series.Sequence durationInFrames={180}>
          <IntroScene />
        </Series.Sequence>

        {/* Scene 2: Problema vs Solución (7 segundos) */}
        <Series.Sequence durationInFrames={210}>
          <ProblemSolutionScene />
        </Series.Sequence>

        {/* Scene 3: Las 4 Columnas del Ecosistema (8 segundos) */}
        <Series.Sequence durationInFrames={240}>
          <CoreFeaturesScene />
        </Series.Sequence>

        {/* Scene 4: Nuevas Mejoras, Menú Vertical Retráctil y Blindaje (8 segundos) */}
        <Series.Sequence durationInFrames={240}>
          <UpgradesScene />
        </Series.Sequence>

        {/* Scene 5: Paso a Paso del Ciclo Mensual (7 segundos) */}
        <Series.Sequence durationInFrames={210}>
          <StepByStepScene />
        </Series.Sequence>

        {/* Scene 6: Portales Especializados con Menú Retráctil (8 segundos) */}
        <Series.Sequence durationInFrames={240}>
          <PortalsScene />
        </Series.Sequence>

        {/* Scene 7: Métricas y Llamado a la Acción (7 segundos) */}
        <Series.Sequence durationInFrames={210}>
          <MetricsCtaScene />
        </Series.Sequence>
      </Series>

      {/* Persistent Bottom Progress Bar */}
      <div
        style={{
          position: 'absolute',
          bottom: 0,
          left: 0,
          width: '100%',
          height: 6,
          backgroundColor: 'rgba(255, 255, 255, 0.08)',
          zIndex: 99,
        }}
      >
        <div
          style={{
            height: '100%',
            width: `${progressPercent}%`,
            background: 'linear-gradient(to right, #3b82f6, #8b5cf6, #10b981)',
            boxShadow: '0 0 12px rgba(59, 130, 246, 0.8)',
          }}
        />
      </div>
    </div>
  );
};

export const MyComposition: React.FC = () => {
  return (
    <Composition
      id="CondoHubPresentation"
      component={MainPresentation}
      durationInFrames={TOTAL_FRAMES}
      fps={30}
      width={1920}
      height={1080}
    />
  );
};
