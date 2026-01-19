import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Calendar,
    Clock,
    FileText,
    Gavel,
    MapPin,
    Printer,
    User,
} from 'lucide-react';

import PageContainer from '@/components/page-container';
import PageHeader from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import MainLayout from '@/layouts/main-layout';

interface ScheduleDetailProps {
    schedule: {
        id: number;
        case_number: string;
        case_type: string;
        case_title: string;
        judge: string;
        courtroom: string;
        time_start: string;
        time_end: string;
        date: string;
        formatted_date: string;
        status: 'scheduled' | 'in_progress' | 'completed' | 'postponed';
        agenda: string;
        notes: string;
        parties: string[];
        last_sync_at: string;
    };
}

const getStatusBadgeVariant = (status: ScheduleDetailProps['schedule']['status']) => {
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

const getStatusText = (status: ScheduleDetailProps['schedule']['status']) => {
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

export default function ScheduleDetail({ schedule }: ScheduleDetailProps) {
    const handlePrint = () => {
        window.print();
    };

    return (
        <MainLayout>
            <Head title={`Jadwal Sidang - ${schedule.case_number}`}>
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700"
                    rel="stylesheet"
                />
            </Head>

            {/* Header */}
            <section className="bg-gradient-to-b from-primary/5 to-background py-8 md:py-12">
                <PageContainer>
                    <div className="mb-4">
                        <Link href="/jadwal-sidang">
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Kembali ke Jadwal
                            </Button>
                        </Link>
                    </div>
                    <div>
                        <div className="mb-4 flex flex-wrap items-center gap-2">
                            <Badge variant={getStatusBadgeVariant(schedule.status)}>
                                {getStatusText(schedule.status)}
                            </Badge>
                            <Badge variant="outline">{schedule.case_type}</Badge>
                        </div>
                        <h1 className="text-3xl font-bold tracking-tight text-foreground md:text-4xl">
                            {schedule.case_title}
                        </h1>
                        <p className="mt-2 font-mono text-lg text-muted-foreground">
                            {schedule.case_number}
                        </p>
                    </div>
                </PageContainer>
            </section>

            {/* Content */}
            <section className="py-8">
                <PageContainer>
                    <div className="grid gap-6 lg:grid-cols-3">
                        {/* Main Content */}
                        <div className="space-y-6 lg:col-span-2">
                            {/* Schedule Details */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Detail Jadwal Sidang</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="flex items-start gap-3">
                                            <Calendar className="mt-1 h-5 w-5 text-muted-foreground" />
                                            <div>
                                                <p className="text-sm text-muted-foreground">Tanggal</p>
                                                <p className="font-medium">{schedule.formatted_date}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-start gap-3">
                                            <Clock className="mt-1 h-5 w-5 text-muted-foreground" />
                                            <div>
                                                <p className="text-sm text-muted-foreground">Waktu</p>
                                                <p className="font-medium">
                                                    {schedule.time_start} - {schedule.time_end}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex items-start gap-3">
                                            <MapPin className="mt-1 h-5 w-5 text-muted-foreground" />
                                            <div>
                                                <p className="text-sm text-muted-foreground">Ruang Sidang</p>
                                                <p className="font-medium">{schedule.courtroom}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-start gap-3">
                                            <User className="mt-1 h-5 w-5 text-muted-foreground" />
                                            <div>
                                                <p className="text-sm text-muted-foreground">Hakim</p>
                                                <p className="font-medium">{schedule.judge}</p>
                                            </div>
                                        </div>
                                    </div>

                                    {schedule.agenda && (
                                        <div className="flex items-start gap-3">
                                            <Gavel className="mt-1 h-5 w-5 text-muted-foreground" />
                                            <div className="flex-1">
                                                <p className="mb-1 text-sm text-muted-foreground">Agenda</p>
                                                <p className="font-medium">{schedule.agenda}</p>
                                            </div>
                                        </div>
                                    )}

                                    {schedule.notes && (
                                        <div className="flex items-start gap-3">
                                            <FileText className="mt-1 h-5 w-4 text-muted-foreground" />
                                            <div className="flex-1">
                                                <p className="mb-1 text-sm text-muted-foreground">Catatan</p>
                                                <p className="whitespace-pre-wrap font-medium">
                                                    {schedule.notes}
                                                </p>
                                            </div>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            {/* Parties */}
                            {schedule.parties && schedule.parties.length > 0 && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Pihak-Pihak</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="space-y-3">
                                            {schedule.parties.map((party, index) => (
                                                <div
                                                    key={index}
                                                    className="flex items-center gap-3 rounded-lg border p-3"
                                                >
                                                    <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                                                        <User className="h-4 w-4" />
                                                    </div>
                                                    <div>
                                                        <p className="font-medium">{party}</p>
                                                        <p className="text-sm text-muted-foreground">
                                                            {index === 0 ? 'Pihak Pertama' : 'Pihak Kedua'}
                                                        </p>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </CardContent>
                                </Card>
                            )}
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            {/* Actions */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Aksi</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-2">
                                    <Button className="w-full" variant="default" onClick={handlePrint}>
                                        <Printer className="mr-2 h-4 w-4" />
                                        Cetak Jadwal
                                    </Button>
                                </CardContent>
                            </Card>

                            {/* Case Information */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Informasi Perkara</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <div>
                                        <p className="text-sm text-muted-foreground">Nomor Perkara</p>
                                        <p className="font-mono font-medium">{schedule.case_number}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm text-muted-foreground">Jenis Perkara</p>
                                        <p className="font-medium">{schedule.case_type}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm text-muted-foreground">Judul Perkara</p>
                                        <p className="font-medium">{schedule.case_title}</p>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Sync Info */}
                            {schedule.last_sync_at && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Informasi Sinkronisasi</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div>
                                            <p className="text-sm text-muted-foreground">Terakhir Diperbarui</p>
                                            <p className="font-medium">{schedule.last_sync_at}</p>
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
