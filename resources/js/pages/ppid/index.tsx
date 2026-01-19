import { Head, Link } from '@inertiajs/react';
import { FileText, Search, Clock, CheckCircle, AlertCircle, ChevronRight } from 'lucide-react';

import PageContainer from '@/components/page-container';
import PageHeader from '@/components/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import MainLayout from '@/layouts/main-layout';
import { useState } from 'react';

interface PpidIndexProps {
    auth?: {
        user?: {
            name: string;
            email: string;
        };
    };
}

const faqs = [
    {
        question: 'Apa itu PPID?',
        answer: 'PPID (Pejabat Pengelola Informasi dan Dokumentasi) adalah unit kerja yang bertanggung jawab dalam bidang penyediaan, pemberian, dan pelayanan informasi publik di lingkungan Pengadilan Agama Penajam.',
    },
    {
        question: 'Bagaimana cara mengajukan permohonan informasi?',
        answer: 'Anda dapat mengajukan permohonan informasi melalui formulir online yang tersedia. Pastikan Anda telah terdaftar dan login ke sistem. Isi data diri dan detail permohonan dengan lengkap dan jelas.',
    },
    {
        question: 'Berapa lama waktu pemrosesan permohonan?',
        answer: 'Sesuai dengan regulasi, kami akan memproses permohonan informasi dalam waktu maksimal 10 hari kerja. Untuk permohonan yang kompleks, waktu dapat diperpanjang hingga 7 hari kerja tambahan.',
    },
    {
        question: 'Apakah biaya untuk mendapatkan informasi?',
        answer: 'Pelayanan informasi publik dasar tidak dikenakan biaya. Namun, untuk penggandaan dokumen atau penyampaian informasi tertentu, mungkin dikenakan biaya sesuai dengan peraturan yang berlaku.',
    },
    {
        question: 'Bagaimana jika permohonan ditolak?',
        answer: 'Jika permohonan Anda ditolak, Anda berhak mengajukan keberatan secara tertulis kepada Atasan PPID dalam waktu 30 hari sejak menerima pemberitahuan penolakan.',
    },
    {
        question: 'Informasi apa saja yang dapat diminta?',
        answer: 'Informasi yang dapat diminta meliputi: produk hukum, prosedur kerja, laporan kinerja, data statistik, dan informasi lainnya yang dikecualikan berdasarkan ketentuan perundang-undangan.',
    },
];

export default function PpidIndex({ auth }: PpidIndexProps) {
    const [searchQuery, setSearchQuery] = useState('');

    return (
        <MainLayout>
            <Head title="Layanan PPID">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700"
                    rel="stylesheet"
                />
            </Head>

            {/* Hero Section */}
            <section className="bg-gradient-to-b from-primary/5 to-background py-12 md:py-16">
                <PageContainer>
                    <div className="text-center">
                        <h1 className="text-4xl font-bold tracking-tight text-foreground md:text-5xl">
                            Layanan Informasi Publik
                        </h1>
                        <p className="mt-4 text-lg text-muted-foreground">
                            Pejabat Pengelola Informasi dan Dokumentasi
                            <br />
                            Pengadilan Agama Penajam
                        </p>
                    </div>
                </PageContainer>
            </section>

            {/* Quick Tracking */}
            <section className="py-8">
                <PageContainer>
                    <Card className="border-primary/20 shadow-lg">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Search className="h-5 w-5 text-primary" />
                                Lacak Permohonan
                            </CardTitle>
                            <CardDescription>
                                Masukkan nomor permohonan untuk melihat status permohonan Anda
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form
                                action="/ppid/tracking"
                                method="GET"
                                className="flex gap-2"
                            >
                                <Input
                                    type="text"
                                    name="number"
                                    placeholder="Contoh: PPID/2025/01/0001"
                                    className="flex-1"
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
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

            {/* Services */}
            <section className="py-8">
                <PageContainer>
                    <PageHeader
                        title="Layanan Kami"
                        description="Berbagai layanan informasi publik yang tersedia"
                    />
                    <div className="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <Card className="transition-all hover:shadow-lg">
                            <CardHeader>
                                <FileText className="mb-2 h-10 w-10 text-primary" />
                                <CardTitle>Permohonan Informasi</CardTitle>
                                <CardDescription>
                                    Ajukan permohonan informasi publik secara online
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Link href={auth?.user ? '/ppid/form' : '/login'}>
                                    <Button className="w-full">
                                        {auth?.user ? 'Buat Permohonan' : 'Login untuk Mengajukan'}
                                        <ChevronRight className="ml-2 h-4 w-4" />
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>

                        <Card className="transition-all hover:shadow-lg">
                            <CardHeader>
                                <Search className="mb-2 h-10 w-10 text-primary" />
                                <CardTitle>Pelacakan Status</CardTitle>
                                <CardDescription>
                                    Pantau status permohonan informasi Anda secara real-time
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Link href="/ppid/tracking">
                                    <Button variant="outline" className="w-full">
                                        Lacak Permohonan
                                        <ChevronRight className="ml-2 h-4 w-4" />
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>

                        <Card className="transition-all hover:shadow-lg">
                            <CardHeader>
                                <FileText className="mb-2 h-10 w-10 text-primary" />
                                <CardTitle>Dokumen Publik</CardTitle>
                                <CardDescription>
                                    Akses dokumen dan informasi publik yang tersedia
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Link href="/documents">
                                    <Button variant="outline" className="w-full">
                                        Lihat Dokumen
                                        <ChevronRight className="ml-2 h-4 w-4" />
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>
                    </div>
                </PageContainer>
            </section>

            {/* Process Flow */}
            <section className="bg-muted/50 py-12">
                <PageContainer>
                    <PageHeader
                        title="Alur Permohonan"
                        description="Proses permohonan informasi publik dari awal hingga selesai"
                    />
                    <div className="mt-8 grid gap-6 md:grid-cols-4">
                        <div className="text-center">
                            <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                <span className="text-2xl font-bold">1</span>
                            </div>
                            <h3 className="mb-2 font-semibold">Submit</h3>
                            <p className="text-sm text-muted-foreground">
                                Isi formulir permohonan dengan data lengkap
                            </p>
                        </div>

                        <div className="text-center">
                            <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                <span className="text-2xl font-bold">2</span>
                            </div>
                            <h3 className="mb-2 font-semibold">Verifikasi</h3>
                            <p className="text-sm text-muted-foreground">
                                Tim PPID memverifikasi permohonan Anda
                            </p>
                        </div>

                        <div className="text-center">
                            <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                <span className="text-2xl font-bold">3</span>
                            </div>
                            <h3 className="mb-2 font-semibold">Proses</h3>
                            <p className="text-sm text-muted-foreground">
                                Informasi dicari dan disiapkan
                            </p>
                        </div>

                        <div className="text-center">
                            <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                <span className="text-2xl font-bold">4</span>
                            </div>
                            <h3 className="mb-2 font-semibold">Selesai</h3>
                            <p className="text-sm text-muted-foreground">
                                Informasi disampaikan kepada pemohon
                            </p>
                        </div>
                    </div>
                </PageContainer>
            </section>

            {/* Status Information */}
            <section className="py-12">
                <PageContainer>
                    <PageHeader
                        title="Status Permohonan"
                        description="Pemahaman tentang status permohonan Anda"
                    />
                    <div className="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <Card>
                            <CardHeader>
                                <Clock className="mb-2 h-6 w-6 text-yellow-500" />
                                <CardTitle className="text-lg">Submitted</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-sm text-muted-foreground">
                                    Permohonan telah diterima dan menunggu verifikasi
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <Search className="mb-2 h-6 w-6 text-blue-500" />
                                <CardTitle className="text-lg">Reviewed</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-sm text-muted-foreground">
                                    Permohonan sedang ditinjau oleh tim PPID
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <AlertCircle className="mb-2 h-6 w-6 text-orange-500" />
                                <CardTitle className="text-lg">Processed</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-sm text-muted-foreground">
                                    Informasi sedang disiapkan dan diproses
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CheckCircle className="mb-2 h-6 w-6 text-green-500" />
                                <CardTitle className="text-lg">Completed</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-sm text-muted-foreground">
                                    Permohonan telah selesai dan informasi telah disampaikan
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <AlertCircle className="mb-2 h-6 w-6 text-red-500" />
                                <CardTitle className="text-lg">Rejected</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-sm text-muted-foreground">
                                    Permohonan tidak dapat dipenuhi dengan alasan tertentu
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </PageContainer>
            </section>

            {/* FAQ Section */}
            <section className="bg-muted/50 py-12">
                <PageContainer>
                    <PageHeader
                        title="Pertanyaan Umum (FAQ)"
                        description="Jawaban untuk pertanyaan yang sering diajukan"
                    />
                    <div className="mt-8 space-y-4">
                        {faqs.map((faq, index) => (
                            <Card key={index}>
                                <CardHeader>
                                    <CardTitle className="text-lg">{faq.question}</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-muted-foreground">{faq.answer}</p>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </PageContainer>
            </section>

            {/* Contact CTA */}
            <section className="py-12">
                <PageContainer>
                    <Card className="border-primary bg-primary/5">
                        <CardHeader>
                            <CardTitle className="text-2xl">Butuh Bantuan?</CardTitle>
                            <CardDescription>
                                Tim PPID kami siap membantu Anda mendapatkan informasi yang
                                dibutuhkan
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex flex-wrap gap-4">
                                <Button size="lg">
                                    <FileText className="mr-2 h-5 w-5" />
                                    Ajukan Permohonan
                                </Button>
                                <Button size="lg" variant="outline">
                                    Hubungi Kami
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </PageContainer>
            </section>
        </MainLayout>
    );
}
