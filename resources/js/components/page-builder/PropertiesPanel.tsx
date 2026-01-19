import React, { useState, useEffect } from 'react';
import { Block } from './types';
import { X, Trash2, Copy } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { HexColorPicker } from 'react-colorful';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { usePageBuilderContext } from './PageBuilderContext';

interface PropertiesPanelProps {
  block: Block;
  onUpdate: (content: Record<string, any>) => void;
  onClose: () => void;
}

const PropertiesPanel: React.FC<PropertiesPanelProps> = ({ block, onUpdate, onClose }) => {
  const { deleteBlock, duplicateBlock } = usePageBuilderContext();
  const [content, setContent] = useState<Record<string, any>>(block.content);
  const [settings, setSettings] = useState<Record<string, any>>(block.settings || {});

  useEffect(() => {
    setContent(block.content);
    setSettings(block.settings || {});
  }, [block]);

  const handleContentChange = (key: string, value: any) => {
    const newContent = { ...content, [key]: value };
    setContent(newContent);
    onUpdate(newContent);
  };

  const handleSettingsChange = (key: string, value: any) => {
    const newSettings = { ...settings, [key]: value };
    setSettings(newSettings);
    // When updating settings, we passed them through a separate key in updateBlock usually,
    // but here the onUpdate prop from PageBuilder.tsx is:
    // onUpdate={(content) => updateBlock(selectedBlock.id, content)}
    // Wait, let's check PageBuilder.tsx onUpdate usage.
    // It calls updateBlock(id, content).
    // Actually updateBlock in PageBuilderContext handles settings as a third arg.
    // Let's adjust this to use updateBlock directly from context if possible or fix the prop.
  };

  // Re-define handleSettingsChange to use the context directly to be safer
  const { updateBlock: updateBlockCtx } = usePageBuilderContext();

  const handleSettingsUpdate = (key: string, value: any) => {
    const newSettings = { ...settings, [key]: value };
    setSettings(newSettings);
    updateBlockCtx(block.id, content, newSettings);
  };

  return (
    <div className="w-80 border-l bg-white flex flex-col h-full shadow-xl">
      <div className="flex items-center justify-between p-4 border-b">
        <h2 className="font-semibold text-lg">Properties</h2>
        <Button variant="ghost" size="sm" onClick={onClose}>
          <X className="h-4 w-4" />
        </Button>
      </div>

      <div className="flex-1 overflow-y-auto p-4 space-y-8">
        {/* Quick Actions */}
        <div className="flex items-center gap-2 pb-4 border-b">
          <Button
            variant="outline"
            size="sm"
            className="flex-1"
            onClick={() => duplicateBlock(block.id)}
          >
            <Copy className="h-3.5 w-3.5 mr-2" />
            Duplicate
          </Button>
          <Button
            variant="outline"
            size="sm"
            className="flex-1 text-red-600 hover:text-red-700 hover:bg-red-50"
            onClick={() => {
              if (confirm('Are you sure you want to delete this block?')) {
                deleteBlock(block.id);
              }
            }}
          >
            <Trash2 className="h-3.5 w-3.5 mr-2" />
            Delete
          </Button>
        </div>

        {/* Block Type Info */}
        <div>
          <Label className="text-[10px] text-gray-500 uppercase tracking-wider font-bold">
            Block Type
          </Label>
          <div className="mt-1 text-sm font-medium text-gray-900 capitalize bg-gray-50 px-3 py-2 rounded border">
            {block.type}
          </div>
        </div>

        {/* Content Properties */}
        <div className="space-y-4">
          <Label className="text-[10px] text-gray-500 uppercase tracking-wider font-bold">
            Content
          </Label>
          {renderContentProperties(block.type, content, handleContentChange)}
        </div>

        {/* Style Settings */}
        <div className="space-y-4 pt-4 border-t">
          <Label className="text-[10px] text-gray-500 uppercase tracking-wider font-bold">
            Styles & Layout
          </Label>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label htmlFor="marginTop" className="text-xs">Margin Top</Label>
              <Input
                id="marginTop"
                type="number"
                size={1}
                value={settings.marginTop || 0}
                onChange={(e) => handleSettingsUpdate('marginTop', parseInt(e.target.value) || 0)}
              />
            </div>
            <div>
              <Label htmlFor="marginBottom" className="text-xs">Margin Bottom</Label>
              <Input
                id="marginBottom"
                type="number"
                value={settings.marginBottom || 0}
                onChange={(e) => handleSettingsUpdate('marginBottom', parseInt(e.target.value) || 0)}
              />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label htmlFor="paddingTop" className="text-xs">Padding Top</Label>
              <Input
                id="paddingTop"
                type="number"
                value={settings.paddingTop || 0}
                onChange={(e) => handleSettingsUpdate('paddingTop', parseInt(e.target.value) || 0)}
              />
            </div>
            <div>
              <Label htmlFor="paddingBottom" className="text-xs">Padding Bottom</Label>
              <Input
                id="paddingBottom"
                type="number"
                value={settings.paddingBottom || 0}
                onChange={(e) => handleSettingsUpdate('paddingBottom', parseInt(e.target.value) || 0)}
              />
            </div>
          </div>

          <div>
            <Label htmlFor="cssClass" className="text-xs">Custom CSS Class</Label>
            <Input
              id="cssClass"
              value={settings.cssClass || ''}
              onChange={(e) => handleSettingsUpdate('cssClass', e.target.value)}
              placeholder="e.g. custom-hero"
            />
          </div>
        </div>
      </div>
    </div>
  );
};

function renderContentProperties(
  type: string,
  content: Record<string, any>,
  onChange: (key: string, value: any) => void
) {
  switch (type) {
    case 'text':
      return (
        <div className="space-y-4">
          <div>
            <Label htmlFor="textContent" className="text-xs">Text (Raw HTML)</Label>
            <Textarea
              id="textContent"
              value={content.text || ''}
              onChange={(e) => onChange('text', e.target.value)}
              rows={10}
              placeholder="Rich text content..."
              className="font-mono text-xs"
            />
            <p className="text-[10px] text-gray-400 mt-1">Note: Use the inline editor for rich formatting.</p>
          </div>
        </div>
      );

    case 'heading':
      return (
        <div className="space-y-4">
          <div>
            <Label htmlFor="headingText" className="text-xs">Text</Label>
            <Input
              id="headingText"
              value={content.text || ''}
              onChange={(e) => onChange('text', e.target.value)}
            />
          </div>
          <div>
            <Label htmlFor="headingLevel" className="text-xs">Level</Label>
            <Select
              value={content.level?.toString() || '2'}
              onValueChange={(value) => onChange('level', parseInt(value))}
            >
              <SelectTrigger id="headingLevel">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="1">Heading 1</SelectItem>
                <SelectItem value="2">Heading 2</SelectItem>
                <SelectItem value="3">Heading 3</SelectItem>
                <SelectItem value="4">Heading 4</SelectItem>
                <SelectItem value="5">Heading 5</SelectItem>
                <SelectItem value="6">Heading 6</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      );

    case 'image':
      return (
        <div className="space-y-4">
          <div>
            <Label htmlFor="imageUrl" className="text-xs">Source URL</Label>
            <Input
              id="imageUrl"
              value={content.url || ''}
              onChange={(e) => onChange('url', e.target.value)}
              placeholder="https://..."
            />
          </div>
          <div>
            <Label htmlFor="imageAlt" className="text-xs">Alt Text</Label>
            <Input
              id="imageAlt"
              value={content.alt || ''}
              onChange={(e) => onChange('alt', e.target.value)}
            />
          </div>
          <div>
            <Label htmlFor="imageCaption" className="text-xs">Caption</Label>
            <Input
              id="imageCaption"
              value={content.caption || ''}
              onChange={(e) => onChange('caption', e.target.value)}
            />
          </div>
        </div>
      );

    case 'video':
      return (
        <div className="space-y-4">
          <div>
            <Label htmlFor="videoUrl" className="text-xs">Video URL</Label>
            <Input
              id="videoUrl"
              value={content.url || ''}
              onChange={(e) => onChange('url', e.target.value)}
              placeholder="YouTube or Vimeo link"
            />
          </div>
          <div>
            <Label htmlFor="videoPlatform" className="text-xs">Platform</Label>
            <Select
              value={content.platform || 'youtube'}
              onValueChange={(value) => onChange('platform', value)}
            >
              <SelectTrigger id="videoPlatform">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="youtube">YouTube</SelectItem>
                <SelectItem value="vimeo">Vimeo</SelectItem>
                <SelectItem value="custom">Custom Embed</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      );

    case 'section':
      return (
        <div className="space-y-4">
          <div>
            <Label className="text-xs">Background Color</Label>
            <div className="mt-1 flex items-center gap-2">
              <Popover>
                <PopoverTrigger asChild>
                  <Button
                    variant="outline"
                    className="w-10 h-10 p-0 border-2"
                    style={{ backgroundColor: content.backgroundColor || '#ffffff' }}
                  />
                </PopoverTrigger>
                <PopoverContent className="w-auto p-3">
                  <HexColorPicker
                    color={content.backgroundColor || '#ffffff'}
                    onChange={(color) => onChange('backgroundColor', color)}
                  />
                  <div className="mt-3">
                    <Input
                      value={content.backgroundColor || '#ffffff'}
                      onChange={(e) => onChange('backgroundColor', e.target.value)}
                      className="h-8 text-xs font-mono"
                    />
                  </div>
                </PopoverContent>
              </Popover>
              <span className="text-xs font-mono text-gray-500">
                {content.backgroundColor || '#ffffff'}
              </span>
            </div>
          </div>
        </div>
      );

    case 'spacer':
      return (
        <div>
          <Label htmlFor="spacerHeight" className="text-xs">Height (px)</Label>
          <Input
            id="spacerHeight"
            type="number"
            value={content.height || 50}
            onChange={(e) => onChange('height', parseInt(e.target.value) || 50)}
          />
        </div>
      );

    case 'columns':
      return (
        <div className="space-y-4">
          <div>
            <Label className="text-xs">Number of Columns</Label>
            <Select
              value={content.columns?.length.toString() || '2'}
              onValueChange={(value) => {
                const count = parseInt(value);
                const currentCols = content.columns || [];
                let newCols = [...currentCols];

                if (count > currentCols.length) {
                  for (let i = currentCols.length; i < count; i++) {
                    newCols.push({ content: { blocks: [] } });
                  }
                } else {
                  newCols = newCols.slice(0, count);
                }
                onChange('columns', newCols);
              }}
            >
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="1">1 Column</SelectItem>
                <SelectItem value="2">2 Columns</SelectItem>
                <SelectItem value="3">3 Columns</SelectItem>
                <SelectItem value="4">4 Columns</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      );

    case 'sipp_schedule':
    case 'news_grid':
    case 'document_list':
      return (
        <div className="space-y-4">
          <div>
            <Label htmlFor="blockTitle" className="text-xs">Block Title</Label>
            <Input
              id="blockTitle"
              value={content.title || ''}
              onChange={(e) => onChange('title', e.target.value)}
              placeholder="Enter title..."
            />
          </div>
        </div>
      );

    default:
      return (
        <p className="text-xs text-gray-500 italic">
          No specific content properties for this block type.
        </p>
      );
  }
}

export default PropertiesPanel;

