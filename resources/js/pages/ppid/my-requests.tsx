import { Head, Link, router } from '@inertiajs/react';
import { Clock, Search, CheckCircle, AlertCircle, Eye, Plus, FileText } from 'lucide-react';

import PageContainer from '@/components/page-container';
import PageHeader from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import MainLayout from '@/layouts/main-layout';
import { formatDate } from '@/lib/utils';

interface PpidRequest {
    id: number;
    request_number: string;
    request_type: string;
    subject: string;
    status: string;
    priority: string;
    created_at: string;
    responded_at?: string;
}

interface PaginatedData {
    data: PpidRequest[];
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
}

interface PpidMyRequestsProps {
    requests: PaginatedData;
}

const statusConfig = {
    submitted: {
        label: 'Submitted',
        color: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        icon: Clock,
    },
    reviewed: {
        label: 'Reviewed',
        color: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        icon: Search,
    },
    processed: {
        label: 'Processed',
        color: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        icon: AlertCircle,
    },
    completed: {
        label: 'Completed',
        color: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        icon: CheckCircle,
    },
    rejected: {
        label: 'Rejected',
        color: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        icon: AlertCircle,
    },
};

const requestTypeLabels: Record<string, string> = {
    informasi_publik: 'Informasi Publik',
    keberatan: 'Keberatan',
    sengketa: 'Sengketa Informasi',
    lainnya: 'Lainnya',
};

export default function PpidMyRequests({ requests }: PpidMyRequestsProps) {
    return (
        <MainLayout>
            <Head title="Permohonan Saya">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700"
                    rel="stylesheet"
                />
            </Head>

            {/* Header */}
            <section className="bg-gradient-to-b from-primary/5 to-background py-8 md:py-12">
                <PageContainer>
                    <div className="flex items-center justify-between">
                        <div>
                            <PageHeader
                                title="Permohonan Saya"
                                description="Daftar permohonan informasi publik yang Anda ajukan"
                            />
                        </div>
                        <Button href="/ppid/form" className="shrink-0">
                            <Plus className="mr-2 h-4 w-4" />
                            Buat Permohonan
                        </Button>
                    </div>
                </PageContainer>
            </section>

            {/* Content */}
            <section className="py-8">
                <PageContainer>
                    {requests.data.length === 0 ? (
                        <Card>
                            <CardContent className="py-12">
                                <div className="text-center">
                                    <FileText className="mx-auto h-16 w-16 text-muted-foreground/50" />
                                    <h3 className="mt-4 text-lg font-semibold">
                                        Belum Ada Permohonan
                                    </h3>
                                    <p className="mt-2 text-muted-foreground">
                                        Anda belum mengajukan permohonan informasi publik.
                                    </p>
                                    <Button href="/ppid/form" className="mt-4">
                                        <Plus className="mr-2 h-4 w-4" />
                                        Buat Permohonan Pertama
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    ) : (
                        <>
                            <div className="mb-4 flex items-center justify-between">
                                <p className="text-sm text-muted-foreground">
                                    Menampilkan {requests.from} - {requests.to} dari {requests.total}{' '}
                                    permohonan
                                </p>
                            </div>

                            <div className="space-y-4">
                                {requests.data.map((request) => {
                                    const statusData =
                                        statusConfig[request.status as keyof typeof statusConfig];
                                    const StatusIcon = statusData.icon;

                                    return (
                                        <Card
                                            key={request.id}
                                            className="transition-all hover:shadow-md"
                                        >
                                            <CardContent className="p-6">
                                                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                                    <div className="flex-1 space-y-2">
                                                        <div className="flex flex-wrap items-center gap-2">
                                                            <h3 className="font-semibold text-lg">
                                                                {request.subject}
                                                            </h3>
                                                            <Badge
                                                                variant="secondary"
                                                                className="text-xs"
                                                            >
                                                                {
                                                                    requestTypeLabels[
                                                                        request.request_type
                                                                    ] || request.request_type
                                                                }
                                                            </Badge>
                                                            {request.priority === 'high' && (
                                                                <Badge className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 text-xs">
                                                                    Prioritas Tinggi
                                                                </Badge>
                                                            )}
                                                        </div>
                                                        <p className="text-sm text-muted-foreground">
                                                            No: {request.request_number}
                                                        </p>
                                                        <p className="text-sm text-muted-foreground">
                                                            Diajukan: {formatDate(request.created_at)}
                                                        </p>
                                                        {request.responded_at && (
                                                            <p className="text-sm text-muted-foreground">
                                                                Ditanggapi:{' '}
                                                                {formatDate(request.responded_at)}
                                                            </p>
                                                        )}
                                                    </div>

                                                    <div className="flex flex-col items-end gap-2">
                                                        <Badge
                                                            className={`${statusData.color} shrink-0`}
                                                        >
                                                            <StatusIcon className="mr-1 h-3 w-3" />
                                                            {statusData.label}
                                                        </Badge>
                                                        <Link href={`/ppid/${request.id}`}>
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                className="mt-2"
                                                            >
                                                                <Eye className="mr-2 h-4 w-4" />
                                                                Lihat Detail
                                                            </Button>
                                                        </Link>
                                                    </div>
                                                </div>
                                            </CardContent>
                                        </Card>
                                    );
                                })}
                            </div>

                            {/* Pagination */}
                            {requests.last_page > 1 && (
                                <div className="mt-6 flex items-center justify-center gap-2">
                                    <Button
                                        variant="outline"
                                        onClick={() => {
                                            const params = new URLSearchParams(window.location.search);
                                            params.set('page', (requests.current_page - 1).toString());
                                            router.get(`/ppid/my-requests?${params.toString()}`);
                                        }}
                                        disabled={requests.current_page === 1}
                                    >
                                        Previous
                                    </Button>
                                    <span className="text-sm text-muted-foreground">
                                        Halaman {requests.current_page} dari {requests.last_page}
                                    </span>
                                    <Button
                                        variant="outline"
                                        onClick={() => {
                                            const params = new URLSearchParams(window.location.search);
                                            params.set('page', (requests.current_page + 1).toString());
                                            router.get(`/ppid/my-requests?${params.toString()}`);
                                        }}
                                        disabled={requests.current_page === requests.last_page}
                                    >
                                        Next
                                    </Button>
                                </div>
                            )}
                        </>
                    )}
                </PageContainer>
            </section>
        </MainLayout>
    );
}
