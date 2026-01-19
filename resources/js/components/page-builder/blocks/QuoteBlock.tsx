import React from 'react';
import { BlockComponentProps } from '../types';
import { Quote } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';

const QuoteBlock: React.FC<BlockComponentProps> = ({ block, onUpdate, isSelected }) => {
  const text = block.content?.text || 'The only way to do great work is to love what you do.';
  const author = block.content?.author || 'Steve Jobs';
  const role = block.content?.role || '';
  const variant = block.content?.variant || 'default';
  const align = block.content?.align || 'left';

  if (isSelected) {
    return (
      <div className="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <h3 className="text-lg font-semibold mb-4">Quote Settings</h3>
        <div className="space-y-4">
          <div>
            <Label htmlFor="quote-text">Quote Text</Label>
            <Textarea
              id="quote-text"
              value={text}
              onChange={(e) =>
                onUpdate?.({
                  text: e.target.value,
                  author,
                  role,
                  variant,
                  align,
                })
              }
              placeholder="The quote text..."
              rows={3}
            />
          </div>
          <div>
            <Label htmlFor="quote-author">Author</Label>
            <Input
              id="quote-author"
              value={author}
              onChange={(e) =>
                onUpdate?.({
                  text,
                  author: e.target.value,
                  role,
                  variant,
                  align,
                })
              }
              placeholder="Author name"
            />
          </div>
          <div>
            <Label htmlFor="quote-role">Role/Title (optional)</Label>
            <Input
              id="quote-role"
              value={role}
              onChange={(e) =>
                onUpdate?.({
                  text,
                  author,
                  role: e.target.value,
                  variant,
                  align,
                })
              }
              placeholder="CEO, Company Name"
            />
          </div>
          <div>
            <Label htmlFor="quote-variant">Style</Label>
            <Select
              value={variant}
              onValueChange={(value) =>
                onUpdate?.({
                  text,
                  author,
                  role,
                  variant: value,
                  align,
                })
              }
            >
              <SelectTrigger id="quote-variant">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="default">Default</SelectItem>
                <SelectItem value="modern">Modern</SelectItem>
                <SelectItem value="classic">Classic</SelectItem>
                <SelectItem value="minimal">Minimal</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div>
            <Label htmlFor="quote-align">Alignment</Label>
            <Select
              value={align}
              onValueChange={(value) =>
                onUpdate?.({
                  text,
                  author,
                  role,
                  variant,
                  align: value,
                })
              }
            >
              <SelectTrigger id="quote-align">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="left">Left</SelectItem>
                <SelectItem value="center">Center</SelectItem>
                <SelectItem value="right">Right</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </div>
    );
  }

  const getVariantStyles = () => {
    switch (variant) {
      case 'modern':
        return 'bg-gradient-to-br from-blue-50 to-indigo-50 border-l-4 border-blue-600';
      case 'classic':
        return 'bg-amber-50 border-l-4 border-amber-600';
      case 'minimal':
        return 'border-l-2 border-gray-300';
      default:
        return 'bg-gray-50 border-l-4 border-gray-600';
    }
  };

  const getAlignmentClass = () => {
    switch (align) {
      case 'center':
        return 'text-center';
      case 'right':
        return 'text-right';
      default:
        return 'text-left';
    }
  };

  return (
    <div className={`rounded-lg p-6 ${getVariantStyles()} ${getAlignmentClass()}`}>
      <div className="flex items-start gap-4">
        {variant !== 'minimal' && (
          <Quote className="h-8 w-8 text-gray-400 flex-shrink-0 mt-1" />
        )}
        <div className="flex-1">
          <blockquote className="text-lg italic text-gray-800 mb-4">"{text}"</blockquote>
          {author && (
            <div className="not-italic">
              <cite className="font-semibold text-gray-900 not-italic">{author}</cite>
              {role && <span className="text-gray-600 text-sm"> - {role}</span>}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default QuoteBlock;
