import { Head } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import {
    ArrowRight,
    Calendar,
    FileText,
    Gavel,
    Search,
    Users,
} from 'lucide-react';

import PageContainer from '@/components/page-container';
import PageHeader from '@/components/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import MainLayout from '@/layouts/main-layout';
import { StaggerContainer, StaggerItem } from '@/hooks/use-stagger-children';
import { useScrollReveal } from '@/hooks/use-scroll-reveal';
import { motion } from 'framer-motion';

export default function Welcome() {
    const heroReveal = useScrollReveal({ triggerOnce: true });
    const featuresReveal = useScrollReveal({ triggerOnce: true });
    const newsReveal = useScrollReveal({ triggerOnce: true });
    const statsReveal = useScrollReveal({ triggerOnce: true });

    const features = [
        {
            icon: FileText,
            title: 'Pendaftaran Perkara Online',
            description:
                'Layanan pendaftaran perkara secara online untuk memudahkan masyarakat.',
            href: '/services/registration',
        },
        {
            icon: Search,
            title: 'Cek Perkara',
            description:
                'Lacak perkembangan perkara Anda secara real-time melalui sistem online.',
            href: '/case-status',
        },
        {
            icon: Calendar,
            title: 'Jadwal Sidang',
            description:
                'Informasi jadwal sidang yang dapat diakses oleh seluruh masyarakat.',
            href: '/court-schedule',
        },
        {
            icon: Gavel,
            title: 'Putusan Pengadilan',
            description:
                'Akses putusan pengadilan secara lengkap dan terverifikasi.',
            href: '/services/decision',
        },
    ];

    const latestNews = [
        {
            id: 1,
            title: 'Pengadilan Agama Penajam Menggelar Sosialisasi E-Court',
            date: '15 Januari 2024',
            category: 'Berita',
            excerpt:
                'Dalam rangka meningkatkan pelayanan kepada masyarakat, Pengadilan Agama Penajam mengadakan sosialisasi mengenai layanan E-Court...',
        },
        {
            id: 2,
            title: 'Pengumuman Seleksi Calon Hakim',
            date: '10 Januari 2024',
            category: 'Pengumuman',
            excerpt:
                'Pengadilan Agama Penajam membuka pendaftaran seleksi calon hakim untuk mengisi posisi yang kosong...',
        },
        {
            id: 3,
            title: 'Rapat Koordinasi Pembangunan Zona Integritas',
            date: '5 Januari 2024',
            category: 'Berita',
            excerpt:
                'Dalam rangka mewujudkan wilayah bebas dari korupsi, Pengadilan Agama Penajam mengadakan rapat koordinasi pembangunan zona integritas...',
        },
    ];

    return (
        <MainLayout>
            <Head title="Beranda">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700"
                    rel="stylesheet"
                />
            </Head>

            {/* Hero Section */}
            <section ref={heroReveal.ref} className="relative bg-gradient-to-b from-primary/5 to-background py-16 md:py-24">
                <PageContainer size="full">
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.6 }}
                        className="grid gap-12 lg:grid-cols-2 lg:gap-8"
                    >
                        <div className="flex flex-col justify-center">
                            <motion.h1
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.6, delay: 0.2 }}
                                className="text-4xl font-bold tracking-tight text-foreground sm:text-5xl lg:text-6xl"
                            >
                                Melayani Dengan
                                <span className="text-primary"> Integritas</span>
                            </motion.h1>
                            <motion.p
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.6, delay: 0.4 }}
                                className="mt-6 text-lg text-muted-foreground"
                            >
                                Pengadilan Agama Penajam berkomitmen memberikan
                                pelayanan yang profesional, transparan, dan
                                akuntabel kepada seluruh masyarakat Penajam
                                Paser Utara dan sekitarnya.
                            </motion.p>
                            <motion.div
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.6, delay: 0.6 }}
                                className="mt-8 flex flex-col gap-4 sm:flex-row"
                            >
                                <Button size="lg" asChild className="hover-lift">
                                    <Link href="/services/registration">
                                        Daftar Perkara
                                        <ArrowRight className="ml-2 h-4 w-4" />
                                    </Link>
                                </Button>
                                <Button size="lg" variant="outline" asChild className="hover-lift">
                                    <Link href="/case-status">
                                        Cek Perkara Anda
                                    </Link>
                                </Button>
                            </motion.div>
                        </div>
                        <div className="relative hidden lg:block">
                            <div className="absolute inset-0 bg-gradient-to-r from-primary/10 to-transparent rounded-lg" />
                            <motion.div
                                initial={{ opacity: 0, scale: 0.9 }}
                                animate={{ opacity: 1, scale: 1 }}
                                transition={{ duration: 0.6, delay: 0.8 }}
                                className="relative flex h-full items-center justify-center"
                            >
                                <div className="text-center">
                                    <motion.div
                                        animate={{ y: [0, -10, 0] }}
                                        transition={{ duration: 3, repeat: Infinity, ease: 'easeInOut' }}
                                        className="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-primary/10"
                                    >
                                        <Users className="h-10 w-10 text-primary" />
                                    </motion.div>
                                    <h3 className="text-xl font-semibold">
                                        Layani Dengan Hati
                                    </h3>
                                    <p className="mt-2 text-muted-foreground">
                                        Membangun kepercayaan masyarakat melalui
                                        pelayanan yang bermartabat
                                    </p>
                                </div>
                            </motion.div>
                        </div>
                    </motion.div>
                </PageContainer>
            </section>

            {/* Quick Services */}
            <section ref={featuresReveal.ref} className="py-12 md:py-16">
                <PageContainer>
                    <PageHeader
                        title="Layanan Cepat"
                        description="Akses berbagai layanan pengadilan dengan mudah dan cepat"
                        className="mb-8"
                    />
                    <StaggerContainer className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        {features.map((feature, index) => (
                            <StaggerItem key={feature.href} delay={index * 0.1}>
                                <Card className="h-full transition-all hover:shadow-lg hover:-translate-y-1">
                                    <CardHeader>
                                        <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                                            <feature.icon className="h-6 w-6 text-primary" />
                                        </div>
                                        <CardTitle className="text-lg">
                                            {feature.title}
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <p className="text-sm text-muted-foreground">
                                            {feature.description}
                                        </p>
                                        <Button
                                            variant="link"
                                            className="mt-4 h-auto p-0 text-primary group"
                                            asChild
                                        >
                                            <Link href={feature.href}>
                                                Selengkapnya
                                                <ArrowRight className="ml-1 h-4 w-4 transition-transform group-hover:translate-x-1" />
                                            </Link>
                                        </Button>
                                    </CardContent>
                                </Card>
                            </StaggerItem>
                        ))}
                    </StaggerContainer>
                </PageContainer>
            </section>

            {/* Latest News */}
            <section ref={newsReveal.ref} className="bg-muted/50 py-12 md:py-16">
                <PageContainer>
                    <PageHeader
                        title="Berita & Pengumuman"
                        description="Informasi terbaru dari Pengadilan Agama Penajam"
                        className="mb-8"
                    >
                        <Button variant="outline" asChild className="hover-lift">
                            <Link href="/news">Lihat Semua</Link>
                        </Button>
                    </PageHeader>
                    <StaggerContainer className="grid gap-6 md:grid-cols-3">
                        {latestNews.map((news, index) => (
                            <StaggerItem key={news.id} delay={index * 0.1}>
                                <Card className="h-full overflow-hidden transition-all hover:shadow-lg hover:-translate-y-1">
                                    <div className="h-2 bg-primary" />
                                    <CardContent className="pt-6">
                                        <div className="mb-3 flex items-center gap-2 text-xs text-muted-foreground">
                                            <span className="rounded-full bg-primary/10 px-2 py-1 text-primary">
                                                {news.category}
                                            </span>
                                            <span className="flex items-center gap-1">
                                                <Calendar className="h-3 w-3" />
                                                {news.date}
                                            </span>
                                        </div>
                                        <h3 className="mb-2 font-semibold line-clamp-2">
                                            {news.title}
                                        </h3>
                                        <p className="text-sm text-muted-foreground line-clamp-3">
                                            {news.excerpt}
                                        </p>
                                        <Button
                                            variant="link"
                                            className="mt-4 h-auto p-0 text-primary group"
                                            asChild
                                        >
                                            <Link href={`/news/${news.id}`}>
                                                Baca Selengkapnya
                                                <ArrowRight className="ml-1 h-4 w-4 transition-transform group-hover:translate-x-1" />
                                            </Link>
                                        </Button>
                                    </CardContent>
                                </Card>
                            </StaggerItem>
                        ))}
                    </StaggerContainer>
                </PageContainer>
            </section>

            {/* Stats Section */}
            <section ref={statsReveal.ref} className="py-12 md:py-16">
                <PageContainer>
                    <motion.div
                        initial={{ opacity: 0, scale: 0.95 }}
                        whileInView={{ opacity: 1, scale: 1 }}
                        viewport={{ once: true }}
                        transition={{ duration: 0.5 }}
                        className="rounded-lg bg-primary py-12 text-primary-foreground"
                    >
                        <div className="grid gap-8 text-center sm:grid-cols-2 md:grid-cols-4">
                            {[
                                { value: '1,234', label: 'Perkara Diputus' },
                                { value: '567', label: 'Perkara Masuk' },
                                { value: '89%', label: 'Tingkat Kepuasan' },
                                { value: '4.8', label: 'Rating Layanan' },
                            ].map((stat, index) => (
                                <motion.div
                                    key={stat.label}
                                    initial={{ opacity: 0, y: 20 }}
                                    whileInView={{ opacity: 1, y: 0 }}
                                    viewport={{ once: true }}
                                    transition={{ duration: 0.5, delay: index * 0.1 }}
                                >
                                    <div className="text-4xl font-bold">{stat.value}</div>
                                    <div className="mt-1 text-sm opacity-90">
                                        {stat.label}
                                    </div>
                                </motion.div>
                            ))}
                        </div>
                    </motion.div>
                </PageContainer>
            </section>
        </MainLayout>
    );
}
