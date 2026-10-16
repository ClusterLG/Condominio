import React from 'react';
import { Audio, Composition, Series, staticFile, useCurrentFrame } from 'remotion';
import { VerticalBackground } from './components/VerticalBackground';
import { VerticalIntroScene } from './scenes/VerticalIntroScene';
import { VerticalProblemSolutionScene } from './scenes/VerticalProblemSolutionScene';
import { VerticalCoreFeaturesScene } from './scenes/VerticalCoreFeaturesScene';
import { VerticalUpgradesScene } from './scenes/VerticalUpgradesScene';
import { VerticalStepByStepScene } from './scenes/VerticalStepByStepScene';
import { VerticalPortalsScene } from './scenes/VerticalPortalsScene';
import { VerticalMetricsCtaScene } from './scenes/VerticalMetricsCtaScene';

const TOTAL_FRAMES = 1530; // 51 seconds at 30 FPS

export const MainVerticalPresentation: React.FC = () => {
  const frame = useCurrentFrame();
  const progressPercent = Math.min(100, (frame / TOTAL_FRAMES) * 100);

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
      {/* Background Music Bed (51s continuous techno-ambient groove) */}
      <Audio src={staticFile('tech_ambient.wav')} volume={0.35} />

      {/* Visual Ambient Background */}
      <VerticalBackground />

      {/* 7 Sequential Scenes */}
      <Series>
        {/* Escena 1: Intro y Branding (6 segundos / 180 frames) */}
        <Series.Sequence durationInFrames={180}>
          <VerticalIntroScene />
        </Series.Sequence>

        {/* Escena 2: Problema vs Solución (7 segundos / 210 frames) */}
        <Series.Sequence durationInFrames={210}>
          <VerticalProblemSolutionScene />
        </Series.Sequence>

        {/* Escena 3: Las 4 Columnas Tecnológicas (8 segundos / 240 frames) */}
        <Series.Sequence durationInFrames={240}>
          <VerticalCoreFeaturesScene />
        </Series.Sequence>

        {/* Escena 4: Nuevas Mejoras, Menú Vertical y Blindaje (8 segundos / 240 frames) */}
        <Series.Sequence durationInFrames={240}>
          <VerticalUpgradesScene />
        </Series.Sequence>

        {/* Escena 5: Flujo Mensual en 4 Pasos (7 segundos / 210 frames) */}
        <Series.Sequence durationInFrames={210}>
          <VerticalStepByStepScene />
        </Series.Sequence>

        {/* Escena 6: Portales Especializados con Menú Retráctil (8 segundos / 240 frames) */}
        <Series.Sequence durationInFrames={240}>
          <VerticalPortalsScene />
        </Series.Sequence>

        {/* Escena 7: Métricas y Contacto (7 segundos / 210 frames) */}
        <Series.Sequence durationInFrames={210}>
          <VerticalMetricsCtaScene />
        </Series.Sequence>
      </Series>

      {/* Persistent Bottom Progress Bar */}
      <div
        style={{
          position: 'absolute',
          bottom: 0,
          left: 0,
          width: '100%',
          height: 8,
          backgroundColor: 'rgba(255, 255, 255, 0.1)',
          zIndex: 99,
        }}
      >
        <div
          style={{
            height: '100%',
            width: `${progressPercent}%`,
            background: 'linear-gradient(to right, #3b82f6, #8b5cf6, #10b981)',
            boxShadow: '0 0 16px rgba(59, 130, 246, 0.9)',
          }}
        />
      </div>
    </div>
  );
};

export const VerticalComposition: React.FC = () => {
  return (
    <Composition
      id="CondoHubVertical"
      component={MainVerticalPresentation}
      durationInFrames={TOTAL_FRAMES}
      fps={30}
      width={1080}
      height={1920}
    />
  );
};
