import { Head } from '@inertiajs/react';
import {
    Download,
    File,
    FileText,
    FileType,
    FolderOpen,
    Search,
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

// Types for document data
interface DocumentItem {
    id: number;
    title: string;
    category: string;
    file_type: string;
    file_size: number;
    download_count: number;
    uploaded_at: string;
    uploaded_by: string;
    description?: string;
    version?: string;
}

interface Category {
    id: string;
    name: string;
    icon: string;
    count: number;
}

const categories: Category[] = [
    { id: 'all', name: 'Semua Kategori', icon: 'folder', count: 24 },
    { id: 'regulation', name: 'Peraturan', icon: 'file-text', count: 8 },
    { id: 'announcement', name: 'Pengumuman', icon: 'megaphone', count: 5 },
    { id: 'form', name: 'Formulir', icon: 'file-type', count: 6 },
    { id: 'guideline', name: 'Panduan', icon: 'book', count: 3 },
    { id: 'report', name: 'Laporan', icon: 'file', count: 2 },
];

const mockDocuments: DocumentItem[] = [
    {
        id: 1,
        title: 'Peraturan Pengadilan Agama Penajam Nomor 1 Tahun 2024',
        category: 'regulation',
        file_type: 'pdf',
        file_size: 2456789,
        download_count: 1250,
        uploaded_at: '2024-01-15',
        uploaded_by: 'Admin',
        description: 'Tentang Tata Cara Pendaftaran Perkara Secara Online',
        version: 'Revisi 1',
    },
    {
        id: 2,
        title: 'Formulir Pendaftaran Perkara Cerai',
        category: 'form',
        file_type: 'pdf',
        file_size: 156789,
        download_count: 3420,
        uploaded_at: '2024-01-10',
        uploaded_by: 'Admin',
        description: 'Formulir untuk pendaftaran perkara perceraian',
        version: '2024',
    },
    {
        id: 3,
        title: 'Pengumuman Jadwal Libur Nasional 2024',
        category: 'announcement',
        file_type: 'pdf',
        file_size: 98765,
        download_count: 890,
        uploaded_at: '2024-01-05',
        uploaded_by: 'Admin',
        description: 'Daftar tanggal libur nasional dan cuti bersama tahun 2024',
    },
    {
        id: 4,
        title: 'Panduan E-Court untuk Advokat',
        category: 'guideline',
        file_type: 'pdf',
        file_size: 4567890,
        download_count: 2100,
        uploaded_at: '2023-12-20',
        uploaded_by: 'Admin',
        description: 'Petunjuk penggunaan sistem E-Court bagi advokat',
        version: '2.0',
    },
    {
        id: 5,
        title: 'Formulir Gugatan Harta Gono-Gini',
        category: 'form',
        file_type: 'docx',
        file_size: 125678,
        download_count: 1890,
        uploaded_at: '2023-12-15',
        uploaded_by: 'Admin',
        description: 'Formulir gugatan pembagian harta bersama',
    },
    {
        id: 6,
        title: 'Laporan Kinerja Pengadilan Agama Penajam 2023',
        category: 'report',
        file_type: 'pdf',
        file_size: 8976543,
        download_count: 560,
        uploaded_at: '2023-12-01',
        uploaded_by: 'Admin',
        description: 'Annual performance report for fiscal year 2023',
    },
    {
        id: 7,
        title: 'Peraturan MA Nomor 2 Tahun 2023',
        category: 'regulation',
        file_type: 'pdf',
        file_size: 1234567,
        download_count: 3450,
        uploaded_at: '2023-11-15',
        uploaded_by: 'Admin',
        description: 'Tentang Pedoman Penyelesaian Perkara Secara Elektronik',
        version: '2023',
    },
    {
        id: 8,
        title: 'Formulir Permohonan Izin参观',
        category: 'form',
        file_type: 'pdf',
        file_size: 78901,
        download_count: 450,
        uploaded_at: '2023-11-01',
        uploaded_by: 'Admin',
    },
    {
        id: 9,
        title: 'Pengumuman Hasil Seleksi Hakim',
        category: 'announcement',
        file_type: 'pdf',
        file_size: 234567,
        download_count: 2100,
        uploaded_at: '2023-10-20',
        uploaded_by: 'Admin',
        description: 'Pengumuman kelulusan seleksi calon hakim',
    },
    {
        id: 10,
        title: 'Panduan Mediasi untuk Para Pihak',
        category: 'guideline',
        file_type: 'pdf',
        file_size: 3456789,
        download_count: 1670,
        uploaded_at: '2023-10-10',
        uploaded_by: 'Admin',
        description: 'Panduan penyelesaian sengketa melalui mediasi',
        version: '1.5',
    },
];

const formatFileSize = (bytes: number): string => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    if (bytes < 1024 * 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    return (bytes / (1024 * 1024 * 1024)).toFixed(1) + ' GB';
};

const getFileIcon = (fileType: string) => {
    switch (fileType.toLowerCase()) {
        case 'pdf':
            return <FileText className="h-8 w-8 text-red-500" />;
        case 'docx':
        case 'doc':
            return <FileType className="h-8 w-8 text-blue-500" />;
        case 'xlsx':
        case 'xls':
            return <FileType className="h-8 w-8 text-green-500" />;
        case 'pptx':
        case 'ppt':
            return <FileType className="h-8 w-8 text-orange-500" />;
        default:
            return <File className="h-8 w-8 text-gray-500" />;
    }
};

const getCategoryName = (categoryId: string): string => {
    const category = categories.find((c) => c.id === categoryId);
    return category?.name || categoryId;
};

const getCategoryColor = (categoryId: string): string => {
    switch (categoryId) {
        case 'regulation':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'announcement':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'form':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'guideline':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
        case 'report':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

export default function Documents() {
    const [searchQuery, setSearchQuery] = useState('');
    const [categoryFilter, setCategoryFilter] = useState('all');
    const [fileTypeFilter, setFileTypeFilter] = useState('all');
    const [sortBy, setSortBy] = useState('newest');
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 6;

    const filteredDocuments = mockDocuments
        .filter((doc) => {
            const matchesSearch =
                searchQuery === '' ||
                doc.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
                doc.description?.toLowerCase().includes(searchQuery.toLowerCase());
            const matchesCategory =
                categoryFilter === 'all' || doc.category === categoryFilter;
            const matchesFileType =
                fileTypeFilter === 'all' || doc.file_type === fileTypeFilter;
            return matchesSearch && matchesCategory && matchesFileType;
        })
        .sort((a, b) => {
            switch (sortBy) {
                case 'newest':
                    return new Date(b.uploaded_at).getTime() - new Date(a.uploaded_at).getTime();
                case 'oldest':
                    return new Date(a.uploaded_at).getTime() - new Date(b.uploaded_at).getTime();
                case 'downloads':
                    return b.download_count - a.download_count;
                case 'name':
                    return a.title.localeCompare(b.title);
                default:
                    return 0;
            }
        });

    const totalPages = Math.ceil(filteredDocuments.length / itemsPerPage);
    const paginatedDocuments = filteredDocuments.slice(
        (currentPage - 1) * itemsPerPage,
        currentPage * itemsPerPage
    );

    const handleDownload = (doc: DocumentItem) => {
        console.log(`Downloading document: ${doc.title}`);
    };

    return (
        <MainLayout>
            <Head title="Perpustakaan Dokumen">
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
                            Perpustakaan Dokumen
                        </h1>
                        <p className="mt-4 text-lg text-muted-foreground">
                            Unduh peraturan, formulir, dan dokumen lainnya
                        </p>
                    </div>
                </PageContainer>
            </section>

            {/* Filters */}
            <section className="py-6">
                <PageContainer>
                    <Card>
                        <CardContent className="pt-6">
                            <div className="flex flex-col gap-4">
                                {/* Search & Category */}
                                <div className="flex flex-col gap-4 sm:flex-row sm:items-center">
                                    <div className="relative flex-1">
                                        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                        <Input
                                            placeholder="Cari dokumen..."
                                            value={searchQuery}
                                            onChange={(e) => setSearchQuery(e.target.value)}
                                            className="pl-9"
                                        />
                                    </div>

                                    <Select
                                        value={categoryFilter}
                                        onValueChange={setCategoryFilter}
                                    >
                                        <SelectTrigger className="w-full sm:w-48">
                                            <SelectValue placeholder="Kategori" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {categories.map((category) => (
                                                <SelectItem key={category.id} value={category.id}>
                                                    <div className="flex items-center justify-between gap-2">
                                                        <span>{category.name}</span>
                                                        <Badge variant="secondary" className="text-xs">
                                                            {category.count}
                                                        </Badge>
                                                    </div>
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                {/* File Type & Sort */}
                                <div className="flex flex-col gap-4 sm:flex-row sm:items-center">
                                    <Select
                                        value={fileTypeFilter}
                                        onValueChange={setFileTypeFilter}
                                    >
                                        <SelectTrigger className="w-full sm:w-40">
                                            <SelectValue placeholder="Tipe File" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">Semua Tipe</SelectItem>
                                            <SelectItem value="pdf">PDF</SelectItem>
                                            <SelectItem value="docx">Word</SelectItem>
                                            <SelectItem value="xlsx">Excel</SelectItem>
                                        </SelectContent>
                                    </Select>

                                    <Select value={sortBy} onValueChange={setSortBy}>
                                        <SelectTrigger className="w-full sm:w-44">
                                            <SelectValue placeholder="Urutkan" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="newest">Terbaru</SelectItem>
                                            <SelectItem value="oldest">Terlama</SelectItem>
                                            <SelectItem value="downloads">Paling Banyak Diunduh</SelectItem>
                                            <SelectItem value="name">Nama A-Z</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </PageContainer>
            </section>

            {/* Document Grid */}
            <section className="pb-12">
                <PageContainer>
                    <div className="mb-4 flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            Menampilkan {paginatedDocuments.length} dari {filteredDocuments.length} dokumen
                        </p>
                    </div>

                    {paginatedDocuments.length > 0 ? (
                        <>
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                {paginatedDocuments.map((doc) => (
                                    <Card
                                        key={doc.id}
                                        className="group overflow-hidden transition-all hover:shadow-md"
                                    >
                                        <CardContent className="p-0">
                                            <div className="flex items-start gap-4 p-4">
                                                <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-muted">
                                                    {getFileIcon(doc.file_type)}
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <div className="mb-1 flex items-start justify-between gap-2">
                                                        <h3 className="font-semibold line-clamp-2 text-sm">
                                                            {doc.title}
                                                        </h3>
                                                    </div>
                                                    <div className="mb-2 flex flex-wrap items-center gap-2">
                                                        <Badge
                                                            className={`text-xs ${getCategoryColor(doc.category)}`}
                                                        >
                                                            {getCategoryName(doc.category)}
                                                        </Badge>
                                                        <Badge variant="outline" className="text-xs">
                                                            {doc.file_type.toUpperCase()}
                                                        </Badge>
                                                        <span className="text-xs text-muted-foreground">
                                                            {formatFileSize(doc.file_size)}
                                                        </span>
                                                    </div>
                                                    {doc.description && (
                                                        <p className="mb-2 text-xs text-muted-foreground line-clamp-2">
                                                            {doc.description}
                                                        </p>
                                                    )}
                                                    <div className="flex items-center justify-between pt-2">
                                                        <span className="text-xs text-muted-foreground">
                                                            Diunduh {doc.download_count.toLocaleString()}x
                                                        </span>
                                                        <Button
                                                            size="sm"
                                                            variant="ghost"
                                                            className="h-7 px-2 text-xs"
                                                            onClick={() => handleDownload(doc)}
                                                        >
                                                            <Download className="mr-1 h-3 w-3" />
                                                            Unduh
                                                        </Button>
                                                    </div>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>

                            {/* Pagination */}
                            {totalPages > 1 && (
                                <div className="mt-8 flex items-center justify-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
                                        disabled={currentPage === 1}
                                    >
                                        Previous
                                    </Button>
                                    {Array.from({ length: totalPages }, (_, i) => i + 1).map((page) => (
                                        <Button
                                            key={page}
                                            variant={currentPage === page ? 'default' : 'outline'}
                                            size="sm"
                                            onClick={() => setCurrentPage(page)}
                                        >
                                            {page}
                                        </Button>
                                    ))}
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => setCurrentPage((p) => Math.min(totalPages, p + 1))}
                                        disabled={currentPage === totalPages}
                                    >
                                        Next
                                    </Button>
                                </div>
                            )}
                        </>
                    ) : (
                        <div className="py-12 text-center">
                            <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-muted">
                                <FolderOpen className="h-6 w-6 text-muted-foreground" />
                            </div>
                            <h3 className="text-lg font-semibold">Tidak ada dokumen ditemukan</h3>
                            <p className="mt-1 text-muted-foreground">
                                Coba ubah kata kunci pencarian atau kategori
                            </p>
                        </div>
                    )}
                </PageContainer>
            </section>
        </MainLayout>
    );
}
