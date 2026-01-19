import React from 'react';
import { BlockComponentProps } from '../types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';

const ButtonBlock: React.FC<BlockComponentProps> = ({ block, onUpdate, isSelected }) => {
  const text = block.content?.text || 'Click me';
  const url = block.content?.url || '#';
  const variant = block.content?.variant || 'default';
  const size = block.content?.size || 'default';
  const align = block.content?.align || 'left';
  const newTab = block.content?.newTab || false;

  if (isSelected) {
    return (
      <div className="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <h3 className="text-lg font-semibold mb-4">Button Settings</h3>
        <div className="space-y-4">
          <div>
            <Label htmlFor="button-text">Button Text</Label>
            <Input
              id="button-text"
              value={text}
              onChange={(e) => onUpdate?.({ text: e.target.value, url, variant, size, align, newTab })}
              placeholder="Click me"
            />
          </div>
          <div>
            <Label htmlFor="button-url">URL</Label>
            <Input
              id="button-url"
              value={url}
              onChange={(e) => onUpdate?.({ text, url: e.target.value, variant, size, align, newTab })}
              placeholder="https://example.com"
            />
          </div>
          <div>
            <Label htmlFor="button-variant">Style</Label>
            <Select
              value={variant}
              onValueChange={(value) =>
                onUpdate?.({ text, url, variant: value, size, align, newTab })
              }
            >
              <SelectTrigger id="button-variant">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="default">Default</SelectItem>
                <SelectItem value="destructive">Destructive</SelectItem>
                <SelectItem value="outline">Outline</SelectItem>
                <SelectItem value="secondary">Secondary</SelectItem>
                <SelectItem value="ghost">Ghost</SelectItem>
                <SelectItem value="link">Link</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div>
            <Label htmlFor="button-size">Size</Label>
            <Select
              value={size}
              onValueChange={(value) =>
                onUpdate?.({ text, url, variant, size: value, align, newTab })
              }
            >
              <SelectTrigger id="button-size">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="default">Default</SelectItem>
                <SelectItem value="sm">Small</SelectItem>
                <SelectItem value="lg">Large</SelectItem>
                <SelectItem value="icon">Icon</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div>
            <Label htmlFor="button-align">Alignment</Label>
            <Select
              value={align}
              onValueChange={(value) =>
                onUpdate?.({ text, url, variant, size, align: value, newTab })
              }
            >
              <SelectTrigger id="button-align">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="left">Left</SelectItem>
                <SelectItem value="center">Center</SelectItem>
                <SelectItem value="right">Right</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div className="flex items-center space-x-2">
            <input
              type="checkbox"
              id="new-tab"
              checked={newTab}
              onChange={(e) =>
                onUpdate?.({ text, url, variant, size, align, newTab: e.target.checked })
              }
              className="rounded"
            />
            <Label htmlFor="new-tab">Open in new tab</Label>
          </div>
        </div>

        <div className="mt-6 p-4 bg-gray-50 rounded-lg">
          <p className="text-sm text-gray-600 mb-2">Preview:</p>
          <div className={`flex ${align === 'center' ? 'justify-center' : align === 'right' ? 'justify-end' : 'justify-start'}`}>
            <Button variant={variant as any} size={size as any} asChild>
              <a href={url} target={newTab ? '_blank' : undefined} rel={newTab ? 'noopener noreferrer' : undefined}>
                {text}
              </a>
            </Button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className={`flex ${align === 'center' ? 'justify-center' : align === 'right' ? 'justify-end' : 'justify-start'} p-4`}>
      <Button variant={variant as any} size={size as any} asChild>
        <a href={url} target={newTab ? '_blank' : undefined} rel={newTab ? 'noopener noreferrer' : undefined}>
          {text}
        </a>
      </Button>
    </div>
  );
};

export default ButtonBlock;
