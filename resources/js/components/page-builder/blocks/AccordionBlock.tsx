import React, { useState } from 'react';
import { BlockComponentProps } from '../types';
import { ChevronDown, Plus, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface AccordionItem {
  id: string;
  title: string;
  content: string;
}

const AccordionBlock: React.FC<BlockComponentProps> = ({ block, onUpdate, isSelected }) => {
  const items: AccordionItem[] = block.content?.items || [
    { id: '1', title: 'Item 1', content: 'Content for item 1' },
  ];
  const allowMultiple = block.content?.allowMultiple || false;

  const [openItems, setOpenItems] = useState<Set<string>>(new Set());

  const toggleItem = (id: string) => {
    setOpenItems((prev) => {
      const newSet = new Set(prev);
      if (allowMultiple) {
        if (newSet.has(id)) {
          newSet.delete(id);
        } else {
          newSet.add(id);
        }
      } else {
        newSet.clear();
        newSet.add(id);
      }
      return newSet;
    });
  };

  if (isSelected) {
    const handleAddItem = () => {
      const newItems: AccordionItem[] = [
        ...items,
        { id: Date.now().toString(), title: `Item ${items.length + 1}`, content: '' },
      ];
      onUpdate?.({ items: newItems, allowMultiple });
    };

    const handleRemoveItem = (id: string) => {
      const newItems = items.filter((item: AccordionItem) => item.id !== id);
      onUpdate?.({ items: newItems, allowMultiple });
    };

    const handleUpdateItem = (id: string, field: keyof AccordionItem, value: string) => {
      const newItems = items.map((item: AccordionItem) =>
        item.id === id ? { ...item, [field]: value } : item
      );
      onUpdate?.({ items: newItems, allowMultiple });
    };

    return (
      <div className="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <div className="flex items-center justify-between mb-4">
          <h3 className="text-lg font-semibold">Accordion Settings</h3>
          <Button type="button" size="sm" onClick={handleAddItem}>
            <Plus className="h-4 w-4 mr-2" />
            Add Item
          </Button>
        </div>

        <div className="flex items-center space-x-2 mb-4">
          <input
            type="checkbox"
            id="allow-multiple"
            checked={allowMultiple}
            onChange={(e) => onUpdate?.({ items, allowMultiple: e.target.checked })}
            className="rounded"
          />
          <Label htmlFor="allow-multiple">Allow multiple items open</Label>
        </div>

        <div className="space-y-4">
          {items.map((item: AccordionItem, index: number) => (
            <div key={item.id} className="border border-gray-200 rounded-lg p-4">
              <div className="flex items-center justify-between mb-3">
                <span className="text-sm font-medium">Item {index + 1}</span>
                <Button
                  type="button"
                  variant="ghost"
                  size="sm"
                  onClick={() => handleRemoveItem(item.id)}
                >
                  <Trash2 className="h-4 w-4" />
                </Button>
              </div>
              <div className="space-y-3">
                <div>
                  <Label htmlFor={`title-${item.id}`}>Title</Label>
                  <Input
                    id={`title-${item.id}`}
                    value={item.title}
                    onChange={(e) => handleUpdateItem(item.id, 'title', e.target.value)}
                    placeholder="Item title"
                  />
                </div>
                <div>
                  <Label htmlFor={`content-${item.id}`}>Content</Label>
                  <Textarea
                    id={`content-${item.id}`}
                    value={item.content}
                    onChange={(e) => handleUpdateItem(item.id, 'content', e.target.value)}
                    placeholder="Item content"
                    rows={3}
                  />
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    );
  }

  if (items.length === 0) {
    return (
      <div className="p-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 text-center">
        <p className="text-sm text-gray-500">No accordion items</p>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
      {items.map((item: AccordionItem, index: number) => (
        <div key={item.id} className={index > 0 ? 'border-t border-gray-200' : ''}>
          <button
            onClick={() => toggleItem(item.id)}
            className="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition-colors"
          >
            <span className="font-medium">{item.title}</span>
            <ChevronDown
              className={`h-5 w-5 text-gray-400 transition-transform ${
                openItems.has(item.id) ? 'transform rotate-180' : ''
              }`}
            />
          </button>
          {openItems.has(item.id) && (
            <div className="px-6 py-4 bg-gray-50 text-gray-700">{item.content}</div>
          )}
        </div>
      ))}
    </div>
  );
};

export default AccordionBlock;
