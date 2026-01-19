import React from 'react';
import { SortableContext, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { BlockComponentProps } from '../types';
import BlockRenderer from '../BlockRenderer';
import DroppableZone from '../DroppableZone';
import SortableBlockWrapper from '../SortableBlockWrapper';
import { usePageBuilderContext } from '../PageBuilderContext';

const SectionBlock: React.FC<BlockComponentProps> = ({ block, isSelected, onUpdate }) => {
  const { setSelectedBlockId, deleteBlock, duplicateBlock, updateBlock, isPreview } = usePageBuilderContext();
  const backgroundColor = block.content?.backgroundColor || '#ffffff';
  const padding = block.content?.padding || { top: 40, bottom: 40, left: 20, right: 20 };
  const children = block.children || [];

  return (
    <div
      className={`
        rounded-lg border transition-all duration-200 overflow-hidden
        ${isSelected ? 'border-blue-500 shadow-md ring-1 ring-blue-500' : 'border-gray-200 shadow-sm'}
      `}
      style={{
        backgroundColor,
        paddingTop: `${padding.top}px`,
        paddingBottom: `${padding.bottom}px`,
        paddingLeft: `${padding.left}px`,
        paddingRight: `${padding.right}px`,
      }}
    >
      <SortableContext items={children.map(c => c.id)} strategy={verticalListSortingStrategy}>
        <DroppableZone
          id={`droppable-${block.id}`}
          parentId={block.id}
          placeholder="Drop blocks inside section"
          className="min-h-[100px] space-y-4"
        >
          {children.map((child) => (
            <SortableBlockWrapper
              key={child.id}
              block={child}
              isSelected={selectedBlockId === child.id}
              onSelect={() => setSelectedBlockId(child.id)}
              onDelete={() => deleteBlock(child.id)}
              onDuplicate={() => duplicateBlock(child.id)}
            >
              <BlockRenderer
                block={child}
                isPreview={isPreview}
              />
            </SortableBlockWrapper>
          ))}
        </DroppableZone>
      </SortableContext>
    </div>
  );
};

export default SectionBlock;
