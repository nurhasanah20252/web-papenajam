import React from 'react';
import { BlockComponentProps } from '../types';
import { Image as ImageIcon } from 'lucide-react';
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

const CardBlock: React.FC<BlockComponentProps> = ({ block, onUpdate, isSelected }) => {
  const title = block.content?.title || 'Card Title';
  const description = block.content?.description || 'Card description goes here';
  const imageUrl = block.content?.imageUrl || '';
  const imageAlt = block.content?.imageAlt || '';
  const buttonText = block.content?.buttonText || '';
  const buttonUrl = block.content?.buttonUrl || '';
  const variant = block.content?.variant || 'default';

  if (isSelected) {
    return (
      <div className="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <h3 className="text-lg font-semibold mb-4">Card Settings</h3>
        <div className="space-y-4">
          <div>
            <Label htmlFor="card-title">Title</Label>
            <Input
              id="card-title"
              value={title}
              onChange={(e) =>
                onUpdate?.({
                  ...block.content,
                  title: e.target.value,
                })
              }
              placeholder="Card Title"
            />
          </div>
          <div>
            <Label htmlFor="card-description">Description</Label>
            <Textarea
              id="card-description"
              value={description}
              onChange={(e) =>
                onUpdate?.({
                  ...block.content,
                  description: e.target.value,
                })
              }
              placeholder="Card description goes here"
              rows={3}
            />
          </div>
          <div>
            <Label htmlFor="card-image">Image URL</Label>
            <Input
              id="card-image"
              value={imageUrl}
              onChange={(e) =>
                onUpdate?.({
                  ...block.content,
                  imageUrl: e.target.value,
                })
              }
              placeholder="https://example.com/image.jpg"
            />
          </div>
          <div>
            <Label htmlFor="card-image-alt">Image Alt Text</Label>
            <Input
              id="card-image-alt"
              value={imageAlt}
              onChange={(e) =>
                onUpdate?.({
                  ...block.content,
                  imageAlt: e.target.value,
                })
              }
              placeholder="Image description"
            />
          </div>
          <div>
            <Label htmlFor="card-button-text">Button Text</Label>
            <Input
              id="card-button-text"
              value={buttonText}
              onChange={(e) =>
                onUpdate?.({
                  ...block.content,
                  buttonText: e.target.value,
                })
              }
              placeholder="Learn More"
            />
          </div>
          <div>
            <Label htmlFor="card-button-url">Button URL</Label>
            <Input
              id="card-button-url"
              value={buttonUrl}
              onChange={(e) =>
                onUpdate?.({
                  ...block.content,
                  buttonUrl: e.target.value,
                })
              }
              placeholder="https://example.com"
            />
          </div>
          <div>
            <Label htmlFor="card-variant">Variant</Label>
            <Select
              value={variant}
              onValueChange={(value) =>
                onUpdate?.({
                  ...block.content,
                  variant: value,
                })
              }
            >
              <SelectTrigger id="card-variant">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="default">Default</SelectItem>
                <SelectItem value="outlined">Outlined</SelectItem>
                <SelectItem value="elevated">Elevated</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </div>
    );
  }

  const getCardVariant = () => {
    switch (variant) {
      case 'outlined':
        return 'border-2 border-gray-200 shadow-none';
      case 'elevated':
        return 'shadow-lg';
      default:
        return 'shadow-md';
    }
  };

  return (
    <div className={`bg-white rounded-lg ${getCardVariant()} overflow-hidden max-w-sm mx-auto`}>
      {imageUrl ? (
        <img src={imageUrl} alt={imageAlt || title} className="w-full h-48 object-cover" />
      ) : (
        <div className="w-full h-48 bg-gray-100 flex items-center justify-center">
          <ImageIcon className="h-12 w-12 text-gray-400" />
        </div>
      )}
      <div className="p-6">
        <h3 className="text-xl font-semibold mb-2">{title}</h3>
        <p className="text-gray-600 mb-4">{description}</p>
        {buttonText && buttonUrl && (
          <a
            href={buttonUrl}
            className="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            {buttonText}
          </a>
        )}
      </div>
    </div>
  );
};

export default CardBlock;
