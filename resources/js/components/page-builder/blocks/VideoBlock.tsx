import React from 'react';
import { BlockComponentProps } from '../types';
import { Video } from 'lucide-react';

const VideoBlock: React.FC<BlockComponentProps> = ({ block }) => {
  const url = block.content?.url;
  const platform = block.content?.platform || 'youtube';

  const getEmbedUrl = () => {
    if (!url) return null;

    if (platform === 'youtube') {
      const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
      return match ? `https://www.youtube.com/embed/${match[1]}` : null;
    }

    if (platform === 'vimeo') {
      const match = url.match(/vimeo\.com\/(\d+)/);
      return match ? `https://player.vimeo.com/video/${match[1]}` : null;
    }

    return url;
  };

  const embedUrl = getEmbedUrl();

  if (!embedUrl) {
    return (
      <div className="p-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center">
        <Video className="h-12 w-12 text-gray-400 mb-2" />
        <p className="text-sm text-gray-500">No video URL provided</p>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
      <div className="aspect-video">
        <iframe
          src={embedUrl}
          className="w-full h-full"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowFullScreen
        />
      </div>
    </div>
  );
};

export default VideoBlock;
