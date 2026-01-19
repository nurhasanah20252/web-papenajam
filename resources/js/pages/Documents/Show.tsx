import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Calendar,
    Download,
    File,
    FileText,
    FileType,
    HardDrive,
    User,
} from 'lucide-react';

import PageContainer from '@/components/page-container';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import MainLayout from '@/layouts/main-layout';

// Types for document data from backend
interface Category {
    id: number;
    name: string;
}

interface Uploader {
    id: number;
    name: string;
}

interface DocumentVersion {
    id: number;
    version: string;
    file_name: string;
    file_size: number;
    changelog: string | null;
    is_current: boolean;
    created_at: string;
}

interface Document {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    file_name: string | null;
    file_type: string | null;
    mime_type: string | null;
    file_size: number | null;
    download_count: number;
    is_public: boolean;
    published_at: string | null;
    version: string | null;
    checksum: string | null;
    category: Category | null;
    uploader: Uploader | null;
    versions: DocumentVersion[];
    created_at: string;
    updated_at: string;
}

interface DocumentsShowPageProps {
    document: Document;
    relatedDocuments: Document[];
}

const formatFileSize = (bytes: number | null): string => {
    if (!bytes) return 'N/A';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    if (bytes < 1024 * 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    return (bytes / (1024 * 1024 * 1024)).toFixed(1) + ' GB';
};

const formatDate = (dateString: string | null): string => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getFileIcon = (fileType: string | null) => {
    if (!fileType) return <File className="h-12 w-12 text-gray-500" />;

    const type = fileType.toLowerCase();
    if (type.includes('pdf')) {
        return <FileText className="h-12 w-12 text-red-500" />;
    }
    if (type.includes('word') || type.includes('doc')) {
        return <FileType className="h-12 w-12 text-blue-500" />;
    }
    if (type.includes('excel') || type.includes('sheet') || type.includes('xls')) {
        return <FileType className="h-12 w-12 text-green-500" />;
    }
    if (type.includes('powerpoint') || type.includes('presentation') || type.includes('ppt')) {
        return <FileType className="h-12 w-12 text-orange-500" />;
    }
    return <File className="h-12 w-12 text-gray-500" />;
};

export default function DocumentsShow({ document, relatedDocuments }: DocumentsShowPageProps) {
    return (
        <MainLayout>
            <Head title={`${document.title} - Dokumen`}>
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700"
                    rel="stylesheet"
                />
            </Head>

            {/* Header */}
            <section className="bg-gradient-to-b from-primary/5 to-background py-8 md:py-12">
                <PageContainer>
                    <Link
                        href={route('documents.index')}
                        className="mb-4 inline-flex items-center text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft className="mr-2 h-4 w-4" />
                        Kembali ke Daftar Dokumen
                    </Link>

                    <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div className="flex-1">
                            <div className="mb-3 flex flex-wrap items-center gap-2">
                                {document.category && (
                                    <Badge variant="secondary">{document.category.name}</Badge>
                                )}
                                {document.version && (
                                    <Badge variant="outline">Versi {document.version}</Badge>
                                )}
                            </div>

                            <h1 className="text-3xl font-bold tracking-tight text-foreground md:text-4xl">
                                {document.title}
                            </h1>

                            {document.description && (
                                <p className="mt-3 text-lg text-muted-foreground">
                                    {document.description}
                                </p>
                            )}
                        </div>

                        <div className="flex shrink-0 gap-2">
                            <Button asChild size="lg">
                                <Link href={route('documents.download', document.slug || document.id)}>
                                    <Download className="mr-2 h-5 w-5" />
                                    Unduh Dokumen
                                </Link>
                            </Button>
                        </div>
                    </div>
                </PageContainer>
            </section>

            {/* Document Details */}
            <section className="py-8">
                <PageContainer>
                    <div className="grid gap-6 lg:grid-cols-3">
                        {/* Main Content */}
                        <div className="lg:col-span-2 space-y-6">
                            {/* File Info */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Informasi File</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex items-start gap-4">
                                        <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-muted">
                                            {getFileIcon(document.file_type)}
                                        </div>
                                        <div className="flex-1">
                                            <h3 className="font-semibold">{document.file_name || 'Dokumen'}</h3>
                                            <div className="mt-2 flex flex-wrap gap-4 text-sm text-muted-foreground">
                                                <div className="flex items-center gap-1">
                                                    <HardDrive className="h-4 w-4" />
                                                    {formatFileSize(document.file_size)}
                                                </div>
                                                <div className="flex items-center gap-1">
                                                    <FileText className="h-4 w-4" />
                                                    {document.mime_type || 'N/A'}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Version History */}
                            {document.versions && document.versions.length > 0 && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Riwayat Versi</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="space-y-4">
                                            {document.versions.map((version) => (
                                                <div
                                                    key={version.id}
                                                    className="flex items-start justify-between rounded-lg border p-4"
                                                >
                                                    <div className="flex-1">
                                                        <div className="mb-1 flex items-center gap-2">
                                                            <span className="font-semibold">
                                                                Versi {version.version}
                                                            </span>
                                                            {version.is_current && (
                                                                <Badge variant="default" className="text-xs">
                                                                    Terbaru
                                                                </Badge>
                                                            )}
                                                        </div>
                                                        {version.changelog && (
                                                            <p className="mb-1 text-sm text-muted-foreground">
                                                                {version.changelog}
                                                            </p>
                                                        )}
                                                        <div className="flex items-center gap-3 text-xs text-muted-foreground">
                                                            <span>{formatFileSize(version.file_size)}</span>
                                                            <span>•</span>
                                                            <span>{formatDate(version.created_at)}</span>
                                                        </div>
                                                    </div>
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        asChild
                                                        className="shrink-0"
                                                    >
                                                        <Link
                                                            href={route(
                                                                'documents.versions.download',
                                                                [document.slug || document.id, version.id],
                                                            )}
                                                        >
                                                            <Download className="mr-1 h-3 w-3" />
                                                            Unduh
                                                        </Link>
                                                    </Button>
                                                </div>
                                            ))}
                                        </div>
                                    </CardContent>
                                </Card>
                            )}
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            {/* Metadata */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Metadata</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div>
                                        <div className="mb-1 flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                            <User className="h-4 w-4" />
                                            Diunggah oleh
                                        </div>
                                        <p>{document.uploader?.name || 'N/A'}</p>
                                    </div>

                                    <Separator />

                                    <div>
                                        <div className="mb-1 flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                            <Calendar className="h-4 w-4" />
                                            Tanggal Unggah
                                        </div>
                                        <p>{formatDate(document.created_at)}</p>
                                    </div>

                                    {document.published_at && (
                                        <>
                                            <Separator />
                                            <div>
                                                <div className="mb-1 flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                                    <Calendar className="h-4 w-4" />
                                                    Tanggal Terbit
                                                </div>
                                                <p>{formatDate(document.published_at)}</p>
                                            </div>
                                        </>
                                    )}

                                    <Separator />

                                    <div>
                                        <div className="mb-1 text-sm font-medium text-muted-foreground">
                                            Total Unduhan
                                        </div>
                                        <p className="text-2xl font-bold">
                                            {document.download_count.toLocaleString()}
                                        </p>
                                    </div>

                                    {document.checksum && (
                                        <>
                                            <Separator />
                                            <div>
                                                <div className="mb-1 text-sm font-medium text-muted-foreground">
                                                    SHA256 Checksum
                                                </div>
                                                <p className="break-all text-xs font-mono">
                                                    {document.checksum}
                                                </p>
                                            </div>
                                        </>
                                    )}
                                </CardContent>
                            </Card>

                            {/* Related Documents */}
                            {relatedDocuments.length > 0 && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Dokumen Terkait</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="space-y-3">
                                            {relatedDocuments.map((related) => (
                                                <Link
                                                    key={related.id}
                                                    href={route('documents.show', related.slug || related.id)}
                                                    className="block rounded-lg p-3 transition-colors hover:bg-muted"
                                                >
                                                    <p className="line-clamp-2 text-sm font-medium">
                                                        {related.title}
                                                    </p>
                                                    <p className="mt-1 text-xs text-muted-foreground">
                                                        {related.download_count} unduhan
                                                    </p>
                                                </Link>
                                            ))}
                                        </div>
                                    </CardContent>
                                </Card>
                            )}
                        </div>
                    </div>
                </PageContainer>
            </section>
        </MainLayout>
    );
}
