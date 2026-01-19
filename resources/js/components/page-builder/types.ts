export interface Block {
  id: string;
  type: BlockType;
  content: Record<string, any>;
  settings?: Record<string, any>;
  order: number;
  parentId?: string | null;
  children?: Block[];
}

export type BlockType =
  | 'text'
  | 'heading'
  | 'image'
  | 'gallery'
  | 'video'
  | 'html'
  | 'columns'
  | 'section'
  | 'spacer'
  | 'separator'
  | 'accordion'
  | 'button'
  | 'card'
  | 'quote'
  | 'tabs'
  | 'sipp_schedule'
  | 'news_grid'
  | 'document_list';

export interface BlockTypeConfig {
  type: BlockType;
  label: string;
  icon: string;
  category: 'basic' | 'media' | 'layout' | 'advanced';
  description: string;
}

export interface PageBuilderContent {
  blocks: Block[];
  settings?: Record<string, any>;
}

export interface Page {
  id: number;
  slug: string;
  title: string;
  excerpt?: string;
  content?: any;
  builder_content?: PageBuilderContent;
  version: number;
  is_builder_enabled: boolean;
  status: 'draft' | 'published' | 'archived';
  page_type: 'static' | 'dynamic' | 'template';
  author?: {
    id: number;
    name: string;
  };
  lastEditedBy?: {
    id: number;
    name: string;
  };
  template?: {
    id: number;
    name: string;
  };
  created_at: string;
  updated_at: string;
}

export interface PageBuilderProps {
  page: Page;
  onSave?: (content: PageBuilderContent) => void;
  onCancel?: () => void;
}

export interface BlockComponentProps {
  block: Block;
  isSelected?: boolean;
  onSelect?: () => void;
  onUpdate?: (content: Record<string, any>, settings?: Record<string, any>) => void;
  onDelete?: () => void;
  onDuplicate?: () => void;
}

export interface TextBlockContent {
  text: string;
}

export interface HeadingBlockContent {
  text: string;
  level: 1 | 2 | 3 | 4 | 5 | 6;
}

export interface ImageBlockContent {
  url: string;
  alt: string;
  width?: number;
  height?: number;
  caption?: string;
}

export interface VideoBlockContent {
  url: string;
  platform: 'youtube' | 'vimeo' | 'custom';
  autoplay?: boolean;
  controls?: boolean;
}

export interface ColumnsBlockContent {
  columns: Array<{
    content?: PageBuilderContent;
    width?: number;
  }>;
}

export interface SpacerBlockContent {
  height: number;
}

export interface SectionBlockContent {
  backgroundColor?: string;
  padding?: {
    top?: number;
    bottom?: number;
    left?: number;
    right?: number;
  };
}
