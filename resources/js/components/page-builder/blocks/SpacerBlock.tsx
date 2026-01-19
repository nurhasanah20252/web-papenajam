import React from 'react';
import { BlockComponentProps } from '../types';

const SpacerBlock: React.FC<BlockComponentProps> = ({ block }) => {
  const height = block.content?.height || 50;

  return (
    <div
      className="bg-blue-50 border border-blue-200 rounded flex items-center justify-center relative group"
      style={{ height: `${height}px` }}
    >
      <div className="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
        <span className="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded">
          Spacer: {height}px
        </span>
      </div>
      <div className="h-px w-8 bg-blue-300"></div>
    </div>
  );
};

export default SpacerBlock;
