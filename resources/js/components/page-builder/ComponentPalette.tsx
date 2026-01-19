import React from 'react';
import { useDraggable } from '@dnd-kit/core';
import { BlockTypeConfig } from './types';
import {
  Type,
  Heading,
  Image as ImageIcon,
  Video,
  Columns,
  Square,
  Minus,
  Code,
  ArrowDownUp,
  List,
  Quote,
  Layout,
  Folder,
  MousePointer,
  Calendar,
  Newspaper,
  FileText
} from 'lucide-react';

interface ComponentPaletteProps {
  onAddBlock: (type: string) => void;
  disabled?: boolean;
}

const blockTypes: BlockTypeConfig[] = [
  {
    type: 'text',
    label: 'Text',
    icon: 'align-left',
    category: 'basic',
    description: 'Add text content with rich formatting',
  },
  {
    type: 'heading',
    label: 'Heading',
    icon: 'heading',
    category: 'basic',
    description: 'Add a heading with different sizes',
  },
  {
    type: 'image',
    label: 'Image',
    icon: 'image',
    category: 'media',
    description: 'Add an image with optional caption',
  },
  {
    type: 'gallery',
    label: 'Gallery',
    icon: 'photo',
    category: 'media',
    description: 'Add a grid of images',
  },
  {
    type: 'video',
    label: 'Video',
    icon: 'video',
    category: 'media',
    description: 'Embed a video from YouTube or Vimeo',
  },
  {
    type: 'columns',
    label: 'Columns',
    icon: 'columns',
    category: 'layout',
    description: 'Split content into multiple columns',
  },
  {
    type: 'section',
    label: 'Section',
    icon: 'square',
    category: 'layout',
    description: 'Add a section with background color',
  },
  {
    type: 'spacer',
    label: 'Spacer',
    icon: 'arrows-vertical',
    category: 'layout',
    description: 'Add vertical space between blocks',
  },
  {
    type: 'separator',
    label: 'Separator',
    icon: 'minus',
    category: 'layout',
    description: 'Add a horizontal line',
  },
  {
    type: 'accordion',
    label: 'Accordion',
    icon: 'list',
    category: 'advanced',
    description: 'Collapsible content items',
  },
  {
    type: 'button',
    label: 'Button',
    icon: 'mouse-pointer',
    category: 'basic',
    description: 'Call to action button',
  },
  {
    type: 'card',
    label: 'Card',
    icon: 'layout',
    category: 'advanced',
    description: 'Content card with image and text',
  },
  {
    type: 'quote',
    label: 'Quote',
    icon: 'quote',
    category: 'basic',
    description: 'Add a blockquote with author',
  },
  {
    type: 'tabs',
    label: 'Tabs',
    icon: 'folder',
    category: 'advanced',
    description: 'Tabbed content navigation',
  },
  {
    type: 'html',
    label: 'HTML',
    icon: 'code',
    category: 'advanced',
    description: 'Add custom HTML code',
  },
  {
    type: 'sipp_schedule',
    label: 'Court Schedule',
    icon: 'calendar',
    category: 'advanced',
    description: 'Display real-time court schedules from SIPP',
  },
  {
    type: 'news_grid',
    label: 'News Grid',
    icon: 'newspaper',
    category: 'advanced',
    description: 'Display latest news and announcements',
  },
  {
    type: 'document_list',
    label: 'Document List',
    icon: 'file-text',
    category: 'advanced',
    description: 'List of downloadable documents and reports',
  },
];

const getIconForBlockType = (type: string) => {
  switch (type) {
    case 'text':
      return Type;
    case 'heading':
      return Heading;
    case 'image':
      return Image;
    case 'gallery':
      return ImageIcon;
    case 'video':
      return Video;
    case 'columns':
      return Columns;
    case 'section':
      return Square;
    case 'spacer':
      return ArrowDownUp;
    case 'separator':
      return Minus;
    case 'accordion':
      return List;
    case 'button':
      return Type; // Using Type as fallback
    case 'card':
      return Square;
    case 'quote':
      return Quote;
    case 'tabs':
      return Columns;
    case 'html':
      return Code;
    case 'sipp_schedule':
      return Calendar;
    case 'news_grid':
      return Newspaper;
    case 'document_list':
      return FileText;
    default:
      return Type;
  }
};

const DraggablePaletteItem: React.FC<{ blockType: BlockTypeConfig; disabled?: boolean; onClick: () => void }> = ({
  blockType,
  disabled,
  onClick
}) => {
  const { attributes, listeners, setNodeRef, isDragging } = useDraggable({
    id: `palette-${blockType.type}`,
    data: {
      type: 'palette',
      blockType: blockType.type,
    },
  });

  const Icon = getIconForBlockType(blockType.type);

  return (
    <button
      ref={setNodeRef}
      {...listeners}
      {...attributes}
      onClick={onClick}
      disabled={disabled}
      className={`
        w-full flex items-start gap-3 p-3 rounded-lg border
        hover:bg-gray-50 hover:border-blue-300
        transition-colors duration-150
        text-left
        ${disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}
        ${isDragging ? 'border-blue-500 bg-blue-50' : 'bg-white'}
      `}
    >
      <div className="flex-shrink-0">
        <Icon className="h-5 w-5 text-gray-600" />
      </div>
      <div className="flex-1 min-w-0">
        <div className="text-sm font-medium text-gray-900">
          {blockType.label}
        </div>
        <div className="text-xs text-gray-500 mt-0.5">
          {blockType.description}
        </div>
      </div>
    </button>
  );
};

const ComponentPalette: React.FC<ComponentPaletteProps> = ({ onAddBlock, disabled }) => {
  const categories = Array.from(new Set(blockTypes.map((bt) => bt.category)));

  return (
    <div className="w-72 border-r bg-white overflow-y-auto">
      <div className="p-4 border-b">
        <h2 className="font-semibold text-lg">Components</h2>
        <p className="text-sm text-gray-500 mt-1">Drag to add blocks</p>
      </div>

      <div className="p-4 space-y-6">
        {categories.map((category) => (
          <div key={category}>
            <h3 className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
              {category}
            </h3>
            <div className="space-y-2">
              {blockTypes
                .filter((bt) => bt.category === category)
                .map((blockType) => (
                  <DraggablePaletteItem
                    key={blockType.type}
                    blockType={blockType}
                    disabled={disabled}
                    onClick={() => !disabled && onAddBlock(blockType.type)}
                  />
                ))}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default ComponentPalette;
