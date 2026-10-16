import React from 'react';
import { MyComposition } from './Composition';
import { VerticalComposition } from './VerticalComposition';

export const RemotionRoot: React.FC = () => {
  return (
    <>
      {/* Composición Vertical 9:16 (1080x1920) con Audio y Efectos de Movimiento */}
      <VerticalComposition />

      {/* Composición Horizontal 16:9 (1920x1080) */}
      <MyComposition />
    </>
  );
};
