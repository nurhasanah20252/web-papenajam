import { Head, Link } from '@inertiajs/react';
import { Clock, CheckCircle, AlertCircle, Calendar, User, FileText, ArrowLeft, Download } from 'lucide-react';

import PageContainer from '@/components/page-container';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import MainLayout from '@/layouts/main-layout';
import { formatDate } from '@/lib/utils';

interface PpidRequest {
    id: number;
    request_number: string;
    applicant_name: string;
    nik?: string;
    address?: string;
    phone?: string;
    email: string;
    request_type: string;
    subject: string;
    description: string;
    status: string;
    priority: string;
    response?: string;
    responded_at?: string;
    created_at: string;
    attachments?: string[];
    notes?: Record<string, string>;
}

interface PpidShowProps {
    request: PpidRequest;
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
        icon: AlertCircle,
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

export default function PpidShow({ request }: PpidShowProps) {
    const statusData = statusConfig[request.status as keyof typeof statusConfig];
    const StatusIcon = statusData.icon;

    return (
        <MainLayout>
            <Head title={`Permohonan ${request.request_number}`}>
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700"
                    rel="stylesheet"
                />
            </Head>

            {/* Header */}
            <section className="bg-gradient-to-b from-primary/5 to-background py-8 md:py-12">
                <PageContainer>
                    <Button variant="ghost" className="mb-4" href="/ppid/my-requests">
                        <ArrowLeft className="mr-2 h-4 w-4" />
                        Kembali ke Daftar
                    </Button>
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight text-foreground md:text-4xl">
                                {request.request_number}
                            </h1>
                            <p className="mt-2 text-muted-foreground">
                                Detail permohonan informasi publik Anda
                            </p>
                        </div>
                        <Badge className={`${statusData.color} text-sm`}>
                            <StatusIcon className="mr-1 h-4 w-4" />
                            {statusData.label}
                        </Badge>
                    </div>
                </PageContainer>
            </section>

            {/* Content */}
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

                        {/* Applicant Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Informasi Pemohon</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="flex items-start gap-2">
                                        <User className="mt-1 h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <p className="text-sm text-muted-foreground">Nama</p>
                                            <p className="font-semibold">{request.applicant_name}</p>
                                        </div>
                                    </div>
                                    {request.nik && (
                                        <div>
                                            <p className="text-sm text-muted-foreground">NIK</p>
                                            <p className="font-semibold">{request.nik}</p>
                                        </div>
                                    )}
                                </div>

                                <Separator />

                                <div className="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <p className="text-sm text-muted-foreground">Email</p>
                                        <p className="font-semibold">{request.email}</p>
                                    </div>
                                    {request.phone && (
                                        <div>
                                            <p className="text-sm text-muted-foreground">
                                                Nomor Telepon
                                            </p>
                                            <p className="font-semibold">{request.phone}</p>
                                        </div>
                                    )}
                                </div>

                                {request.address && (
                                    <>
                                        <Separator />
                                        <div>
                                            <p className="text-sm text-muted-foreground">Alamat</p>
                                            <p className="whitespace-pre-wrap">{request.address}</p>
                                        </div>
                                    </>
                                )}
                            </CardContent>
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
                                        <p className="font-semibold text-lg">
                                            {request.request_number}
                                        </p>
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
                                    <p className="font-semibold text-lg">{request.subject}</p>
                                </div>

                                <div>
                                    <p className="text-sm text-muted-foreground">Deskripsi</p>
                                    <p className="mt-2 whitespace-pre-wrap rounded-lg bg-muted p-4">
                                        {request.description}
                                    </p>
                                </div>

                                <Separator />

                                <div className="grid gap-4 md:grid-cols-2">
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
                                    <div>
                                        <p className="text-sm text-muted-foreground">Prioritas</p>
                                        <Badge
                                            className={
                                                request.priority === 'high'
                                                    ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                                    : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
                                            }
                                        >
                                            {request.priority === 'high' ? 'Tinggi' : 'Normal'}
                                        </Badge>
                                    </div>
                                </div>

                                {request.attachments && request.attachments.length > 0 && (
                                    <>
                                        <Separator />
                                        <div>
                                            <p className="text-sm text-muted-foreground mb-3">
                                                Lampiran ({request.attachments.length})
                                            </p>
                                            <div className="space-y-2">
                                                {request.attachments.map((attachment, index) => (
                                                    <div
                                                        key={index}
                                                        className="flex items-center justify-between rounded-lg border p-3"
                                                    >
                                                        <div className="flex items-center gap-2">
                                                            <FileText className="h-5 w-5 text-muted-foreground" />
                                                            <span className="text-sm">{attachment}</span>
                                                        </div>
                                                        <Button size="sm" variant="outline">
                                                            <Download className="mr-2 h-4 w-4" />
                                                            Unduh
                                                        </Button>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    </>
                                )}
                            </CardContent>
                        </Card>

                        {/* Response */}
                        {(request.status === 'completed' || request.status === 'rejected') &&
                            request.response && (
                                <Card
                                    className={
                                        request.status === 'completed'
                                            ? 'border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-950'
                                            : 'border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-950'
                                    }
                                >
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
                                        <div className="rounded-lg bg-white p-4 dark:bg-gray-900">
                                            <p className="whitespace-pre-wrap">{request.response}</p>
                                        </div>
                                    </CardContent>
                                </Card>
                            )}

                        {/* Timeline */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Timeline</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    <div className="flex gap-4">
                                        <div className="flex flex-col items-center">
                                            <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                                <span className="text-sm font-bold">1</span>
                                            </div>
                                            <div className="w-0.5 flex-1 bg-border" />
                                        </div>
                                        <div className="flex-1 pb-4">
                                            <p className="font-semibold">Permohonan Diterima</p>
                                            <p className="text-sm text-muted-foreground">
                                                {formatDate(request.created_at)}
                                            </p>
                                        </div>
                                    </div>

                                    {request.status !== 'submitted' && (
                                        <div className="flex gap-4">
                                            <div className="flex flex-col items-center">
                                                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                                    <span className="text-sm font-bold">2</span>
                                                </div>
                                                {request.status !== 'reviewed' &&
                                                    request.status !== 'processed' &&
                                                    request.status !== 'completed' &&
                                                    request.status !== 'rejected' && (
                                                        <div className="w-0.5 flex-1 bg-border" />
                                                    )}
                                            </div>
                                            <div className="flex-1 pb-4">
                                                <p className="font-semibold">Ditinjau</p>
                                                <p className="text-sm text-muted-foreground">
                                                    Permohonan sedang ditinjau oleh tim PPID
                                                </p>
                                            </div>
                                        </div>
                                    )}

                                    {request.status === 'processed' && (
                                        <div className="flex gap-4">
                                            <div className="flex flex-col items-center">
                                                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                                    <span className="text-sm font-bold">3</span>
                                                </div>
                                            </div>
                                            <div className="flex-1">
                                                <p className="font-semibold">Sedang Diproses</p>
                                                <p className="text-sm text-muted-foreground">
                                                    Informasi sedang disiapkan
                                                </p>
                                            </div>
                                        </div>
                                    )}

                                    {(request.status === 'completed' ||
                                        request.status === 'rejected') && (
                                        <div className="flex gap-4">
                                            <div className="flex flex-col items-center">
                                                <div
                                                    className={`flex h-8 w-8 items-center justify-center rounded-full ${
                                                        request.status === 'completed'
                                                            ? 'bg-green-500'
                                                            : 'bg-red-500'
                                                    } text-white`}
                                                >
                                                    <CheckCircle className="h-4 w-4" />
                                                </div>
                                            </div>
                                            <div className="flex-1">
                                                <p className="font-semibold">
                                                    {request.status === 'completed'
                                                        ? 'Selesai'
                                                        : 'Ditolak'}
                                                </p>
                                                {request.responded_at && (
                                                    <p className="text-sm text-muted-foreground">
                                                        {formatDate(request.responded_at)}
                                                    </p>
                                                )}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Actions */}
                        <Card>
                            <CardContent className="pt-6">
                                <div className="flex flex-wrap gap-4">
                                    <Button href="/ppid/form" variant="outline">
                                        <FileText className="mr-2 h-4 w-4" />
                                        Buat Permohonan Baru
                                    </Button>
                                    <Button
                                        href={`/ppid/tracking?number=${request.request_number}`}
                                        variant="outline"
                                    >
                                        Bagikan Status
                                    </Button>
                                    <Button href="/ppid/my-requests">Kembali ke Daftar</Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </PageContainer>
            </section>
        </MainLayout>
    );
}
