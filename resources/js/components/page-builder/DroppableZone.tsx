import React from 'react';
import { useDroppable } from '@dnd-kit/core';

interface DroppableZoneProps {
  id: string;
  parentId: string;
  index?: number;
  children?: React.ReactNode;
  className?: string;
  placeholder?: string;
}

const DroppableZone: React.FC<DroppableZoneProps> = ({
  id,
  parentId,
  index,
  children,
  className = '',
  placeholder = 'Drop blocks here',
}) => {
  const { isOver, setNodeRef } = useDroppable({
    id,
    data: {
      type: 'container',
      parentId,
      index,
    },
  });

  return (
    <div
      ref={setNodeRef}
      className={`
        transition-all duration-200 rounded-lg
        ${isOver ? 'bg-blue-50 ring-2 ring-blue-300 ring-dashed' : ''}
        ${className}
      `}
    >
      {children}
      {!children && (
        <div className="flex items-center justify-center h-20 border-2 border-dashed border-gray-200 rounded-lg bg-gray-50/50">
          <p className="text-sm text-gray-400">{placeholder}</p>
        </div>
      )}
    </div>
  );
};

export default DroppableZone;
