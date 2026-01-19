import React from 'react';
import { BlockComponentProps } from '../types';
import { Calendar, Clock, MapPin, User } from 'lucide-react';

interface SippScheduleItem {
  id: string;
  caseNumber: string;
  parties: string;
  time: string;
  room: string;
  judge: string;
}

const mockSchedules: SippScheduleItem[] = [
  {
    id: '1',
    caseNumber: '123/Pdt.G/2025/PA.Pnj',
    parties: 'Ahmad vs Siti',
    time: '09:00 AM',
    room: 'Ruang Sidang Utama',
    judge: 'H. Moh. Jufri, S.Ag., M.H.',
  },
  {
    id: '2',
    caseNumber: '456/Pdt.P/2025/PA.Pnj',
    parties: 'Permohonan Isbat Nikah - Budi',
    time: '10:30 AM',
    room: 'Ruang Sidang II',
    judge: 'Dra. Hj. Siti Aminah, M.H.',
  },
  {
    id: '3',
    caseNumber: '789/Pdt.G/2025/PA.Pnj',
    parties: 'Cerai Gugat - Ani vs Doni',
    time: '01:00 PM',
    room: 'Ruang Sidang Utama',
    judge: 'H. Moh. Jufri, S.Ag., M.H.',
  },
];

const SippScheduleBlock: React.FC<BlockComponentProps> = ({ block, onUpdate, isSelected }) => {
  const limit = block.settings?.limit || 5;
  const title = block.content?.title || 'Jadwal Sidang Hari Ini';

  return (
    <div className="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
      <div className="bg-blue-600 p-4 text-white flex items-center gap-2">
        <Calendar className="h-5 w-5" />
        <h3 className="font-bold">{title}</h3>
      </div>
      <div className="divide-y divide-gray-100">
        {mockSchedules.slice(0, limit).map((item) => (
          <div key={item.id} className="p-4 hover:bg-gray-50 transition-colors">
            <div className="flex justify-between items-start mb-2">
              <span className="text-sm font-semibold text-blue-700">{item.caseNumber}</span>
              <div className="flex items-center text-xs text-gray-500 gap-1">
                <Clock className="h-3.5 w-3.5" />
                {item.time}
              </div>
            </div>
            <p className="text-sm font-medium text-gray-900 mb-2">{item.parties}</p>
            <div className="grid grid-cols-2 gap-2 text-xs text-gray-500">
              <div className="flex items-center gap-1">
                <MapPin className="h-3.5 w-3.5" />
                {item.room}
              </div>
              <div className="flex items-center gap-1">
                <User className="h-3.5 w-3.5" />
                {item.judge}
              </div>
            </div>
          </div>
        ))}
      </div>
      <div className="p-3 bg-gray-50 border-t border-gray-100 text-center">
        <button className="text-sm text-blue-600 font-medium hover:text-blue-800">
          Lihat Semua Jadwal
        </button>
      </div>

      {isSelected && (
        <div className="p-4 border-t border-dashed border-gray-300 bg-blue-50/30">
          <p className="text-xs font-medium text-blue-800 mb-2 uppercase">Block Settings</p>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs text-gray-600 mb-1">Title</label>
              <input
                type="text"
                className="w-full text-sm border rounded px-2 py-1"
                value={title}
                onChange={(e) => onUpdate?.({ ...block.content, title: e.target.value })}
              />
            </div>
            <div>
              <label className="block text-xs text-gray-600 mb-1">Display Limit</label>
              <input
                type="number"
                className="w-full text-sm border rounded px-2 py-1"
                value={limit}
                onChange={(e) => onUpdate?.(block.content, { limit: parseInt(e.target.value) || 1 })}
              />
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default SippScheduleBlock;
