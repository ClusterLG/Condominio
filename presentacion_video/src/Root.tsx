import React from 'react';
import { MyComposition } from './Composition';
import { VerticalComposition } from './VerticalComposition';
import { UltimaEdicion1710Composition } from './UltimaEdicion1710';

export const RemotionRoot: React.FC = () => {
  return (
    <>
      {/* Nueva Edición Dinámica TikTok / Reels 9:16 (1080x1920 - 36s) */}
      <UltimaEdicion1710Composition />

      {/* Composición Vertical Extendida 9:16 (1080x1920) */}
      <VerticalComposition />

      {/* Composición Horizontal 16:9 (1920x1080) */}
      <MyComposition />
    </>
  );
};
