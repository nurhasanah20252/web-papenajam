import { Head, router } from '@inertiajs/react';
import {
    BarChart,
    Bar,
    LineChart,
    Line,
    PieChart,
    Pie,
    Cell,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    Legend,
    ResponsiveContainer,
} from 'recharts';
import { Download, Filter, FileSpreadsheet, TrendingUp } from 'lucide-react';
import { useState } from 'react';

import PageContainer from '@/components/page-container';
import PageHeader from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import MainLayout from '@/layouts/main-layout';

// Types
interface CaseStatistics {
    id: number;
    year: number;
    month: number;
    month_name: string;
    court_type: string;
    court_type_label: string;
    total_filed: number;
    total_resolved: number;
    pending_carryover: number;
    avg_resolution_days?: number;
    settlement_rate?: number;
}

interface OverviewData {
    monthlyTrends: Array<{
        month: number;
        total_filed: number;
        total_resolved: number;
    }>;
    courtDistribution: Array<{
        court_type: string;
        total: number;
        resolved: number;
    }>;
    yearlyTrends: Array<{
        year: number;
        total_filed: number;
        total_resolved: number;
    }>;
}

interface PaginatedData {
    data: CaseStatistics[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface PageProps {
    statistics: PaginatedData;
    availableYears: number[];
    overview: OverviewData;
    filters: {
        year?: string;
        month?: string;
        court_type?: string;
    };
}

const COLORS = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];

const monthNames = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'Mei',
    'Jun',
    'Jul',
    'Agu',
    'Sep',
    'Okt',
    'Nov',
    'Des',
];

export default function CaseStatisticsIndex({
    statistics,
    availableYears,
    overview,
    filters,
}: PageProps) {
    const [yearFilter, setYearFilter] = useState(filters.year || '');
    const [monthFilter, setMonthFilter] = useState(filters.month || '');
    const [courtTypeFilter, setCourtTypeFilter] = useState(filters.court_type || '');

    const courtTypeOptions = [
        { value: 'perdata', label: 'Perdata' },
        { value: 'pidana', label: 'Pidana' },
        { value: 'agama', label: 'Agama' },
    ];

    const monthOptions = [
        { value: '1', label: 'Januari' },
        { value: '2', label: 'Februari' },
        { value: '3', label: 'Maret' },
        { value: '4', label: 'April' },
        { value: '5', label: 'Mei' },
        { value: '6', label: 'Juni' },
        { value: '7', label: 'Juli' },
        { value: '8', label: 'Agustus' },
        { value: '9', label: 'September' },
        { value: '10', label: 'Oktober' },
        { value: '11', label: 'November' },
        { value: '12', label: 'Desember' },
    ];

    const getCourtTypeColor = (courtType: string): string => {
        switch (courtType) {
            case 'perdata':
                return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
            case 'pidana':
                return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
            case 'agama':
                return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
        }
    };

    const applyFilters = () => {
        const params: Record<string, string> = {};
        if (yearFilter) params.year = yearFilter;
        if (monthFilter) params.month = monthFilter;
        if (courtTypeFilter) params.court_type = courtTypeFilter;

        router.get(route('case-statistics.index'), params, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const resetFilters = () => {
        setYearFilter('');
        setMonthFilter('');
        setCourtTypeFilter('');
        router.get(route('case-statistics.index'), {}, { preserveState: true });
    };

    const handleExport = () => {
        const params = new URLSearchParams();
        if (yearFilter) params.set('year', yearFilter);
        if (monthFilter) params.set('month', monthFilter);
        if (courtTypeFilter) params.set('court_type', courtTypeFilter);

        window.open(
            `${route('case-statistics.export')}?${params.toString()}`,
            '_blank'
        );
    };

    // Prepare chart data
    const monthlyData = overview.monthlyTrends.map((item) => ({
        name: monthNames[item.month - 1],
        'Perkara Masuk': item.total_filed,
        'Perkara Selesai': item.total_resolved,
    }));

    const courtData = overview.courtDistribution.map((item, index) => ({
        name: courtTypeOptions.find((opt) => opt.value === item.court_type)?.label || item.court_type,
        value: item.total,
        fill: COLORS[index % COLORS.length],
    }));

    const yearlyData = overview.yearlyTrends.map((item) => ({
        tahun: item.year.toString(),
        'Perkara Masuk': item.total_filed,
        'Perkara Selesai': item.total_resolved,
    }));

    return (
        <MainLayout>
            <Head title="Statistik Perkara">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700"
                    rel="stylesheet"
                />
            </Head>

            {/* Header */}
            <PageHeader
                title="Statistik Perkara"
                description="Data statistik perkara pengadilan berdasarkan jenis dan periode"
                icon={<TrendingUp className="h-8 w-8" />}
            />

            {/* Filters */}
            <section className="py-6">
                <PageContainer>
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <Filter className="h-5 w-5" />
                                Filter Data
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                {/* Year Filter */}
                                <Select value={yearFilter} onValueChange={setYearFilter}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Semua Tahun" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Semua Tahun</SelectItem>
                                        {availableYears.map((year) => (
                                            <SelectItem key={year} value={year.toString()}>
                                                {year}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                {/* Month Filter */}
                                <Select value={monthFilter} onValueChange={setMonthFilter}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Semua Bulan" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Semua Bulan</SelectItem>
                                        {monthOptions.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                {/* Court Type Filter */}
                                <Select value={courtTypeFilter} onValueChange={setCourtTypeFilter}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Semua Jenis" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Semua Jenis</SelectItem>
                                        {courtTypeOptions.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                {/* Action Buttons */}
                                <div className="flex gap-2">
                                    <Button onClick={applyFilters} className="flex-1">
                                        <Filter className="mr-2 h-4 w-4" />
                                        Filter
                                    </Button>
                                    <Button onClick={resetFilters} variant="outline" className="flex-1">
                                        Reset
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </PageContainer>
            </section>

            {/* Charts */}
            {overview.monthlyTrends.length > 0 && (
                <section className="py-6">
                    <PageContainer>
                        <div className="grid gap-6 lg:grid-cols-2">
                            {/* Monthly Trends */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Tren Bulanan</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <ResponsiveContainer width="100%" height={300}>
                                        <BarChart data={monthlyData}>
                                            <CartesianGrid strokeDasharray="3 3" />
                                            <XAxis dataKey="name" />
                                            <YAxis />
                                            <Tooltip />
                                            <Legend />
                                            <Bar dataKey="Perkara Masuk" fill="#3b82f6" />
                                            <Bar dataKey="Perkara Selesai" fill="#10b981" />
                                        </BarChart>
                                    </ResponsiveContainer>
                                </CardContent>
                            </Card>

                            {/* Court Distribution */}
                            {courtData.length > 0 && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Distribusi Jenis Perkara</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <ResponsiveContainer width="100%" height={300}>
                                            <PieChart>
                                                <Pie
                                                    data={courtData}
                                                    cx="50%"
                                                    cy="50%"
                                                    labelLine={false}
                                                    label={({ name, percent }) =>
                                                        `${name} ${(percent * 100).toFixed(0)}%`
                                                    }
                                                    outerRadius={80}
                                                    fill="#8884d8"
                                                    dataKey="value"
                                                >
                                                    {courtData.map((entry, index) => (
                                                        <Cell
                                                            key={`cell-${index}`}
                                                            fill={entry.fill}
                                                        />
                                                    ))}
                                                </Pie>
                                                <Tooltip />
                                            </PieChart>
                                        </ResponsiveContainer>
                                    </CardContent>
                                </Card>
                            )}

                            {/* Yearly Trends */}
                            {yearlyData.length > 0 && (
                                <Card className="lg:col-span-2">
                                    <CardHeader>
                                        <CardTitle>Tren Tahunan (5 Tahun Terakhir)</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <ResponsiveContainer width="100%" height={300}>
                                            <LineChart data={yearlyData}>
                                                <CartesianGrid strokeDasharray="3 3" />
                                                <XAxis dataKey="tahun" />
                                                <YAxis />
                                                <Tooltip />
                                                <Legend />
                                                <Line
                                                    type="monotone"
                                                    dataKey="Perkara Masuk"
                                                    stroke="#3b82f6"
                                                    strokeWidth={2}
                                                />
                                                <Line
                                                    type="monotone"
                                                    dataKey="Perkara Selesai"
                                                    stroke="#10b981"
                                                    strokeWidth={2}
                                                />
                                            </LineChart>
                                        </ResponsiveContainer>
                                    </CardContent>
                                </Card>
                            )}
                        </div>
                    </PageContainer>
                </section>
            )}

            {/* Data Table */}
            <section className="pb-12">
                <PageContainer>
                    <div className="mb-4 flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            Menampilkan {statistics.data.length} dari {statistics.total} data
                        </p>
                        <Button onClick={handleExport} variant="outline">
                            <FileSpreadsheet className="mr-2 h-4 w-4" />
                            Export Excel
                        </Button>
                    </div>

                    {statistics.data.length > 0 ? (
                        <div className="space-y-4">
                            <Card>
                                <CardContent className="p-0">
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-sm">
                                            <thead className="border-b bg-muted/50">
                                                <tr>
                                                    <th className="p-4 text-left font-semibold">Periode</th>
                                                    <th className="p-4 text-left font-semibold">Jenis Perkara</th>
                                                    <th className="p-4 text-center font-semibold">Diajukan</th>
                                                    <th className="p-4 text-center font-semibold">Selesai</th>
                                                    <th className="p-4 text-center font-semibold">Sisa</th>
                                                    <th className="p-4 text-center font-semibold">Rata-rata</th>
                                                    <th className="p-4 text-center font-semibold">Penyelesaian</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {statistics.data.map((stat) => (
                                                    <tr key={stat.id} className="border-b hover:bg-muted/50">
                                                        <td className="p-4">
                                                            <div className="font-semibold">{stat.year}</div>
                                                            <div className="text-xs text-muted-foreground">
                                                                {stat.month_name}
                                                            </div>
                                                        </td>
                                                        <td className="p-4">
                                                            <Badge className={getCourtTypeColor(stat.court_type)}>
                                                                {stat.court_type_label}
                                                            </Badge>
                                                        </td>
                                                        <td className="p-4 text-center">
                                                            <span className="font-semibold text-blue-600">
                                                                {stat.total_filed}
                                                            </span>
                                                        </td>
                                                        <td className="p-4 text-center">
                                                            <span className="font-semibold text-green-600">
                                                                {stat.total_resolved}
                                                            </span>
                                                        </td>
                                                        <td className="p-4 text-center">
                                                            <span className="font-semibold text-orange-600">
                                                                {stat.pending_carryover}
                                                            </span>
                                                        </td>
                                                        <td className="p-4 text-center">
                                                            {stat.avg_resolution_days
                                                                ? `${stat.avg_resolution_days} hari`
                                                                : '-'}
                                                        </td>
                                                        <td className="p-4 text-center">
                                                            {stat.settlement_rate !== null && stat.settlement_rate !== undefined ? (
                                                                <Badge
                                                                    variant={
                                                                        stat.settlement_rate >= 80
                                                                            ? 'default'
                                                                            : 'secondary'
                                                                    }
                                                                >
                                                                    {stat.settlement_rate.toFixed(1)}%
                                                                </Badge>
                                                            ) : (
                                                                '-'
                                                            )}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Pagination */}
                            {statistics.last_page > 1 && (
                                <div className="flex items-center justify-center gap-2">
                                    <Button
                                        variant="outline"
                                        onClick={() => {
                                            const params = new URLSearchParams(window.location.search);
                                            params.set('page', (statistics.current_page - 1).toString());
                                            router.get(`${window.location.pathname}?${params.toString()}`);
                                        }}
                                        disabled={statistics.current_page === 1}
                                    >
                                        Previous
                                    </Button>
                                    <span className="text-sm text-muted-foreground">
                                        Halaman {statistics.current_page} dari {statistics.last_page}
                                    </span>
                                    <Button
                                        variant="outline"
                                        onClick={() => {
                                            const params = new URLSearchParams(window.location.search);
                                            params.set('page', (statistics.current_page + 1).toString());
                                            router.get(`${window.location.pathname}?${params.toString()}`);
                                        }}
                                        disabled={statistics.current_page === statistics.last_page}
                                    >
                                        Next
                                    </Button>
                                </div>
                            )}
                        </div>
                    ) : (
                        <Card>
                            <CardContent className="py-12">
                                <div className="text-center">
                                    <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-muted">
                                        <TrendingUp className="h-8 w-8 text-muted-foreground" />
                                    </div>
                                    <h3 className="text-lg font-semibold">
                                        Tidak ada data statistik ditemukan
                                    </h3>
                                    <p className="mt-2 text-muted-foreground">
                                        Coba ubah filter atau pilih periode lain
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    )}
                </PageContainer>
            </section>
        </MainLayout>
    );
}
