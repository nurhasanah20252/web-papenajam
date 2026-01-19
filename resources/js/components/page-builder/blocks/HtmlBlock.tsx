import React from 'react';
import DOMPurify from 'isomorphic-dompurify';
import { BlockComponentProps } from '../types';

const HtmlBlock: React.FC<BlockComponentProps> = ({ block, isSelected, onUpdate }) => {
  const html = block.content?.html || '';

  if (!isSelected && !html) {
    return (
      <div className="p-8 bg-gray-50 rounded-lg border border-dashed border-gray-300 text-center">
        <p className="text-sm text-gray-400">Empty HTML Block</p>
      </div>
    );
  }

  if (isSelected) {
    return (
      <div className="bg-white rounded-lg border border-blue-500 shadow-sm overflow-hidden">
        <div className="bg-blue-50 px-3 py-1 text-[10px] font-bold text-blue-600 uppercase border-b border-blue-200">
          HTML Editor
        </div>
        <textarea
          className="w-full p-4 font-mono text-sm bg-gray-900 text-green-400 focus:outline-none min-h-[200px]"
          value={html}
          onChange={(e) => onUpdate?.({ html: e.target.value })}
          placeholder="<div>Enter your HTML here...</div>"
        />
      </div>
    );
  }

  const sanitizedHtml = DOMPurify.sanitize(html);

  return (
    <div
      className="prose max-w-none"
      dangerouslySetInnerHTML={{ __html: sanitizedHtml }}
    />
  );
};

export default HtmlBlock;
