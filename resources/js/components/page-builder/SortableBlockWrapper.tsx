import React from 'react';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { Block } from './types';
import { GripVertical, Trash2, Copy } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface SortableBlockWrapperProps {
  block: Block;
  isSelected: boolean;
  onSelect: () => void;
  onDelete: () => void;
  onDuplicate: () => void;
  children: React.ReactNode;
}

const SortableBlockWrapper: React.FC<SortableBlockWrapperProps> = ({
  block,
  isSelected,
  onSelect,
  onDelete,
  onDuplicate,
  children,
}) => {
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    transition,
    isDragging,
  } = useSortable({ id: block.id });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.5 : 1,
  };

  return (
    <div
      ref={setNodeRef}
      style={style}
      className={`
        relative group transition-all duration-200
        ${isSelected ? 'ring-2 ring-blue-500 ring-offset-4 rounded-lg' : ''}
        ${isDragging ? 'z-50' : 'z-0'}
        mb-4
      `}
    >
      {/* Drag Handle & Actions */}
      <div
        className={`
          absolute -top-3 -left-3 z-10 flex items-center gap-1
          transition-opacity duration-200
          ${isSelected ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'}
        `}
      >
        <button
          {...attributes}
          {...listeners}
          className="bg-white border border-gray-300 rounded-md p-1 shadow-sm hover:bg-gray-50 cursor-grab active:cursor-grabbing"
          title="Drag to move"
        >
          <GripVertical className="h-4 w-4 text-gray-600" />
        </button>

        <button
          onClick={(e) => {
            e.stopPropagation();
            onDuplicate();
          }}
          className="bg-white border border-gray-300 rounded-md p-1 shadow-sm hover:bg-gray-50"
          title="Duplicate block"
        >
          <Copy className="h-4 w-4 text-gray-600" />
        </button>

        <button
          onClick={(e) => {
            e.stopPropagation();
            onDelete();
          }}
          className="bg-white border border-gray-300 rounded-md p-1 shadow-sm hover:bg-red-50"
          title="Delete block"
        >
          <Trash2 className="h-4 w-4 text-red-600" />
        </button>
      </div>

      {/* Block Content */}
      <div
        onClick={onSelect}
        className="cursor-pointer"
      >
        {children}
      </div>
    </div>
  );
};

export default SortableBlockWrapper;
