import React from 'react';
import { Block, BlockComponentProps } from './types';
import { usePageBuilderContext } from './PageBuilderContext';
import TextBlock from './blocks/TextBlock';
import HeadingBlock from './blocks/HeadingBlock';
import ImageBlock from './blocks/ImageBlock';
import VideoBlock from './blocks/VideoBlock';
import ColumnsBlock from './blocks/ColumnsBlock';
import SectionBlock from './blocks/SectionBlock';
import SpacerBlock from './blocks/SpacerBlock';
import SeparatorBlock from './blocks/SeparatorBlock';
import HtmlBlock from './blocks/HtmlBlock';
import AccordionBlock from './blocks/AccordionBlock';
import ButtonBlock from './blocks/ButtonBlock';
import CardBlock from './blocks/CardBlock';
import GalleryBlock from './blocks/GalleryBlock';
import QuoteBlock from './blocks/QuoteBlock';
import TabsBlock from './blocks/TabsBlock';
import SippScheduleBlock from './blocks/SippScheduleBlock';
import NewsGridBlock from './blocks/NewsGridBlock';
import DocumentListBlock from './blocks/DocumentListBlock';

interface BlockRendererProps {
  block: Block;
  isSelected?: boolean;
  isPreview?: boolean;
  onSelect?: () => void;
  onUpdate?: (content: Record<string, any>) => void;
}

const BlockRenderer: React.FC<BlockRendererProps> = ({
  block,
  isSelected: propsIsSelected,
  isPreview: propsIsPreview,
  onSelect: propsOnSelect,
  onUpdate: propsOnUpdate,
}) => {
  const {
    selectedBlockId,
    setSelectedBlockId,
    isPreview: contextIsPreview,
    updateBlock,
  } = usePageBuilderContext();

  const isSelected = propsIsSelected ?? selectedBlockId === block.id;
  const isPreview = propsIsPreview ?? contextIsPreview;

  const handleSelect = () => {
    if (propsOnSelect) {
      propsOnSelect();
    } else {
      setSelectedBlockId(isSelected ? null : block.id);
    }
  };

  const handleUpdate = (content: Record<string, any>, settings?: Record<string, any>) => {
    if (propsOnUpdate) {
      propsOnUpdate(content, settings);
    } else {
      updateBlock(block.id, content, settings);
    }
  };

  const commonProps: BlockComponentProps = {
    block,
    isSelected,
    onSelect: handleSelect,
    onUpdate: handleUpdate,
  };

  switch (block.type) {
    case 'text':
      return <TextBlock {...commonProps} />;
    case 'heading':
      return <HeadingBlock {...commonProps} />;
    case 'image':
      return <ImageBlock {...commonProps} />;
    case 'video':
      return <VideoBlock {...commonProps} />;
    case 'columns':
      return <ColumnsBlock {...commonProps} />;
    case 'section':
      return <SectionBlock {...commonProps} />;
    case 'spacer':
      return <SpacerBlock {...commonProps} />;
    case 'separator':
      return <SeparatorBlock {...commonProps} />;
    case 'html':
      return <HtmlBlock {...commonProps} />;
    case 'accordion':
      return <AccordionBlock {...commonProps} />;
    case 'button':
      return <ButtonBlock {...commonProps} />;
    case 'card':
      return <CardBlock {...commonProps} />;
    case 'gallery':
      return <GalleryBlock {...commonProps} />;
    case 'quote':
      return <QuoteBlock {...commonProps} />;
    case 'tabs':
      return <TabsBlock {...commonProps} />;
    case 'sipp_schedule':
      return <SippScheduleBlock {...commonProps} />;
    case 'news_grid':
      return <NewsGridBlock {...commonProps} />;
    case 'document_list':
      return <DocumentListBlock {...commonProps} />;
    default:
      return (
        <div className="p-4 bg-gray-100 text-gray-500 rounded border border-dashed border-gray-300">
          Unknown block type: {block.type}
        </div>
      );
  }
};

export default BlockRenderer;
