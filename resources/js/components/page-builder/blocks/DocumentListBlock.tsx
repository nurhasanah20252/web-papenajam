import React from 'react';
import { BlockComponentProps } from '../types';
import { FileText, Download, ExternalLink, Search } from 'lucide-react';

interface DocumentItem {
  id: string;
  title: string;
  category: string;
  fileSize: string;
  updatedAt: string;
}

const mockDocuments: DocumentItem[] = [
  {
    id: '1',
    title: 'Daftar Radius Biaya Perkara PA Penajam 2025',
    category: 'Biaya Perkara',
    fileSize: '1.2 MB',
    updatedAt: '05 Jan 2025',
  },
  {
    id: '2',
    title: 'Standar Operasional Prosedur (SOP) Kepaniteraan',
    category: 'SOP',
    fileSize: '4.5 MB',
    updatedAt: '12 Dec 2024',
  },
  {
    id: '3',
    title: 'Laporan Kinerja Instansi Pemerintah (LKjIP) 2024',
    category: 'Laporan',
    fileSize: '2.8 MB',
    updatedAt: '10 Jan 2025',
  },
  {
    id: '4',
    title: 'Rencana Strategis (Renstra) 2020-2024',
    category: 'Laporan',
    fileSize: '3.1 MB',
    updatedAt: '15 Nov 2024',
  },
];

const DocumentListBlock: React.FC<BlockComponentProps> = ({ block, onUpdate, isSelected }) => {
  const limit = block.settings?.limit || 5;
  const showSearch = block.settings?.showSearch ?? true;
  const category = block.settings?.category || 'All';

  const filteredDocs = category === 'All'
    ? mockDocuments
    : mockDocuments.filter(d => d.category === category);

  return (
    <div className="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
      {showSearch && (
        <div className="p-4 border-b border-gray-100 bg-gray-50/50">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
            <input
              type="text"
              placeholder="Cari dokumen..."
              className="w-full pl-10 pr-4 py-2 text-sm border-gray-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white"
            />
          </div>
        </div>
      )}

      <div className="divide-y divide-gray-100">
        {filteredDocs.slice(0, limit).map((doc) => (
          <div key={doc.id} className="p-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
            <div className="flex items-start gap-3">
              <div className="mt-1 p-2 bg-blue-50 rounded-lg text-blue-600">
                <FileText className="h-5 w-5" />
              </div>
              <div>
                <h4 className="text-sm font-bold text-gray-900 leading-snug mb-1">
                  {doc.title}
                </h4>
                <div className="flex items-center gap-3 text-xs text-gray-500">
                  <span className="font-medium text-blue-600/70">{doc.category}</span>
                  <span>•</span>
                  <span>{doc.fileSize}</span>
                  <span>•</span>
                  <span>Diperbarui: {doc.updatedAt}</span>
                </div>
              </div>
            </div>
            <div className="flex items-center gap-2">
              <button className="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Download">
                <Download className="h-4 w-4" />
              </button>
              <button className="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="View Online">
                <ExternalLink className="h-4 w-4" />
              </button>
            </div>
          </div>
        ))}
      </div>

      <div className="p-3 bg-gray-50 border-t border-gray-100 flex justify-between items-center px-6">
        <span className="text-xs text-gray-500">Menampilkan {Math.min(filteredDocs.length, limit)} dari {filteredDocs.length} dokumen</span>
        <button className="text-sm text-blue-600 font-medium hover:text-blue-800">
          Lihat Semua Dokumen
        </button>
      </div>

      {isSelected && (
        <div className="p-4 border-t border-dashed border-gray-300 bg-blue-50/20">
          <p className="text-xs font-bold text-blue-800 mb-4 uppercase tracking-wider">Document List Configuration</p>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label className="block text-xs font-medium text-gray-700 mb-1">Display Limit</label>
              <input
                type="number"
                min="1"
                className="w-full text-sm border-gray-200 rounded px-2 py-1"
                value={limit}
                onChange={(e) => onUpdate?.(block.content, { ...block.settings, limit: parseInt(e.target.value) || 1 })}
              />
            </div>
            <div>
              <label className="block text-xs font-medium text-gray-700 mb-1">Filter Category</label>
              <select
                className="w-full text-sm border-gray-200 rounded px-2 py-1"
                value={category}
                onChange={(e) => onUpdate?.(block.content, { ...block.settings, category: e.target.value })}
              >
                <option value="All">Semua Kategori</option>
                <option value="Biaya Perkara">Biaya Perkara</option>
                <option value="SOP">SOP</option>
                <option value="Laporan">Laporan</option>
                <option value="SK">SK</option>
              </select>
            </div>
            <div className="flex items-end pb-1">
              <label className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  className="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                  checked={showSearch}
                  onChange={(e) => onUpdate?.(block.content, { ...block.settings, showSearch: e.target.checked })}
                />
                <span className="text-xs font-medium text-gray-700">Tampilkan Pencarian</span>
              </label>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default DocumentListBlock;
