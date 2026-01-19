import React from 'react';
import { BlockComponentProps } from '../types';

const SeparatorBlock: React.FC<BlockComponentProps> = () => {
  return (
    <div className="py-4">
      <hr className="border-t border-gray-300" />
    </div>
  );
};

export default SeparatorBlock;
