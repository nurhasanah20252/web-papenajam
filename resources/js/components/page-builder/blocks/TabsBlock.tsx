import React, { useState } from 'react';
import { BlockComponentProps } from '../types';
import { Plus, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface TabItem {
  id: string;
  label: string;
  content: string;
}

const TabsBlock: React.FC<BlockComponentProps> = ({ block, onUpdate, isSelected }) => {
  const items: TabItem[] = block.content?.items || [
    { id: '1', label: 'Tab 1', content: 'Content for tab 1' },
  ];
  const [activeTab, setActiveTab] = useState(items[0]?.id || '');

  if (isSelected) {
    const handleAddTab = () => {
      const newItems: TabItem[] = [
        ...items,
        { id: Date.now().toString(), label: `Tab ${items.length + 1}`, content: '' },
      ];
      onUpdate?.({ items: newItems });
    };

    const handleRemoveTab = (id: string) => {
      const newItems = items.filter((item: TabItem) => item.id !== id);
      onUpdate?.({ items: newItems });
    };

    const handleUpdateTab = (id: string, field: keyof TabItem, value: string) => {
      const newItems = items.map((item: TabItem) =>
        item.id === id ? { ...item, [field]: value } : item
      );
      onUpdate?.({ items: newItems });
    };

    return (
      <div className="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <div className="flex items-center justify-between mb-4">
          <h3 className="text-lg font-semibold">Tabs Settings</h3>
          <Button type="button" size="sm" onClick={handleAddTab}>
            <Plus className="h-4 w-4 mr-2" />
            Add Tab
          </Button>
        </div>

        <div className="space-y-4">
          {items.map((item: TabItem, index: number) => (
            <div key={item.id} className="border border-gray-200 rounded-lg p-4">
              <div className="flex items-center justify-between mb-3">
                <span className="text-sm font-medium">Tab {index + 1}</span>
                <Button
                  type="button"
                  variant="ghost"
                  size="sm"
                  onClick={() => handleRemoveTab(item.id)}
                >
                  <Trash2 className="h-4 w-4" />
                </Button>
              </div>
              <div className="space-y-3">
                <div>
                  <Label htmlFor={`label-${item.id}`}>Tab Label</Label>
                  <Input
                    id={`label-${item.id}`}
                    value={item.label}
                    onChange={(e) => handleUpdateTab(item.id, 'label', e.target.value)}
                    placeholder="Tab label"
                  />
                </div>
                <div>
                  <Label htmlFor={`content-${item.id}`}>Tab Content</Label>
                  <Textarea
                    id={`content-${item.id}`}
                    value={item.content}
                    onChange={(e) => handleUpdateTab(item.id, 'content', e.target.value)}
                    placeholder="Tab content"
                    rows={4}
                  />
                </div>
              </div>
            </div>
          ))}
        </div>

        {items.length === 0 && (
          <div className="text-center py-8 text-gray-500">
            <p>No tabs added yet</p>
            <p className="text-sm">Click "Add Tab" to get started</p>
          </div>
        )}
      </div>
    );
  }

  if (items.length === 0) {
    return (
      <div className="p-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 text-center">
        <p className="text-sm text-gray-500">No tabs</p>
      </div>
    );
  }

  const activeItem = items.find((item: TabItem) => item.id === activeTab) || items[0];

  return (
    <div className="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
      {/* Tab Headers */}
      <div className="flex border-b border-gray-200 bg-gray-50">
        {items.map((item: TabItem) => (
          <button
            key={item.id}
            onClick={() => setActiveTab(item.id)}
            className={`px-6 py-3 font-medium transition-colors ${
              activeTab === item.id
                ? 'bg-white text-blue-600 border-b-2 border-blue-600'
                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
            }`}
          >
            {item.label}
          </button>
        ))}
      </div>

      {/* Tab Content */}
      <div className="p-6">
        <div className="prose prose-sm max-w-none">
          {activeItem?.content || <p className="text-gray-500">No content</p>}
        </div>
      </div>
    </div>
  );
};

export default TabsBlock;
