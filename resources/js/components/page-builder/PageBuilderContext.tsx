import React, { createContext, useContext, useState, useCallback, useMemo } from 'react';
import { Block, Page, PageBuilderContent } from './types';
import { arrayMove } from '@dnd-kit/sortable';
import { v4 as uuidv4 } from 'uuid';

interface PageBuilderContextType {
  page: Page;
  blocks: Block[];
  selectedBlockId: string | null;
  isPreview: boolean;
  history: Block[][];
  historyIndex: number;

  setBlocks: (blocks: Block[]) => void;
  setSelectedBlockId: (id: string | null) => void;
  setIsPreview: (isPreview: boolean) => void;

  addBlock: (type: string, parentId?: string | null, index?: number) => void;
  updateBlock: (id: string, content: Record<string, any>, settings?: Record<string, any>) => void;
  deleteBlock: (id: string) => void;
  duplicateBlock: (id: string) => void;
  moveBlock: (activeId: string, overId: string) => void;

  undo: () => void;
  redo: () => void;
  canUndo: boolean;
  canRedo: boolean;
}

const PageBuilderContext = createContext<PageBuilderContextType | undefined>(undefined);

export const usePageBuilderContext = () => {
  const context = useContext(PageBuilderContext);
  if (!context) {
    throw new Error('usePageBuilderContext must be used within a PageBuilderProvider');
  }
  return context;
};

// Tree helper functions
const findBlockById = (blocks: Block[], id: string): Block | null => {
  for (const block of blocks) {
    if (block.id === id) return block;
    if (block.children) {
      const found = findBlockById(block.children, id);
      if (found) return found;
    }
  }
  return null;
};

const updateBlockInTree = (blocks: Block[], id: string, updates: Partial<Block>): Block[] => {
  return blocks.map((block) => {
    if (block.id === id) {
      return { ...block, ...updates };
    }
    if (block.children) {
      return { ...block, children: updateBlockInTree(block.children, id, updates) };
    }
    return block;
  });
};

const removeBlockFromTree = (blocks: Block[], id: string): Block[] => {
  return blocks
    .filter((block) => block.id !== id)
    .map((block) => {
      if (block.children) {
        return { ...block, children: removeBlockFromTree(block.children, id) };
      }
      return block;
    });
};

const addBlockToTree = (blocks: Block[], newBlock: Block, parentId: string | null, index?: number): Block[] => {
  if (!parentId) {
    const newBlocks = [...blocks];
    if (typeof index === 'number') {
      newBlocks.splice(index, 0, newBlock);
    } else {
      newBlocks.push(newBlock);
    }
    return newBlocks.map((b, i) => ({ ...b, order: i }));
  }

  return blocks.map((block) => {
    if (block.id === parentId) {
      // Handle Columns block specially if index is provided as column index
      if (block.type === 'columns' && typeof index === 'number') {
        const columns = [...(block.content.columns || [])];
        if (columns[index]) {
          const columnBlocks = [...(columns[index].content?.blocks || [])];
          columnBlocks.push(newBlock);
          columns[index] = {
            ...columns[index],
            content: { ...columns[index].content, blocks: columnBlocks }
          };
          return { ...block, content: { ...block.content, columns } };
        }
      }

      // Default children handling (Section, etc)
      const children = [...(block.children || [])];
      if (typeof index === 'number') {
        children.splice(index, 0, newBlock);
      } else {
        children.push(newBlock);
      }
      return { ...block, children: children.map((b, i) => ({ ...b, order: i })) };
    }

    if (block.children) {
      return { ...block, children: addBlockToTree(block.children, newBlock, parentId, index) };
    }

    // Also check inside columns for nested blocks
    if (block.type === 'columns' && block.content.columns) {
      const newColumns = block.content.columns.map((col: any) => ({
        ...col,
        content: col.content ? {
          ...col.content,
          blocks: addBlockToTree(col.content.blocks || [], newBlock, parentId, index)
        } : col.content
      }));
      return { ...block, content: { ...block.content, columns: newColumns } };
    }

    return block;
  });
};

const moveBlockInTree = (blocks: Block[], activeId: string, overId: string): Block[] => {
  // Check if both blocks are at the current level
  const activeIndex = blocks.findIndex((b) => b.id === activeId);
  const overIndex = blocks.findIndex((b) => b.id === overId);

  if (activeIndex !== -1 && overIndex !== -1) {
    return arrayMove(blocks, activeIndex, overIndex).map((b, i) => ({
      ...b,
      order: i,
    }));
  }

  // Otherwise, recurse into children or columns
  return blocks.map((block) => {
    if (block.children) {
      return {
        ...block,
        children: moveBlockInTree(block.children, activeId, overId),
      };
    }

    if (block.type === 'columns' && block.content.columns) {
      const newColumns = block.content.columns.map((col: any) => ({
        ...col,
        content: col.content ? {
          ...col.content,
          blocks: moveBlockInTree(col.content.blocks || [], activeId, overId)
        } : col.content
      }));
      return { ...block, content: { ...block.content, columns: newColumns } };
    }

    return block;
  });
};

export const PageBuilderProvider: React.FC<{ page: Page; children: React.ReactNode }> = ({ page, children }) => {
  const [blocks, setBlocksState] = useState<Block[]>(page.builder_content?.blocks || []);
  const [selectedBlockId, setSelectedBlockId] = useState<string | null>(null);
  const [isPreview, setIsPreview] = useState(false);
  const [history, setHistory] = useState<Block[][]>([page.builder_content?.blocks || []]);
  const [historyIndex, setHistoryIndex] = useState(0);

  const addToHistory = useCallback((newBlocks: Block[]) => {
    setHistory((prev) => {
      const newHistory = prev.slice(0, historyIndex + 1);
      return [...newHistory, newBlocks];
    });
    setHistoryIndex((prev) => prev + 1);
  }, [historyIndex]);

  const setBlocks = useCallback((newBlocks: Block[]) => {
    setBlocksState(newBlocks);
    addToHistory(newBlocks);
  }, [addToHistory]);

  const addBlock = useCallback((type: string, parentId: string | null = null, index?: number) => {
    const newBlock: Block = {
      id: uuidv4(),
      type: type as any,
      content: getDefaultContentForBlockType(type),
      settings: {},
      order: 0,
      parentId,
      children: type === 'section' || type === 'columns' ? [] : undefined,
    };

    setBlocksState((prev) => {
      const updatedBlocks = addBlockToTree(prev, newBlock, parentId, index);
      addToHistory(updatedBlocks);
      return updatedBlocks;
    });

    setSelectedBlockId(newBlock.id);
  }, [addToHistory]);

  const updateBlock = useCallback((id: string, content: Record<string, any>, settings?: Record<string, any>) => {
    setBlocksState((prev) => {
      const block = findBlockById(prev, id);
      if (!block) return prev;

      const updates: Partial<Block> = {
        content: { ...block.content, ...content },
      };
      if (settings) {
        updates.settings = { ...block.settings, ...settings };
      }

      const newBlocks = updateBlockInTree(prev, id, updates);
      addToHistory(newBlocks);
      return newBlocks;
    });
  }, [addToHistory]);

  const deleteBlock = useCallback((id: string) => {
    setBlocksState((prev) => {
      const newBlocks = removeBlockFromTree(prev, id);
      addToHistory(newBlocks);
      return newBlocks;
    });
    if (selectedBlockId === id) {
      setSelectedBlockId(null);
    }
  }, [selectedBlockId, addToHistory]);

  const duplicateBlock = useCallback((id: string) => {
    setBlocksState((prev) => {
      const block = findBlockById(prev, id);
      if (!block) return prev;

      const newBlock: Block = JSON.parse(JSON.stringify(block));
      newBlock.id = uuidv4();
      // Recursive ID update for children
      const updateChildrenIds = (b: Block) => {
        if (b.children) {
          b.children = b.children.map(child => {
            const newChild = { ...child, id: uuidv4() };
            updateChildrenIds(newChild);
            return newChild;
          });
        }
      };
      updateChildrenIds(newBlock);

      const updatedBlocks = addBlockToTree(prev, newBlock, block.parentId || null, block.order + 1);
      addToHistory(updatedBlocks);
      return updatedBlocks;
    });
  }, [addToHistory]);

  const moveBlock = useCallback((activeId: string, overId: string) => {
    setBlocksState((prev) => {
      const newBlocks = moveBlockInTree(prev, activeId, overId);
      addToHistory(newBlocks);
      return newBlocks;
    });
  }, [addToHistory]);

  const undo = useCallback(() => {
    if (historyIndex > 0) {
      const newIndex = historyIndex - 1;
      setHistoryIndex(newIndex);
      setBlocksState(history[newIndex]);
    }
  }, [history, historyIndex]);

  const redo = useCallback(() => {
    if (historyIndex < history.length - 1) {
      const newIndex = historyIndex + 1;
      setHistoryIndex(newIndex);
      setBlocksState(history[newIndex]);
    }
  }, [history, historyIndex]);

  const value = useMemo(() => ({
    page,
    blocks,
    selectedBlockId,
    isPreview,
    history,
    historyIndex,
    setBlocks,
    setSelectedBlockId,
    setIsPreview,
    addBlock,
    updateBlock,
    deleteBlock,
    duplicateBlock,
    moveBlock,
    undo,
    redo,
    canUndo: historyIndex > 0,
    canRedo: historyIndex < history.length - 1,
  }), [
    page,
    blocks,
    selectedBlockId,
    isPreview,
    history,
    historyIndex,
    setBlocks,
    setSelectedBlockId,
    setIsPreview,
    addBlock,
    updateBlock,
    deleteBlock,
    duplicateBlock,
    moveBlock,
    undo,
    redo
  ]);

  return (
    <PageBuilderContext.Provider value={value}>
      {children}
    </PageBuilderContext.Provider>
  );
};

function getDefaultContentForBlockType(type: string): Record<string, any> {
  switch (type) {
    case 'text':
      return { text: '' };
    case 'heading':
      return { text: 'Heading', level: 2 };
    case 'image':
      return { url: '', alt: '' };
    case 'video':
      return { url: '', platform: 'youtube' };
    case 'columns':
      return { columns: [{ content: { blocks: [] } }, { content: { blocks: [] } }] };
    case 'spacer':
      return { height: 50 };
    case 'separator':
      return {};
    case 'section':
      return { backgroundColor: '#ffffff' };
    case 'html':
      return { html: '' };
    case 'sipp_schedule':
      return { title: 'Jadwal Sidang Hari Ini' };
    case 'news_grid':
      return { title: 'Berita Terkini' };
    case 'document_list':
      return { title: 'Daftar Dokumen' };
    default:
      return {};
  }
}
