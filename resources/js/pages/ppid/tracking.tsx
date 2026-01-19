import { Head, Link } from '@inertiajs/react';
import { Search, Clock, CheckCircle, AlertCircle, Calendar, User, FileText, ArrowLeft } from 'lucide-react';

import PageContainer from '@/components/page-container';
import PageHeader from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import MainLayout from '@/layouts/main-layout';
import { formatDate } from '@/lib/utils';

interface PpidRequest {
    id: number;
    request_number: string;
    applicant_name: string;
    email: string;
    phone?: string;
    request_type: string;
    subject: string;
    description: string;
    status: string;
    priority: string;
    response?: string;
    responded_at?: string;
    created_at: string;
    attachments?: string[];
}

interface PpidTrackingProps {
    requestNumber?: string;
    request?: PpidRequest | null;
    error?: string | null;
}

const statusConfig = {
    submitted: {
        label: 'Submitted',
        color: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        icon: Clock,
        description: 'Permohonan telah diterima dan menunggu verifikasi',
    },
    reviewed: {
        label: 'Reviewed',
        color: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        icon: Search,
        description: 'Permohonan sedang ditinjau oleh tim PPID',
    },
    processed: {
        label: 'Processed',
        color: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        icon: AlertCircle,
        description: 'Informasi sedang disiapkan dan diproses',
    },
    completed: {
        label: 'Completed',
        color: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        icon: CheckCircle,
        description: 'Permohonan telah selesai',
    },
    rejected: {
        label: 'Rejected',
        color: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        icon: AlertCircle,
        description: 'Permohonan tidak dapat dipenuhi',
    },
};

const requestTypeLabels: Record<string, string> = {
    informasi_publik: 'Informasi Publik',
    keberatan: 'Keberatan',
    sengketa: 'Sengketa Informasi',
    lainnya: 'Lainnya',
};

export default function PpidTracking({ requestNumber, request, error }: PpidTrackingProps) {
    const statusData = request ? statusConfig[request.status as keyof typeof statusConfig] : null;
    const StatusIcon = statusData?.icon || Clock;

    return (
        <MainLayout>
            <Head title="Lacak Permohonan PPID">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700"
                    rel="stylesheet"
                />
            </Head>

            {/* Header */}
            <section className="bg-gradient-to-b from-primary/5 to-background py-8 md:py-12">
                <PageContainer>
                    <Button variant="ghost" className="mb-4" href="/ppid">
                        <ArrowLeft className="mr-2 h-4 w-4" />
                        Kembali
                    </Button>
                    <PageHeader
                        title="Lacak Permohonan"
                        description="Masukkan nomor permohonan untuk melihat status"
                    />
                </PageContainer>
            </section>

            {/* Search Form */}
            <section className="py-8">
                <PageContainer>
                    <Card className="border-primary/20 shadow-lg">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Search className="h-5 w-5 text-primary" />
                                Cari Permohonan
                            </CardTitle>
                            <CardDescription>
                                Masukkan nomor permohonan (Contoh: PPID/2025/01/0001)
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form action="/ppid/tracking" method="GET" className="flex gap-2">
                                <Input
                                    type="text"
                                    name="number"
                                    placeholder="Contoh: PPID/2025/01/0001"
                                    className="flex-1"
                                    defaultValue={requestNumber}
                                />
                                <Button type="submit" className="shrink-0">
                                    <Search className="mr-2 h-4 w-4" />
                                    Lacak
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </PageContainer>
            </section>

            {/* Result */}
            {error && (
                <section className="py-8">
                    <PageContainer>
                        <Card className="border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-950">
                            <CardHeader>
                                <AlertCircle className="mb-2 h-8 w-8 text-red-500" />
                                <CardTitle className="text-red-900 dark:text-red-100">
                                    Permohonan Tidak Ditemukan
                                </CardTitle>
                                <CardDescription className="text-red-700 dark:text-red-300">
                                    {error}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <p className="text-sm text-muted-foreground">
                                    Pastikan nomor permohonan yang Anda masukkan benar. Hubungi kami
                                    jika Anda membutuhkan bantuan.
                                </p>
                            </CardContent>
                        </Card>
                    </PageContainer>
                </section>
            )}

            {request && statusData && (
                <section className="py-8">
                    <PageContainer>
                        <div className="space-y-6">
                            {/* Status Card */}
                            <Card className={`border-2 ${statusData.color}`}>
                                <CardHeader>
                                    <div className="flex items-center gap-3">
                                        <StatusIcon className="h-8 w-8" />
                                        <div>
                                            <CardTitle className="text-2xl">
                                                {statusData.label}
                                            </CardTitle>
                                            <CardDescription>{statusData.description}</CardDescription>
                                        </div>
                                    </div>
                                </CardHeader>
                            </Card>

                            {/* Request Details */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Detail Permohonan</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <p className="text-sm text-muted-foreground">
                                                Nomor Permohonan
                                            </p>
                                            <p className="font-semibold text-lg">{request.request_number}</p>
                                        </div>
                                        <div>
                                            <p className="text-sm text-muted-foreground">Jenis</p>
                                            <Badge variant="secondary">
                                                {requestTypeLabels[request.request_type] || request.request_type}
                                            </Badge>
                                        </div>
                                    </div>

                                    <Separator />

                                    <div>
                                        <p className="text-sm text-muted-foreground">Subjek</p>
                                        <p className="font-semibold">{request.subject}</p>
                                    </div>

                                    <div>
                                        <p className="text-sm text-muted-foreground">Deskripsi</p>
                                        <p className="mt-1 whitespace-pre-wrap">{request.description}</p>
                                    </div>

                                    <Separator />

                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="flex items-start gap-2">
                                            <User className="mt-1 h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p className="text-sm text-muted-foreground">
                                                    Nama Pemohon
                                                </p>
                                                <p className="font-semibold">{request.applicant_name}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-start gap-2">
                                            <Calendar className="mt-1 h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p className="text-sm text-muted-foreground">
                                                    Tanggal Pengajuan
                                                </p>
                                                <p className="font-semibold">
                                                    {formatDate(request.created_at)}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    {request.attachments && request.attachments.length > 0 && (
                                        <>
                                            <Separator />
                                            <div>
                                                <p className="text-sm text-muted-foreground mb-2">
                                                    Lampiran
                                                </p>
                                                <div className="space-y-1">
                                                    {request.attachments.map((attachment, index) => (
                                                        <div
                                                            key={index}
                                                            className="flex items-center gap-2 text-sm"
                                                        >
                                                            <FileText className="h-4 w-4 text-muted-foreground" />
                                                            <span>{attachment}</span>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        </>
                                    )}
                                </CardContent>
                            </Card>

                            {/* Response Card */}
                            {(request.status === 'completed' || request.status === 'rejected') && request.response && (
                                <Card className={request.status === 'completed' ? 'border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-950' : 'border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-950'}>
                                    <CardHeader>
                                        <CardTitle className="flex items-center gap-2">
                                            <CheckCircle className="h-5 w-5" />
                                            Respon PPID
                                        </CardTitle>
                                        {request.responded_at && (
                                            <CardDescription>
                                                Ditanggapi pada {formatDate(request.responded_at)}
                                            </CardDescription>
                                        )}
                                    </CardHeader>
                                    <CardContent>
                                        <p className="whitespace-pre-wrap">{request.response}</p>
                                    </CardContent>
                                </Card>
                            )}

                            {/* Actions */}
                            <Card>
                                <CardContent className="pt-6">
                                    <div className="flex flex-wrap gap-4">
                                        <Button href="/ppid/form" variant="outline">
                                            <FileText className="mr-2 h-4 w-4" />
                                            Buat Permohonan Baru
                                        </Button>
                                        <Button
                                            href={request.status === 'completed' ? '/ppid' : '/ppid/tracking'}
                                            variant="outline"
                                        >
                                            <Search className="mr-2 h-4 w-4" />
                                            Lacak Lainnya
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </PageContainer>
                </section>
            )}
        </MainLayout>
    );
}
