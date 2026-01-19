import { Head, Link, router } from '@inertiajs/react';
import { Download, FileText, Filter, Search, DollarSign } from 'lucide-react';
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

// Types
interface BudgetTransparency {
    id: number;
    title: string;
    description: string;
    year: number;
    amount: number;
    formatted_amount: string;
    category: string;
    document_path?: string;
    document_name?: string;
    document_url?: string;
    published_at: string;
    author?: {
        name: string;
    };
}

interface PaginatedData {
    data: BudgetTransparency[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface PageProps {
    budgets: PaginatedData;
    availableYears: number[];
    filters: {
        year?: string;
        category?: string;
        search?: string;
    };
}

export default function BudgetTransparencyIndex({
    budgets,
    availableYears,
    filters,
}: PageProps) {
    const [searchQuery, setSearchQuery] = useState(filters.search || '');
    const [yearFilter, setYearFilter] = useState(filters.year || '');
    const [categoryFilter, setCategoryFilter] = useState(filters.category || '');

    const categoryOptions = [
        { value: 'apbn', label: 'APBN' },
        { value: 'apbd', label: 'APBD' },
        { value: 'other', label: 'Lainnya' },
    ];

    const getCategoryLabel = (category: string): string => {
        const option = categoryOptions.find((opt) => opt.value === category);
        return option?.label || category;
    };

    const getCategoryColor = (category: string): string => {
        switch (category) {
            case 'apbn':
                return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
            case 'apbd':
                return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
        }
    };

    const applyFilters = () => {
        const params: Record<string, string> = {};
        if (searchQuery) params.search = searchQuery;
        if (yearFilter) params.year = yearFilter;
        if (categoryFilter) params.category = categoryFilter;

        router.get(route('budget-transparency.index'), params, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const resetFilters = () => {
        setSearchQuery('');
        setYearFilter('');
        setCategoryFilter('');
        router.get(route('budget-transparency.index'), {}, { preserveState: true });
    };

    const handleDownload = (budget: BudgetTransparency) => {
        if (budget.document_url) {
            window.open(budget.document_url, '_blank');
        }
    };

    return (
        <MainLayout>
            <Head title="Transparansi Anggaran">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700"
                    rel="stylesheet"
                />
            </Head>

            {/* Header */}
            <PageHeader
                title="Transparansi Anggaran"
                description="Informasi transparansi pengelolaan anggaran APBN dan APBD"
                icon={<DollarSign className="h-8 w-8" />}
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
                                {/* Search */}
                                <div className="relative sm:col-span-2">
                                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        placeholder="Cari judul atau deskripsi..."
                                        value={searchQuery}
                                        onChange={(e) => setSearchQuery(e.target.value)}
                                        onKeyDown={(e) => e.key === 'Enter' && applyFilters()}
                                        className="pl-9"
                                    />
                                </div>

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

                                {/* Category Filter */}
                                <Select value={categoryFilter} onValueChange={setCategoryFilter}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Semua Kategori" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Semua Kategori</SelectItem>
                                        {categoryOptions.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                {/* Action Buttons */}
                                <div className="flex gap-2 sm:col-span-2 lg:col-span-4">
                                    <Button onClick={applyFilters} className="flex-1">
                                        <Filter className="mr-2 h-4 w-4" />
                                        Terapkan Filter
                                    </Button>
                                    <Button
                                        onClick={resetFilters}
                                        variant="outline"
                                        className="flex-1"
                                    >
                                        Reset
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </PageContainer>
            </section>

            {/* Results */}
            <section className="pb-12">
                <PageContainer>
                    <div className="mb-4 flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            Menampilkan {budgets.data.length} dari {budgets.total} data
                        </p>
                    </div>

                    {budgets.data.length > 0 ? (
                        <div className="space-y-4">
                            {budgets.data.map((budget) => (
                                <Card
                                    key={budget.id}
                                    className="transition-all hover:shadow-md"
                                >
                                    <CardContent className="p-6">
                                        <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                            {/* Main Content */}
                                            <div className="flex-1 space-y-3">
                                                {/* Title & Category */}
                                                <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                                    <div>
                                                        <div className="mb-2 flex flex-wrap items-center gap-2">
                                                            <Badge
                                                                variant="outline"
                                                                className="font-mono font-semibold"
                                                            >
                                                                {budget.year}
                                                            </Badge>
                                                            <Badge
                                                                className={getCategoryColor(budget.category)}
                                                            >
                                                                {getCategoryLabel(budget.category)}
                                                            </Badge>
                                                        </div>
                                                        <h3 className="text-xl font-semibold">
                                                            {budget.title}
                                                        </h3>
                                                    </div>
                                                    <div className="text-left sm:text-right">
                                                        <p className="text-sm text-muted-foreground">
                                                            Total Anggaran
                                                        </p>
                                                        <p className="text-2xl font-bold text-green-600 dark:text-green-400">
                                                            {budget.formatted_amount}
                                                        </p>
                                                    </div>
                                                </div>

                                                {/* Description */}
                                                <p className="text-muted-foreground">
                                                    {budget.description}
                                                </p>

                                                {/* Meta Info */}
                                                <div className="flex flex-wrap gap-4 text-sm text-muted-foreground">
                                                    {budget.author && (
                                                        <span>Oleh: {budget.author.name}</span>
                                                    )}
                                                    <span>
                                                        Dipublikasikan:{' '}
                                                        {new Date(
                                                            budget.published_at
                                                        ).toLocaleDateString('id-ID', {
                                                            year: 'numeric',
                                                            month: 'long',
                                                            day: 'numeric',
                                                        })}
                                                    </span>
                                                </div>
                                            </div>

                                            {/* Action Button */}
                                            {budget.document_url && (
                                                <Button
                                                    onClick={() => handleDownload(budget)}
                                                    className="shrink-0"
                                                    variant="default"
                                                >
                                                    <Download className="mr-2 h-4 w-4" />
                                                    Unduh Dokumen
                                                </Button>
                                            )}
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    ) : (
                        <Card>
                            <CardContent className="py-12">
                                <div className="text-center">
                                    <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-muted">
                                        <FileText className="h-8 w-8 text-muted-foreground" />
                                    </div>
                                    <h3 className="text-lg font-semibold">
                                        Tidak ada data anggaran ditemukan
                                    </h3>
                                    <p className="mt-2 text-muted-foreground">
                                        Coba ubah kata kunci pencarian atau filter
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Pagination */}
                    {budgets.last_page > 1 && (
                        <div className="mt-8 flex items-center justify-center gap-2">
                            <Button
                                variant="outline"
                                onClick={() => {
                                    const params = new URLSearchParams(
                                        window.location.search
                                    );
                                    params.set('page', (budgets.current_page - 1).toString());
                                    router.get(
                                        `${window.location.pathname}?${params.toString()}`
                                    );
                                }}
                                disabled={budgets.current_page === 1}
                            >
                                Previous
                            </Button>
                            <span className="text-sm text-muted-foreground">
                                Halaman {budgets.current_page} dari {budgets.last_page}
                            </span>
                            <Button
                                variant="outline"
                                onClick={() => {
                                    const params = new URLSearchParams(
                                        window.location.search
                                    );
                                    params.set('page', (budgets.current_page + 1).toString());
                                    router.get(
                                        `${window.location.pathname}?${params.toString()}`
                                    );
                                }}
                                disabled={budgets.current_page === budgets.last_page}
                            >
                                Next
                            </Button>
                        </div>
                    )}
                </PageContainer>
            </section>
        </MainLayout>
    );
}
