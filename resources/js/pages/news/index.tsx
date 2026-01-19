import { Head, Link, router } from '@inertiajs/react';
import { Calendar, Search } from 'lucide-react';
import { useState } from 'react';

import PageContainer from '@/components/page-container';
import PageHeader from '@/components/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import MainLayout from '@/layouts/main-layout';

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface NewsItem {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    featured_image: string | null;
    category: {
        id: number;
        name: string;
        slug: string;
    } | null;
    author: {
        id: number;
        name: string;
    } | null;
    published_at: string;
    tags: string[] | null;
}

interface PaginatedData {
    data: NewsItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
}

interface Props {
    news: PaginatedData;
    featuredNews: NewsItem[];
    categories: Category[];
    filters: {
        category: string;
        tag: string | null;
        search: string | null;
    };
}

const formatDate = (dateStr: string): string => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

export default function NewsIndex({ news, featuredNews, categories, filters }: Props) {
    const [selectedCategory, setSelectedCategory] = useState(filters.category || 'all');
    const [searchQuery, setSearchQuery] = useState(filters.search || '');

    const handleCategoryChange = (categorySlug: string) => {
        setSelectedCategory(categorySlug);
        router.get(
            '/news',
            {
                category: categorySlug === 'all' ? undefined : categorySlug,
                tag: filters.tag,
                search: searchQuery || undefined,
            },
            { preserveState: true }
        );
    };

    const handleSearch = (query: string) => {
        setSearchQuery(query);
        if (query.length >= 2 || query.length === 0) {
            router.get(
                '/news',
                {
                    category: selectedCategory === 'all' ? undefined : selectedCategory,
                    tag: filters.tag,
                    search: query || undefined,
                },
                { preserveState: true }
            );
        }
    };

    const handlePageChange = (page: number) => {
        router.get(
            '/news',
            {
                page,
                category: selectedCategory === 'all' ? undefined : selectedCategory,
                tag: filters.tag,
                search: searchQuery || undefined,
            },
            { preserveState: true }
        );
    };

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

            {/* Featured News */}
            {featuredNews.length > 0 && (
                <section className="border-b py-8">
                    <PageContainer>
                        <div className="mb-4 flex items-center justify-between">
                            <h2 className="text-xl font-semibold">Berita Utama</h2>
                        </div>
                        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {featuredNews.map((item) => (
                                <Link key={item.id} href={`/news/${item.slug}`}>
                                    <Card className="overflow-hidden transition-shadow hover:shadow-lg">
                                        {item.featured_image && (
                                            <div className="relative aspect-video w-full overflow-hidden">
                                                <img
                                                    src={item.featured_image}
                                                    alt={item.title}
                                                    className="h-full w-full object-cover"
                                                />
                                            </div>
                                        )}
                                        {!item.featured_image && <div className="h-2 bg-primary" />}
                                        <CardContent className="pt-6">
                                            <div className="mb-3 flex items-center gap-2 text-xs text-muted-foreground">
                                                {item.category && (
                                                    <span className="rounded-full bg-primary/10 px-2 py-1 text-primary">
                                                        {item.category.name}
                                                    </span>
                                                )}
                                                <span className="flex items-center gap-1">
                                                    <Calendar className="h-3 w-3" />
                                                    {formatDate(item.published_at)}
                                                </span>
                                            </div>
                                            <h3 className="mb-2 font-semibold line-clamp-2">{item.title}</h3>
                                            <p className="text-sm text-muted-foreground line-clamp-3">
                                                {item.excerpt}
                                            </p>
                                        </CardContent>
                                    </Card>
                                </Link>
                            ))}
                        </div>
                    </PageContainer>
                </section>
            )}

            {/* Filters */}
            <section className="py-6">
                <PageContainer>
                    <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        {/* Category Tabs */}
                        <div className="flex flex-wrap gap-2">
                            <Button
                                variant={selectedCategory === 'all' ? 'default' : 'outline'}
                                size="sm"
                                onClick={() => handleCategoryChange('all')}
                            >
                                Semua
                            </Button>
                            {categories.map((category) => (
                                <Button
                                    key={category.id}
                                    variant={selectedCategory === category.slug ? 'default' : 'outline'}
                                    size="sm"
                                    onClick={() => handleCategoryChange(category.slug)}
                                >
                                    {category.name}
                                </Button>
                            ))}
                        </div>

                        {/* Search */}
                        <div className="relative w-full md:w-64">
                            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                placeholder="Cari berita..."
                                value={searchQuery}
                                onChange={(e) => handleSearch(e.target.value)}
                                className="pl-9"
                            />
                        </div>
                    </div>
                </PageContainer>
            </section>

            {/* News Grid */}
            <section className="pb-12">
                <PageContainer>
                    {news.data.length > 0 ? (
                        <>
                            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                                {news.data.map((item) => (
                                    <Link key={item.id} href={`/news/${item.slug}`}>
                                        <Card className="overflow-hidden transition-shadow hover:shadow-lg">
                                            {item.featured_image && (
                                                <div className="relative aspect-video w-full overflow-hidden">
                                                    <img
                                                        src={item.featured_image}
                                                        alt={item.title}
                                                        className="h-full w-full object-cover"
                                                    />
                                                </div>
                                            )}
                                            {!item.featured_image && <div className="h-2 bg-primary" />}
                                            <CardContent className="pt-6">
                                                <div className="mb-3 flex items-center gap-2 text-xs text-muted-foreground">
                                                    {item.category && (
                                                        <span className="rounded-full bg-primary/10 px-2 py-1 text-primary">
                                                            {item.category.name}
                                                        </span>
                                                    )}
                                                    <span className="flex items-center gap-1">
                                                        <Calendar className="h-3 w-3" />
                                                        {formatDate(item.published_at)}
                                                    </span>
                                                </div>
                                                <h3 className="mb-2 font-semibold line-clamp-2">{item.title}</h3>
                                                <p className="mb-4 text-sm text-muted-foreground line-clamp-3">
                                                    {item.excerpt}
                                                </p>
                                                <Button
                                                    variant="link"
                                                    className="h-auto p-0 text-primary"
                                                >
                                                    Baca Selengkapnya
                                                </Button>
                                            </CardContent>
                                        </Card>
                                    </Link>
                                ))}
                            </div>

                            {/* Pagination */}
                            {news.last_page > 1 && (
                                <div className="mt-8 flex items-center justify-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        disabled={!news.links.prev}
                                        onClick={() => handlePageChange(news.current_page - 1)}
                                    >
                                        Previous
                                    </Button>
                                    <span className="text-sm text-muted-foreground">
                                        Page {news.current_page} of {news.last_page}
                                    </span>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        disabled={!news.links.next}
                                        onClick={() => handlePageChange(news.current_page + 1)}
                                    >
                                        Next
                                    </Button>
                                </div>
                            )}
                        </>
                    ) : (
                        <div className="py-12 text-center">
                            <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-muted">
                                <Search className="h-6 w-6 text-muted-foreground" />
                            </div>
                            <h3 className="text-lg font-semibold">Tidak ada berita ditemukan</h3>
                            <p className="mt-1 text-muted-foreground">
                                Coba ubah kata kunci pencarian atau kategori
                            </p>
                        </div>
                    )}
                </PageContainer>
            </section>
        </MainLayout>
    );
}
