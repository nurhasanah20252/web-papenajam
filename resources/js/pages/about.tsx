import { Head } from '@inertiajs/react';
import { Scale, Target, Users, Award } from 'lucide-react';

import PageContainer from '@/components/page-container';
import PageHeader from '@/components/page-header';
import { Card, CardContent } from '@/components/ui/card';
import MainLayout from '@/layouts/main-layout';

export default function About() {
    const sejarah = {
        title: 'Sejarah Pengadilan Agama Penajam',
        content: `Pengadilan Agama Penajam merupakan salah satu pengadilan tingkat pertama yang berada di lingkungan Peradilan Agama yang berkedudukan di Kabupaten Penajam Paser Utara, Provinsi Kalimantan Timur. Pengadilan ini didirikan berdasarkan Undang-Undang Nomor 7 Tahun 1989 tentang Peradilan Agama yang kemudian digantikan oleh Undang-Undang Nomor 50 Tahun 2009.`,
    };

    const visiMisi = {
        visi: 'Mewujudkan Pengadilan Agama Penajam yang profesional, akuntabel, dan bermartabat dalam menegakkan keadilan bagi masyarakat.',
        misi: [
            'Menyelenggarakan pelayanan peradilan yang sederhana, cepat, dan biaya ringan.',
            'Meningkatkan kualitas sumber daya manusia aparatur pengadilan.',
            'Mewujudkan pengelolaan administrasi peradilan yang transparan dan akuntabel.',
            'Membangun mekanisme pengawasan yang efektif dan efisien.',
        ],
    };

    const struktur = [
        { name: 'Dr. Ahmad Fauzi, S.H., M.H.', position: 'Ketua Pengadilan' },
        { name: 'Budi Santoso, S.H., M.H.', position: 'Panitera' },
        { name: 'Siti Nurhasanah, S.H.', position: 'Sekretaris' },
        { name: 'Muhammad Rizki, S.H.', position: 'Hakim' },
        { name: 'Dewi Lestari, S.H., M.H.', position: 'Hakim' },
    ];

    return (
        <MainLayout>
            <Head title="Tentang Kami">
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
                            Tentang Pengadilan Agama Penajam
                        </h1>
                        <p className="mt-4 text-lg text-muted-foreground">
                            Mengenal lebih dekat institusi yang melayani masyarakat
                            dengan penuh dedikasi dan integritas
                        </p>
                    </div>
                </PageContainer>
            </section>

            {/* Values */}
            <section className="py-12">
                <PageContainer>
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <Card className="text-center">
                            <CardContent className="pt-6">
                                <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-primary/10">
                                    <Scale className="h-7 w-7 text-primary" />
                                </div>
                                <h3 className="font-semibold">Keadilan</h3>
                                <p className="mt-2 text-sm text-muted-foreground">
                                    Menegakkan keadilan yang hakiki dan
                                    berkeadilan bagi semua pihak
                                </p>
                            </CardContent>
                        </Card>
                        <Card className="text-center">
                            <CardContent className="pt-6">
                                <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-primary/10">
                                    <Target className="h-7 w-7 text-primary" />
                                </div>
                                <h3 className="font-semibold">Integritas</h3>
                                <p className="mt-2 text-sm text-muted-foreground">
                                    Menjunjung tinggi kejujuran dan
                                    keteguhan dalam bertugas
                                </p>
                            </CardContent>
                        </Card>
                        <Card className="text-center">
                            <CardContent className="pt-6">
                                <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-primary/10">
                                    <Users className="h-7 w-7 text-primary" />
                                </div>
                                <h3 className="font-semibold">Profesional</h3>
                                <p className="mt-2 text-sm text-muted-foreground">
                                    Melayani dengan kompetensi dan
                                    standar tertinggi
                                </p>
                            </CardContent>
                        </Card>
                        <Card className="text-center">
                            <CardContent className="pt-6">
                                <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-primary/10">
                                    <Award className="h-7 w-7 text-primary" />
                                </div>
                                <h3 className="font-semibold">Akuntabel</h3>
                                <p className="mt-2 text-sm text-muted-foreground">
                                    Bertanggung jawab atas setiap keputusan
                                    yang diambil
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </PageContainer>
            </section>

            {/* Sejarah */}
            <section id="sejarah" className="bg-muted/50 py-12 md:py-16">
                <PageContainer>
                    <div className="grid gap-8 lg:grid-cols-2">
                        <div>
                            <h2 className="mb-4 text-2xl font-bold">{sejarah.title}</h2>
                            <div className="prose prose-muted max-w-none">
                                <p className="text-muted-foreground">
                                    {sejarah.content}
                                </p>
                                <p className="mt-4 text-muted-foreground">
                                    Sejak berdirinya, Pengadilan Agama Penajam
                                    telah mengalami berbagai perkembangan baik
                                    dari segi organisasi,人力资源, maupun
                                    sistem pelayanan. Dengan dukungan teknologi
                                    informasi modern, pengadilan ini terus
                                    berinovasi untuk memberikan pelayanan terbaik
                                    kepada masyarakat.
                                </p>
                            </div>
                        </div>
                        <div className="relative">
                            <div className="rounded-lg bg-primary/10 p-8">
                                <div className="text-center">
                                    <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary">
                                        <Scale className="h-8 w-8 text-primary-foreground" />
                                    </div>
                                    <h3 className="text-xl font-semibold">
                                        Berdiri Sejak 1990
                                    </h3>
                                    <p className="mt-2 text-muted-foreground">
                                        Lebih dari 3 dekade melayani masyarakat
                                        Penajam Paser Utara dalam menegakkan
                                        keadilan
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </PageContainer>
            </section>

            {/* Visi Misi */}
            <section id="visi-misi" className="py-12 md:py-16">
                <PageContainer>
                    <div className="mx-auto max-w-3xl text-center">
                        <h2 className="mb-8 text-2xl font-bold">Visi & Misi</h2>
                        <div className="mb-8 rounded-lg bg-primary/5 p-8">
                            <h3 className="mb-4 text-lg font-semibold">Visi</h3>
                            <p className="text-lg text-muted-foreground">
                                {visiMisi.visi}
                            </p>
                        </div>
                        <div className="text-left">
                            <h3 className="mb-4 text-lg font-semibold">Misi</h3>
                            <ul className="space-y-3">
                                {visiMisi.misi.map((misi, index) => (
                                    <li
                                        key={index}
                                        className="flex items-start gap-3"
                                    >
                                        <div className="mt-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-xs text-primary-foreground">
                                            {index + 1}
                                        </div>
                                        <span className="text-muted-foreground">
                                            {misi}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </PageContainer>
            </section>

            {/* Struktur Organisasi */}
            <section id="struktur" className="bg-muted/50 py-12 md:py-16">
                <PageContainer>
                    <PageHeader
                        title="Struktur Organisasi"
                        description="Tim manajemen Pengadilan Agama Penajam"
                        className="mb-8"
                    />
                    <div className="mx-auto max-w-4xl">
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {struktur.map((item, index) => (
                                <Card key={index} className="text-center">
                                    <CardContent className="pt-6">
                                        <div className="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-foreground font-semibold">
                                            {item.name.charAt(0)}
                                        </div>
                                        <h3 className="font-semibold">
                                            {item.name}
                                        </h3>
                                        <p className="text-sm text-muted-foreground">
                                            {item.position}
                                        </p>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </div>
                </PageContainer>
            </section>
        </MainLayout>
    );
}
