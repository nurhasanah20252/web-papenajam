import React from 'react';
import { BlockComponentProps } from '../types';
import { Calendar, Tag, ChevronRight } from 'lucide-react';

interface NewsItem {
  id: string;
  title: string;
  excerpt: string;
  category: string;
  date: string;
  imageUrl: string;
}

const mockNews: NewsItem[] = [
  {
    id: '1',
    title: 'PA Penajam Meraih Penghargaan Pelayanan Publik Terbaik 2024',
    excerpt: 'Pengadilan Agama Penajam kembali mengukir prestasi dengan meraih penghargaan sebagai unit kerja dengan pelayanan publik terbaik...',
    category: 'Berita',
    date: '15 Jan 2025',
    imageUrl: 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?auto=format&fit=crop&w=400&q=80',
  },
  {
    id: '2',
    title: 'Sosialisasi E-Court dan Gugatan Mandiri bagi Masyarakat',
    excerpt: 'Dalam rangka meningkatkan akses keadilan, PA Penajam menyelenggarakan sosialisasi penggunaan aplikasi e-court kepada warga setempat...',
    category: 'Pengumuman',
    date: '12 Jan 2025',
    imageUrl: 'https://images.unsplash.com/photo-1505664194779-8beaceb93744?auto=format&fit=crop&w=400&q=80',
  },
  {
    id: '3',
    title: 'Pelantikan Wakil Ketua Pengadilan Agama Penajam yang Baru',
    excerpt: 'Ketua Pengadilan Agama Penajam secara resmi melantik pejabat baru untuk mengisi posisi Wakil Ketua yang telah kosong...',
    category: 'Berita',
    date: '10 Jan 2025',
    imageUrl: 'https://images.unsplash.com/photo-1521791136064-7986c29596ad?auto=format&fit=crop&w=400&q=80',
  },
];

const NewsGridBlock: React.FC<BlockComponentProps> = ({ block, onUpdate, isSelected }) => {
  const limit = block.settings?.limit || 3;
  const category = block.settings?.category || 'All';
  const columns = block.settings?.columns || 3;

  const filteredNews = category === 'All'
    ? mockNews
    : mockNews.filter(n => n.category === category);

  return (
    <div className="py-6">
      <div className={`grid grid-cols-1 md:grid-cols-${columns} gap-6`}>
        {filteredNews.slice(0, limit).map((news) => (
          <div key={news.id} className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow group">
            <div className="aspect-video w-full overflow-hidden bg-gray-100">
              <img
                src={news.imageUrl}
                alt={news.title}
                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              />
            </div>
            <div className="p-4">
              <div className="flex items-center gap-3 mb-3">
                <span className="bg-blue-50 text-blue-600 text-xs font-bold px-2.5 py-1 rounded-full flex items-center gap-1">
                  <Tag className="h-3 w-3" />
                  {news.category}
                </span>
                <span className="text-gray-400 text-xs flex items-center gap-1">
                  <Calendar className="h-3 w-3" />
                  {news.date}
                </span>
              </div>
              <h4 className="font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                {news.title}
              </h4>
              <p className="text-gray-600 text-sm line-clamp-3 mb-4">
                {news.excerpt}
              </p>
              <button className="text-blue-600 text-sm font-bold flex items-center gap-1 group/btn">
                Baca Selengkapnya
                <ChevronRight className="h-4 w-4 group-hover/btn:translate-x-1 transition-transform" />
              </button>
            </div>
          </div>
        ))}
      </div>

      {isSelected && (
        <div className="mt-6 p-4 border border-dashed border-gray-300 rounded-lg bg-gray-50/50">
          <p className="text-xs font-bold text-gray-400 mb-4 uppercase tracking-wider">News Grid Configuration</p>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label className="block text-xs font-medium text-gray-700 mb-1">Display Limit</label>
              <input
                type="number"
                min="1"
                max="12"
                className="w-full text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                value={limit}
                onChange={(e) => onUpdate?.(block.content, { ...block.settings, limit: parseInt(e.target.value) || 1 })}
              />
            </div>
            <div>
              <label className="block text-xs font-medium text-gray-700 mb-1">Filter Category</label>
              <select
                className="w-full text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                value={category}
                onChange={(e) => onUpdate?.(block.content, { ...block.settings, category: e.target.value })}
              >
                <option value="All">Semua Kategori</option>
                <option value="Berita">Berita</option>
                <option value="Pengumuman">Pengumuman</option>
                <option value="Artikel">Artikel</option>
              </select>
            </div>
            <div>
              <label className="block text-xs font-medium text-gray-700 mb-1">Columns</label>
              <select
                className="w-full text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                value={columns}
                onChange={(e) => onUpdate?.(block.content, { ...block.settings, columns: parseInt(e.target.value) || 3 })}
              >
                <option value={1}>1 Kolom</option>
                <option value={2}>2 Kolom</option>
                <option value={3}>3 Kolom</option>
                <option value={4}>4 Kolom</option>
              </select>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default NewsGridBlock;
