import React, { useState, useCallback } from 'react';
import { DndContext, DragEndEvent, DragOverlay, DragStartEvent, closestCenter } from '@dnd-kit/core';
import { PageBuilderProps, Block } from './types';
import ComponentPalette from './ComponentPalette';
import BuilderCanvas from './BuilderCanvas';
import PropertiesPanel from './PropertiesPanel';
import { Save, X, Eye, Undo2, Redo2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { usePageBuilder } from '@/hooks/usePageBuilder';
import { PageBuilderProvider, usePageBuilderContext } from './PageBuilderContext';

const PageBuilderContent: React.FC<PageBuilderProps> = ({ page, onSave, onCancel }) => {
  const {
    blocks,
    selectedBlockId,
    setSelectedBlockId,
    isPreview,
    setIsPreview,
    addBlock,
    updateBlock,
    deleteBlock,
    duplicateBlock,
    moveBlock,
    undo,
    redo,
    canUndo,
    canRedo,
    historyIndex,
  } = usePageBuilderContext();

  const [activeBlock, setActiveBlock] = useState<Block | null>(null);
  const [isSaving, setIsSaving] = useState(false);
  const { savePageBuilder } = usePageBuilder();

  const selectedBlock = blocks.find((b) => b.id === selectedBlockId) || null;

  const handleDragStart = useCallback((event: DragStartEvent) => {
    const { active } = event;
    const block = blocks.find((b) => b.id === active.id);
    if (block) {
      setActiveBlock(block);
    }
  }, [blocks]);

  const handleDragEnd = useCallback((event: DragEndEvent) => {
    const { active, over } = event;

    if (!over) {
      setActiveBlock(null);
      return;
    }

    const overData = over.data.current;
    const activeData = active.data.current;

    // Handle dropping from palette
    if (activeData?.type === 'palette') {
      const blockType = activeData.blockType;

      // If dropping into a container (Section or Column)
      if (overData?.type === 'container') {
        addBlock(blockType, overData.parentId, overData.index);
      } else {
        // Find index of the block we dropped over in the top level
        const overIndex = blocks.findIndex((b) => b.id === over.id);
        addBlock(blockType, null, overIndex !== -1 ? overIndex : blocks.length);
      }
    }
    // Handle reordering (for now mostly top level reordering)
    else if (active.id !== over.id && activeData?.type !== 'palette') {
      moveBlock(active.id as string, over.id as string);
    }

    setActiveBlock(null);
  }, [addBlock, moveBlock, blocks]);

  const handleSave = useCallback(async () => {
    setIsSaving(true);
    try {
      // Simple HTML generation from blocks (basic version)
      const generateHtml = (blocks: Block[]): string => {
        return blocks.map(block => {
          switch(block.type) {
            case 'text': return `<div class="prose">${block.content.text || ''}</div>`;
            case 'heading': return `<h${block.content.level || 2}>${block.content.text || ''}</h${block.content.level || 2}>`;
            case 'image': return `<img src="${block.content.url || ''}" alt="${block.content.alt || ''}">`;
            // Add more renderers or a unified rendering service later
            default: return '';
          }
        }).join('\n');
      };

      await savePageBuilder(page.id, {
        builder_content: { blocks, settings: {} },
        html_content: generateHtml(blocks),
      } as any);

      if (onSave) {
        onSave({ blocks, settings: {} });
      }
    } catch (error) {
      console.error('Failed to save:', error);
    } finally {
      setIsSaving(false);
    }
  }, [page.id, blocks, savePageBuilder, onSave]);

  return (
    <div className="flex h-screen flex-col bg-gray-50">
      {/* Header */}
      <div className="flex items-center justify-between border-b bg-white px-6 py-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Page Builder</h1>
          <p className="text-sm text-gray-500">{page.title}</p>
        </div>

        <div className="flex items-center gap-2">
          <Button
            variant="outline"
            size="sm"
            onClick={undo}
            disabled={!canUndo}
          >
            <Undo2 className="h-4 w-4" />
          </Button>
          <Button
            variant="outline"
            size="sm"
            onClick={redo}
            disabled={!canRedo}
          >
            <Redo2 className="h-4 w-4" />
          </Button>
          <Button
            variant="outline"
            size="sm"
            onClick={() => setIsPreview(!isPreview)}
          >
            <Eye className="h-4 w-4 mr-2" />
            {isPreview ? 'Edit' : 'Preview'}
          </Button>
          <Button
            variant="outline"
            size="sm"
            onClick={onCancel}
          >
            <X className="h-4 w-4 mr-2" />
            Cancel
          </Button>
          <Button
            variant="default"
            size="sm"
            onClick={handleSave}
            disabled={isSaving}
          >
            <Save className="h-4 w-4 mr-2" />
            {isSaving ? 'Saving...' : 'Save'}
          </Button>
        </div>
      </div>

      {/* Main Content */}
      <div className="flex flex-1 overflow-hidden">
        <DndContext
          collisionDetection={closestCenter}
          onDragStart={handleDragStart}
          onDragEnd={handleDragEnd}
        >
          {/* Component Palette */}
          {!isPreview && (
            <ComponentPalette
              onAddBlock={addBlock}
              disabled={isPreview}
            />
          )}

          {/* Canvas */}
          <div className="flex-1 overflow-auto">
            <BuilderCanvas
              blocks={blocks}
              selectedBlockId={selectedBlockId}
              onSelectBlock={setSelectedBlockId}
              onUpdateBlock={updateBlock}
              onDeleteBlock={deleteBlock}
              onDuplicateBlock={duplicateBlock}
              isPreview={isPreview}
            />
          </div>

          {/* Properties Panel */}
          {selectedBlock && !isPreview && (
            <PropertiesPanel
              block={selectedBlock}
              onUpdate={(content, settings) => updateBlock(selectedBlock.id, content, settings)}
              onClose={() => setSelectedBlockId(null)}
            />
          )}
        </DndContext>
      </div>

      {/* Drag Overlay */}
      <DragOverlay>
        {activeBlock ? (
          <div className="w-64 rounded-lg border-2 border-blue-500 bg-gray-100 p-4 opacity-50 shadow-lg">
            <div className="text-sm font-medium">{activeBlock.type}</div>
          </div>
        ) : null}
      </DragOverlay>
    </div>
  );
};

const PageBuilder: React.FC<PageBuilderProps> = (props) => {
  return (
    <PageBuilderProvider page={props.page}>
      <PageBuilderContent {...props} />
    </PageBuilderProvider>
  );
};

export default PageBuilder;
