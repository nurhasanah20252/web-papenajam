import { Head } from '@inertiajs/react';
import { ArrowRight, Calendar, Clock, Search } from 'lucide-react';
import { useState, lazy, Suspense } from 'react';

import PageContainer from '@/components/page-container';
import PageHeader from '@/components/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import MainLayout from '@/layouts/main-layout';
import { StaggerContainer, StaggerItem } from '@/hooks/use-stagger-children';
import { NewsCardSkeleton } from '@/components/ui/skeleton-loader';
import { motion } from 'framer-motion';

// Lazy load news detail component for code splitting
const NewsDetail = lazy(() => import('./news-detail'));

const categories = ['Semua', 'Berita', 'Pengumuman', 'Artikel'];

const newsItems = [
    {
        id: 1,
        title: 'Pengadilan Agama Penajam Menggelar Sosialisasi E-Court',
        date: '15 Januari 2024',
        category: 'Berita',
        excerpt:
            'Dalam rangka meningkatkan pelayanan kepada masyarakat, Pengadilan Agama Penajam mengadakan sosialisasi mengenai layanan E-Court yang dapat diakses secara online.',
        image: null,
    },
    {
        id: 2,
        title: 'Pengumuman Seleksi Calon Hakim',
        date: '10 Januari 2024',
        category: 'Pengumuman',
        excerpt:
            'Pengadilan Agama Penajam membuka pendaftaran seleksi calon hakim untuk mengisi posisi yang kosong. Pendaftaran dibuka hingga tanggal 25 Januari 2024.',
        image: null,
    },
    {
        id: 3,
        title: 'Rapat Koordinasi Pembangunan Zona Integritas',
        date: '5 Januari 2024',
        category: 'Berita',
        excerpt:
            'Dalam rangka mewujudkan wilayah bebas dari korupsi, Pengadilan Agama Penajam mengadakan rapat koordinasi pembangunan zona integritas menuju WBK.',
        image: null,
    },
    {
        id: 4,
        title: 'Pentingnya Mediasi dalam Penyelesaian Sengketa',
        date: '28 Desember 2023',
        category: 'Artikel',
        excerpt:
            'Mediasi merupakan salah satu upaya penyelesaian sengketa yang dapat dipilih oleh para pihak sebelum berperkara di pengadilan. Berikut penjelasannya.',
        image: null,
    },
    {
        id: 5,
        title: 'Jadwal Layanan Pengadilan Selama Libur Nataru',
        date: '20 Desember 2023',
        category: 'Pengumuman',
        excerpt:
            'Pengadilan Agama Penajam menyampaikan informasi terkait jadwal layanan selama periode Natal dan Tahun Baru 2024.',
        image: null,
    },
    {
        id: 6,
        title: 'Optimalisasi Penggunaan SIKEP untuk Administrasi Perkara',
        date: '15 Desember 2023',
        category: 'Berita',
        excerpt:
            'Pengadilan Agama Penajam terus mengoptimalkan penggunaan Sistem Informasi Kepegawaian (SIKEP) untuk meningkatkan efisiensi administrasi.',
        image: null,
    },
];

export default function News() {
    const [selectedCategory, setSelectedCategory] = useState('Semua');
    const [searchQuery, setSearchQuery] = useState('');

    const filteredNews = newsItems.filter((news) => {
        const matchesCategory =
            selectedCategory === 'Semua' || news.category === selectedCategory;
        const matchesSearch =
            searchQuery === '' ||
            news.title.toLowerCase().includes(searchQuery.toLowerCase());
        return matchesCategory && matchesSearch;
    });

    return (
        <MainLayout>
            <Head title="Berita & Pengumuman">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700"
                    rel="stylesheet"
                />
            </Head>

            {/* Hero */}
            <section className="bg-gradient-to-b from-primary/5 to-background py-12 md:py-16">
                <PageContainer>
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.6 }}
                        className="text-center"
                    >
                        <h1 className="text-4xl font-bold tracking-tight text-foreground md:text-5xl">
                            Berita & Pengumuman
                        </h1>
                        <motion.p
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            transition={{ duration: 0.6, delay: 0.2 }}
                            className="mt-4 text-lg text-muted-foreground"
                        >
                            Informasi terbaru dari Pengadilan Agama Penajam
                        </motion.p>
                    </motion.div>
                </PageContainer>
            </section>

            {/* Filters */}
            <section className="py-6">
                <PageContainer>
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.4, delay: 0.3 }}
                        className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                    >
                        {/* Category Tabs */}
                        <div className="flex flex-wrap gap-2">
                            {categories.map((category, index) => (
                                <motion.div
                                    key={category}
                                    initial={{ opacity: 0, y: 10 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    transition={{ duration: 0.3, delay: 0.4 + index * 0.05 }}
                                >
                                    <Button
                                        variant={
                                            selectedCategory === category
                                                ? 'default'
                                                : 'outline'
                                        }
                                        size="sm"
                                        onClick={() => setSelectedCategory(category)}
                                        className="hover-lift"
                                    >
                                        {category}
                                    </Button>
                                </motion.div>
                            ))}
                        </div>

                        {/* Search */}
                        <motion.div
                            initial={{ opacity: 0, x: 20 }}
                            animate={{ opacity: 1, x: 0 }}
                            transition={{ duration: 0.4, delay: 0.6 }}
                            className="relative w-full md:w-64"
                        >
                            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                placeholder="Cari berita..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="pl-9 transition-all focus:ring-2 focus:ring-primary/20"
                            />
                        </motion.div>
                    </motion.div>
                </PageContainer>
            </section>

            {/* News Grid */}
            <section className="pb-12">
                <PageContainer>
                    {filteredNews.length > 0 ? (
                        <StaggerContainer className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {filteredNews.map((news, index) => (
                                <StaggerItem key={news.id} delay={index * 0.05}>
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
                                            <p className="mb-4 text-sm text-muted-foreground line-clamp-3">
                                                {news.excerpt}
                                            </p>
                                            <Button
                                                variant="link"
                                                className="h-auto p-0 text-primary group"
                                            >
                                                Baca Selengkapnya
                                                <ArrowRight className="ml-1 h-4 w-4 transition-transform group-hover:translate-x-1" />
                                            </Button>
                                        </CardContent>
                                    </Card>
                                </StaggerItem>
                            ))}
                        </StaggerContainer>
                    ) : (
                        <motion.div
                            initial={{ opacity: 0, scale: 0.95 }}
                            animate={{ opacity: 1, scale: 1 }}
                            transition={{ duration: 0.3 }}
                            className="py-12 text-center"
                        >
                            <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-muted">
                                <Search className="h-6 w-6 text-muted-foreground" />
                            </div>
                            <h3 className="text-lg font-semibold">
                                Tidak ada berita ditemukan
                            </h3>
                            <p className="mt-1 text-muted-foreground">
                                Coba ubah kata kunci pencarian atau kategori
                            </p>
                        </motion.div>
                    )}

                    {/* Load More */}
                    {filteredNews.length > 0 && (
                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            transition={{ duration: 0.4, delay: 0.8 }}
                            className="mt-8 text-center"
                        >
                            <Button variant="outline" className="hover-lift">
                                Muat Lebih Banyak
                            </Button>
                        </motion.div>
                    )}
                </PageContainer>
            </section>
        </MainLayout>
    );
}
