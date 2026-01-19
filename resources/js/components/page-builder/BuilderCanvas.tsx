import React from 'react';
import { SortableContext, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { Block } from './types';
import SortableBlockWrapper from './SortableBlockWrapper';
import BlockRenderer from './BlockRenderer';

interface BuilderCanvasProps {
  blocks: Block[];
  selectedBlockId: string | null;
  onSelectBlock: (blockId: string | null) => void;
  onUpdateBlock: (blockId: string, content: Record<string, any>, settings?: Record<string, any>) => void;
  onDeleteBlock: (blockId: string) => void;
  onDuplicateBlock: (blockId: string) => void;
  isPreview?: boolean;
}

const BuilderCanvas: React.FC<BuilderCanvasProps> = ({
  blocks,
  selectedBlockId,
  onSelectBlock,
  onUpdateBlock,
  onDeleteBlock,
  onDuplicateBlock,
  isPreview = false,
}) => {
  const renderBlock = (block: Block) => {
    const isSelected = selectedBlockId === block.id;

    const blockContent = (
      <BlockRenderer
        block={block}
        isSelected={isSelected}
        isPreview={isPreview}
        onSelect={() => onSelectBlock(isSelected ? null : block.id)}
        onUpdate={(content) => onUpdateBlock(block.id, content)}
      />
    );

    if (isPreview) {
      return <div key={block.id}>{blockContent}</div>;
    }

    return (
      <SortableBlockWrapper
        key={block.id}
        block={block}
        isSelected={isSelected}
        onSelect={() => onSelectBlock(isSelected ? null : block.id)}
        onDelete={() => onDeleteBlock(block.id)}
        onDuplicate={() => onDuplicateBlock(block.id)}
      >
        {blockContent}
      </SortableBlockWrapper>
    );
  };

  if (blocks.length === 0) {
    return (
      <div className="h-full flex items-center justify-center p-8">
        <div className="text-center">
          <div className="text-gray-400 mb-4">
            <svg
              className="mx-auto h-12 w-12"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 3h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"
              />
            </svg>
          </div>
          <h3 className="text-lg font-medium text-gray-900 mb-2">No blocks yet</h3>
          <p className="text-sm text-gray-500">
            Add blocks from the components palette on the left to start building your page.
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-5xl mx-auto p-8">
      <SortableContext
        items={blocks.map((b) => b.id)}
        strategy={verticalListSortingStrategy}
      >
        <div className="space-y-4">
          {blocks.map((block) => renderBlock(block))}
        </div>
      </SortableContext>
    </div>
  );
};

export default BuilderCanvas;
