import React, { useState } from 'react';
import { BlockComponentProps } from '../types';
import { Image as ImageIcon, Plus, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface GalleryImage {
  url: string;
  alt?: string;
  caption?: string;
}

const GalleryBlock: React.FC<BlockComponentProps> = ({ block, onUpdate, isSelected }) => {
  const images = block.content?.images || [];
  const columns = block.content?.columns || 3;
  const gap = block.content?.gap || 4;

  const handleAddImage = () => {
    const newImages: GalleryImage[] = [...images, { url: '', alt: '', caption: '' }];
    onUpdate?.({ images: newImages, columns, gap });
  };

  const handleRemoveImage = (index: number) => {
    const newImages = images.filter((_: any, i: number) => i !== index);
    onUpdate?.({ images: newImages, columns, gap });
  };

  const handleImageChange = (index: number, field: keyof GalleryImage, value: string) => {
    const newImages = images.map((img: any, i: number) =>
      i === index ? { ...img, [field]: value } : img
    );
    onUpdate?.({ images: newImages, columns, gap });
  };

  if (isSelected) {
    return (
      <div className="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <div className="flex items-center justify-between mb-4">
          <h3 className="text-lg font-semibold">Gallery Settings</h3>
          <Button type="button" size="sm" onClick={handleAddImage}>
            <Plus className="h-4 w-4 mr-2" />
            Add Image
          </Button>
        </div>

        <div className="space-y-4 mb-6">
          <div>
            <Label htmlFor="columns">Columns: {columns}</Label>
            <input
              id="columns"
              type="range"
              min="1"
              max="5"
              value={columns}
              onChange={(e) => onUpdate?.({ images, columns: parseInt(e.target.value), gap })}
              className="w-full mt-2"
            />
          </div>
          <div>
            <Label htmlFor="gap">Gap Size: {gap}</Label>
            <input
              id="gap"
              type="range"
              min="0"
              max="8"
              value={gap}
              onChange={(e) => onUpdate?.({ images, columns, gap: parseInt(e.target.value) })}
              className="w-full mt-2"
            />
          </div>
        </div>

        <div className="space-y-4">
          {images.map((image: GalleryImage, index: number) => (
            <div key={index} className="border border-gray-200 rounded-lg p-4">
              <div className="flex items-center justify-between mb-3">
                <span className="text-sm font-medium">Image {index + 1}</span>
                <Button
                  type="button"
                  variant="ghost"
                  size="sm"
                  onClick={() => handleRemoveImage(index)}
                >
                  <X className="h-4 w-4" />
                </Button>
              </div>
              <div className="space-y-3">
                <div>
                  <Label htmlFor={`url-${index}`}>Image URL</Label>
                  <Input
                    id={`url-${index}`}
                    value={image.url}
                    onChange={(e) => handleImageChange(index, 'url', e.target.value)}
                    placeholder="https://example.com/image.jpg"
                  />
                </div>
                <div>
                  <Label htmlFor={`alt-${index}`}>Alt Text</Label>
                  <Input
                    id={`alt-${index}`}
                    value={image.alt || ''}
                    onChange={(e) => handleImageChange(index, 'alt', e.target.value)}
                    placeholder="Image description"
                  />
                </div>
                <div>
                  <Label htmlFor={`caption-${index}`}>Caption</Label>
                  <Input
                    id={`caption-${index}`}
                    value={image.caption || ''}
                    onChange={(e) => handleImageChange(index, 'caption', e.target.value)}
                    placeholder="Optional caption"
                  />
                </div>
              </div>
            </div>
          ))}
        </div>

        {images.length === 0 && (
          <div className="text-center py-8 text-gray-500">
            <ImageIcon className="h-12 w-12 mx-auto mb-2 text-gray-400" />
            <p>No images added yet</p>
            <p className="text-sm">Click "Add Image" to get started</p>
          </div>
        )}
      </div>
    );
  }

  if (images.length === 0) {
    return (
      <div className="p-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center">
        <ImageIcon className="h-12 w-12 text-gray-400 mb-2" />
        <p className="text-sm text-gray-500">No images in gallery</p>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden p-4">
      <div
        className="grid gap-4"
        style={{
          gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))`,
          gap: `${gap * 0.25}rem`,
        }}
      >
        {images.map((image: GalleryImage, index: number) => (
          <div key={index} className="relative group">
            {image.url ? (
              <>
                <img
                  src={image.url}
                  alt={image.alt || `Gallery image ${index + 1}`}
                  className="w-full h-48 object-cover rounded-lg"
                />
                {image.caption && (
                  <div className="mt-2 text-sm text-gray-600 text-center">{image.caption}</div>
                )}
              </>
            ) : (
              <div className="h-48 bg-gray-100 rounded-lg flex items-center justify-center">
                <ImageIcon className="h-8 w-8 text-gray-400" />
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  );
};

export default GalleryBlock;
