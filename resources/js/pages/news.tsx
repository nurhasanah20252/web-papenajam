import { Head } from '@inertiajs/react';
import { Calendar, Clock, Search } from 'lucide-react';
import { useState } from 'react';

import PageContainer from '@/components/page-container';
import PageHeader from '@/components/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import MainLayout from '@/layouts/main-layout';

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
                    <div className="text-center">
                        <h1 className="text-4xl font-bold tracking-tight text-foreground md:text-5xl">
                            Berita & Pengumuman
                        </h1>
                        <p className="mt-4 text-lg text-muted-foreground">
                            Informasi terbaru dari Pengadilan Agama Penajam
                        </p>
                    </div>
                </PageContainer>
            </section>

            {/* Filters */}
            <section className="py-6">
                <PageContainer>
                    <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        {/* Category Tabs */}
                        <div className="flex flex-wrap gap-2">
                            {categories.map((category) => (
                                <Button
                                    key={category}
                                    variant={
                                        selectedCategory === category
                                            ? 'default'
                                            : 'outline'
                                    }
                                    size="sm"
                                    onClick={() => setSelectedCategory(category)}
                                >
                                    {category}
                                </Button>
                            ))}
                        </div>

                        {/* Search */}
                        <div className="relative w-full md:w-64">
                            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                placeholder="Cari berita..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="pl-9"
                            />
                        </div>
                    </div>
                </PageContainer>
            </section>

            {/* News Grid */}
            <section className="pb-12">
                <PageContainer>
                    {filteredNews.length > 0 ? (
                        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {filteredNews.map((news) => (
                                <Card
                                    key={news.id}
                                    className="overflow-hidden transition-shadow hover:shadow-lg"
                                >
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
                                            className="h-auto p-0 text-primary"
                                        >
                                            Baca Selengkapnya
                                        </Button>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    ) : (
                        <div className="py-12 text-center">
                            <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-muted">
                                <Search className="h-6 w-6 text-muted-foreground" />
                            </div>
                            <h3 className="text-lg font-semibold">
                                Tidak ada berita ditemukan
                            </h3>
                            <p className="mt-1 text-muted-foreground">
                                Coba ubah kata kunci pencarian atau kategori
                            </p>
                        </div>
                    )}

                    {/* Load More */}
                    {filteredNews.length > 0 && (
                        <div className="mt-8 text-center">
                            <Button variant="outline">Muat Lebih Banyak</Button>
                        </div>
                    )}
                </PageContainer>
            </section>
        </MainLayout>
    );
}
