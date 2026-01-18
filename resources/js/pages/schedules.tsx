import { Head } from '@inertiajs/react';
import {
    Calendar,
    ChevronLeft,
    ChevronRight,
    Clock,
    Gavel,
    MapPin,
    Search,
    User,
} from 'lucide-react';
import { useState } from 'react';

import PageContainer from '@/components/page-container';
import PageHeader from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import MainLayout from '@/layouts/main-layout';

// Types for schedule data
interface ScheduleItem {
    id: number;
    case_number: string;
    case_type: string;
    case_title: string;
    judge: string;
    courtroom: string;
    time_start: string;
    time_end: string;
    date: string;
    status: 'scheduled' | 'in_progress' | 'completed' | 'postponed';
    parties: string[];
}

interface Judge {
    id: number;
    name: string;
    title: string;
}

interface Courtroom {
    id: number;
    name: string;
    building: string;
}

const judges: Judge[] = [
    { id: 1, name: 'Dr. H. Abdul Rahman, S.H., M.H.', title: 'Hakim Ketua' },
    { id: 2, name: 'Hj. Fatimah, S.H., M.Hum.', title: 'Hakim Anggota' },
    { id: 3, name: 'Muhammad Yusuf, S.H., M.H.', title: 'Hakim Anggota' },
    { id: 4, name: 'Siti Aminah, S.H., M.H.', title: 'Hakim Ketua' },
];

const courtrooms: Courtroom[] = [
    { id: 1, name: 'Ruang Sidang I', building: 'Gedung Utama' },
    { id: 2, name: 'Ruang Sidang II', building: 'Gedung Utama' },
    { id: 3, name: 'Ruang Sidang III', building: 'Gedung A' },
    { id: 4, name: 'Ruang Mediasi', building: 'Gedung Utama' },
];

const caseTypes = [
    'Semua Jenis',
    'Perceraian',
    'Harta Gono-Gini',
    'Penguasaan Anak',
    'Pembatalan Nikah',
    'Itsbat Nikah',
    'Warisan',
    'Wakaf',
];

// Mock schedule data
const mockSchedules: ScheduleItem[] = [
    {
        id: 1,
        case_number: '1234/Pdt.G/2024/PA.Pnj',
        case_type: 'Perceraian',
        case_title: 'Permohonan Cerai Talak',
        judge: 'Dr. H. Abdul Rahman, S.H., M.H.',
        courtroom: 'Ruang Sidang I',
        time_start: '08:00',
        time_end: '09:30',
        date: '2024-01-18',
        status: 'scheduled',
        parties: ['Ahmad Saputra', 'Siti Nurhaliza'],
    },
    {
        id: 2,
        case_number: '5678/Pdt.G/2024/PA.Pnj',
        case_type: 'Harta Gono-Gini',
        case_title: 'Gugatan Harta Bersama',
        judge: 'Hj. Fatimah, S.H., M.Hum.',
        courtroom: 'Ruang Sidang II',
        time_start: '09:30',
        time_end: '11:00',
        date: '2024-01-18',
        status: 'scheduled',
        parties: ['Budi Santoso', 'Dewi Lestari'],
    },
    {
        id: 3,
        case_number: '9012/Pdt.G/2024/PA.Pnj',
        case_type: 'Penguasaan Anak',
        case_title: 'Perkara Hadlanah',
        judge: 'Muhammad Yusuf, S.H., M.H.',
        courtroom: 'Ruang Sidang I',
        time_start: '11:00',
        time_end: '12:30',
        date: '2024-01-18',
        status: 'in_progress',
        parties: ['Rudi Hermawan', 'Mega Pertiwi'],
    },
    {
        id: 4,
        case_number: '3456/Pdt.G/2024/PA.Pnj',
        case_type: 'Perceraian',
        case_title: 'Permohonan Cerai Gugat',
        judge: 'Siti Aminah, S.H., M.H.',
        courtroom: 'Ruang Sidang III',
        time_start: '13:00',
        time_end: '14:30',
        date: '2024-01-18',
        status: 'scheduled',
        parties: ['Joko Prasetyo', 'Tina Marlina'],
    },
    {
        id: 5,
        case_number: '7890/Pdt.G/2024/PA.Pnj',
        case_type: 'Itsbat Nikah',
        case_title: 'Itsbat Nikah Siri',
        judge: 'Dr. H. Abdul Rahman, S.H., M.H.',
        courtroom: 'Ruang Mediasi',
        time_start: '14:30',
        time_end: '16:00',
        date: '2024-01-18',
        status: 'scheduled',
        parties: ['Hendra Gunawan', 'Wati Susilawati'],
    },
];

const getStatusBadgeVariant = (status: ScheduleItem['status']) => {
    switch (status) {
        case 'scheduled':
            return 'default';
        case 'in_progress':
            return 'secondary';
        case 'completed':
            return 'outline';
        case 'postponed':
            return 'destructive';
        default:
            return 'default';
    }
};

const getStatusText = (status: ScheduleItem['status']) => {
    switch (status) {
        case 'scheduled':
            return 'Dijadwalkan';
        case 'in_progress':
            return 'Sedang Berlangsung';
        case 'completed':
            return 'Selesai';
        case 'postponed':
            return 'Ditunda';
        default:
            return status;
    }
};

export default function Schedules() {
    const [viewMode, setViewMode] = useState<'day' | 'week' | 'month'>('day');
    const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split('T')[0]);
    const [searchQuery, setSearchQuery] = useState('');
    const [caseTypeFilter, setCaseTypeFilter] = useState('Semua Jenis');
    const [judgeFilter, setJudgeFilter] = useState('all');
    const [courtroomFilter, setCourtroomFilter] = useState('all');

    const filteredSchedules = mockSchedules.filter((schedule) => {
        const matchesSearch =
            searchQuery === '' ||
            schedule.case_number.toLowerCase().includes(searchQuery.toLowerCase()) ||
            schedule.case_title.toLowerCase().includes(searchQuery.toLowerCase());
        const matchesCaseType =
            caseTypeFilter === 'Semua Jenis' || schedule.case_type === caseTypeFilter;
        const matchesJudge =
            judgeFilter === 'all' || schedule.judge.includes(judgeFilter);
        const matchesCourtroom =
            courtroomFilter === 'all' || schedule.courtroom === courtroomFilter;
        const matchesDate = schedule.date === selectedDate;

        return matchesSearch && matchesCaseType && matchesJudge && matchesCourtroom && matchesDate;
    });

    const handleDateChange = (days: number) => {
        const currentDate = new Date(selectedDate);
        currentDate.setDate(currentDate.getDate() + days);
        setSelectedDate(currentDate.toISOString().split('T')[0]);
    };

    const formatDate = (dateStr: string) => {
        const date = new Date(dateStr);
        return date.toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    return (
        <MainLayout>
            <Head title="Jadwal Sidang">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700"
                    rel="stylesheet"
                />
            </Head>

            {/* Hero */}
            <section className="bg-gradient-to-b from-primary/5 to-background py-12 md:py-16">
                <PageContainer>
                    <div className="text-center">
                        <h1 className="text-4xl font-bold tracking-tight text-foreground md:text-5xl">
                            Jadwal Sidang
                        </h1>
                        <p className="mt-4 text-lg text-muted-foreground">
                            Informasi jadwal sidang Pengadilan Agama Penajam
                        </p>
                    </div>
                </PageContainer>
            </section>

            {/* Filters & Date Picker */}
            <section className="py-6">
                <PageContainer>
                    <Card>
                        <CardContent className="pt-6">
                            <div className="flex flex-col gap-4">
                                {/* Date Picker */}
                                <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div className="flex items-center gap-2">
                                        <Button
                                            variant="outline"
                                            size="icon"
                                            onClick={() => handleDateChange(-1)}
                                        >
                                            <ChevronLeft className="h-4 w-4" />
                                        </Button>
                                        <div className="flex items-center gap-2">
                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                            <Input
                                                type="date"
                                                value={selectedDate}
                                                onChange={(e) => setSelectedDate(e.target.value)}
                                                className="w-40"
                                            />
                                        </div>
                                        <Button
                                            variant="outline"
                                            size="icon"
                                            onClick={() => handleDateChange(1)}
                                        >
                                            <ChevronRight className="h-4 w-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => setSelectedDate(new Date().toISOString().split('T')[0])}
                                        >
                                            Hari Ini
                                        </Button>
                                    </div>

                                    {/* View Mode Toggle */}
                                    <div className="flex gap-1 rounded-md bg-muted p-1">
                                        {(['day', 'week', 'month'] as const).map((mode) => (
                                            <Button
                                                key={mode}
                                                variant={viewMode === mode ? 'default' : 'ghost'}
                                                size="sm"
                                                onClick={() => setViewMode(mode)}
                                                className="capitalize"
                                            >
                                                {mode === 'day'
                                                    ? 'Hari'
                                                    : mode === 'week'
                                                      ? 'Minggu'
                                                      : 'Bulan'}
                                            </Button>
                                        ))}
                                    </div>
                                </div>

                                {/* Filter Row */}
                                <div className="flex flex-col gap-4 sm:flex-row sm:items-center">
                                    {/* Search */}
                                    <div className="relative flex-1">
                                        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                        <Input
                                            placeholder="Cari nomor perkara..."
                                            value={searchQuery}
                                            onChange={(e) => setSearchQuery(e.target.value)}
                                            className="pl-9"
                                        />
                                    </div>

                                    {/* Case Type Filter */}
                                    <Select
                                        value={caseTypeFilter}
                                        onValueChange={setCaseTypeFilter}
                                    >
                                        <SelectTrigger className="w-full sm:w-48">
                                            <SelectValue placeholder="Jenis Perkara" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {caseTypes.map((type) => (
                                                <SelectItem key={type} value={type}>
                                                    {type}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>

                                    {/* Judge Filter */}
                                    <Select value={judgeFilter} onValueChange={setJudgeFilter}>
                                        <SelectTrigger className="w-full sm:w-56">
                                            <SelectValue placeholder="Semua Hakim" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">Semua Hakim</SelectItem>
                                            {judges.map((judge) => (
                                                <SelectItem key={judge.id} value={judge.name}>
                                                    {judge.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>

                                    {/* Courtroom Filter */}
                                    <Select
                                        value={courtroomFilter}
                                        onValueChange={setCourtroomFilter}
                                    >
                                        <SelectTrigger className="w-full sm:w-48">
                                            <SelectValue placeholder="Semua Ruang Sidang" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">Semua Ruang Sidang</SelectItem>
                                            {courtrooms.map((room) => (
                                                <SelectItem key={room.id} value={room.name}>
                                                    {room.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </PageContainer>
            </section>

            {/* Schedule List */}
            <section className="pb-12">
                <PageContainer>
                    <div className="mb-4">
                        <h2 className="text-lg font-semibold">
                            {formatDate(selectedDate)} ({filteredSchedules.length} sidang)
                        </h2>
                    </div>

                    {filteredSchedules.length > 0 ? (
                        <div className="space-y-4">
                            {filteredSchedules.map((schedule) => (
                                <Card
                                    key={schedule.id}
                                    className="transition-shadow hover:shadow-md"
                                >
                                    <CardContent className="p-0">
                                        <div className="flex flex-col md:flex-row">
                                            {/* Time Column */}
                                            <div className="flex items-center justify-center border-b bg-muted/30 px-4 py-4 md:w-32 md:flex-col md:border-b-0 md:border-r">
                                                <div className="flex items-center gap-1 text-sm font-medium">
                                                    <Clock className="h-4 w-4" />
                                                    {schedule.time_start}
                                                </div>
                                                <span className="text-xs text-muted-foreground md:block">
                                                    s/d {schedule.time_end}
                                                </span>
                                            </div>

                                            {/* Content */}
                                            <div className="flex-1 p-4">
                                                <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                                    <div className="flex-1">
                                                        <div className="mb-2 flex flex-wrap items-center gap-2">
                                                            <Badge variant={getStatusBadgeVariant(schedule.status)}>
                                                                {getStatusText(schedule.status)}
                                                            </Badge>
                                                            <Badge variant="outline">
                                                                {schedule.case_type}
                                                            </Badge>
                                                            <span className="text-sm font-mono text-muted-foreground">
                                                                {schedule.case_number}
                                                            </span>
                                                        </div>

                                                        <h3 className="mb-2 text-lg font-semibold">
                                                            {schedule.case_title}
                                                        </h3>

                                                        <div className="flex flex-wrap gap-4 text-sm text-muted-foreground">
                                                            <div className="flex items-center gap-1">
                                                                <User className="h-4 w-4" />
                                                                <span className="max-w-64 truncate">
                                                                    {schedule.judge}
                                                                </span>
                                                            </div>
                                                            <div className="flex items-center gap-1">
                                                                <MapPin className="h-4 w-4" />
                                                                <span>{schedule.courtroom}</span>
                                                            </div>
                                                            <div className="flex items-center gap-1">
                                                                <Gavel className="h-4 w-4" />
                                                                <span className="max-w-64 truncate">
                                                                    {schedule.parties.join(' vs ')}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div className="flex items-center gap-2">
                                                        <Button variant="outline" size="sm">
                                                            Detail
                                                        </Button>
                                                        <Button size="sm">Cetak</Button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    ) : (
                        <div className="py-12 text-center">
                            <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-muted">
                                <Calendar className="h-6 w-6 text-muted-foreground" />
                            </div>
                            <h3 className="text-lg font-semibold">Tidak ada sidang pada tanggal ini</h3>
                            <p className="mt-1 text-muted-foreground">
                                Coba pilih tanggal lain atau ubah filter pencarian
                            </p>
                        </div>
                    )}
                </PageContainer>
            </section>
        </MainLayout>
    );
}
