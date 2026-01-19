import React from 'react';
import { SortableContext, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { BlockComponentProps, Block } from '../types';
import BlockRenderer from '../BlockRenderer';
import DroppableZone from '../DroppableZone';
import SortableBlockWrapper from '../SortableBlockWrapper';
import { usePageBuilderContext } from '../PageBuilderContext';

const ColumnsBlock: React.FC<BlockComponentProps> = ({ block, isSelected }) => {
  const { setSelectedBlockId, deleteBlock, duplicateBlock, isPreview } = usePageBuilderContext();
  const columns = block.content?.columns || [
    { content: { blocks: [] } },
    { content: { blocks: [] } }
  ];

  return (
    <div className={`
      bg-white rounded-lg border transition-all duration-200 overflow-hidden
      ${isSelected ? 'border-blue-500 shadow-md ring-1 ring-blue-500' : 'border-gray-200 shadow-sm'}
    `}>
      <div className={`grid gap-4 p-4`} style={{ gridTemplateColumns: `repeat(${columns.length}, 1fr)` }}>
        {columns.map((column: any, colIndex: number) => {
          const columnBlocks = column.content?.blocks || [];

          return (
            <SortableContext
              key={colIndex}
              items={columnBlocks.map((b: Block) => b.id)}
              strategy={verticalListSortingStrategy}
            >
              <DroppableZone
                id={`droppable-${block.id}-col-${colIndex}`}
                parentId={block.id}
                index={colIndex}
                placeholder={`Column ${colIndex + 1}`}
                className="border border-dashed border-gray-300 rounded p-4 min-h-[150px] bg-gray-50/30 space-y-4"
              >
                {columnBlocks.map((childBlock: Block) => (
                  <SortableBlockWrapper
                    key={childBlock.id}
                    block={childBlock}
                    isSelected={selectedBlockId === childBlock.id}
                    onSelect={() => setSelectedBlockId(childBlock.id)}
                    onDelete={() => deleteBlock(childBlock.id)}
                    onDuplicate={() => duplicateBlock(childBlock.id)}
                  >
                    <BlockRenderer
                      block={childBlock}
                      isPreview={isPreview}
                    />
                  </SortableBlockWrapper>
                ))}
              </DroppableZone>
            </SortableContext>
          );
        })}
      </div>
    </div>
  );
};

export default ColumnsBlock;
