import React from 'react';
import { BlockComponentProps } from '../types';
import { Image as ImageIcon } from 'lucide-react';

const ImageBlock: React.FC<BlockComponentProps> = ({ block }) => {
  const url = block.content?.url;
  const alt = block.content?.alt || 'Image';
  const caption = block.content?.caption;

  if (!url) {
    return (
      <div className="p-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center">
        <ImageIcon className="h-12 w-12 text-gray-400 mb-2" />
        <p className="text-sm text-gray-500">No image selected</p>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
      <img
        src={url}
        alt={alt}
        className="w-full h-auto"
      />
      {caption && (
        <div className="p-2 text-sm text-gray-600 text-center italic">
          {caption}
        </div>
      )}
    </div>
  );
};

export default ImageBlock;
