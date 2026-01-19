import React from 'react';
import { BlockComponentProps } from '../types';

const HeadingBlock: React.FC<BlockComponentProps> = ({ block }) => {
  const text = block.content?.text || 'Heading';
  const level = block.content?.level || 2;

  const HeadingTag = `h${level}` as keyof JSX.IntrinsicElements;

  return (
    <div className="bg-white rounded-lg border border-gray-200 shadow-sm">
      <HeadingTag className="p-4 font-bold text-gray-900">
        {text}
      </HeadingTag>
    </div>
  );
};

export default HeadingBlock;
