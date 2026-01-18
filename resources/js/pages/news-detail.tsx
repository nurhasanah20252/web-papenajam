import { Head } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import {
    Calendar,
    ChevronLeft,
    Clock,
    Facebook,
    Link as LinkIcon,
    MessageSquare,
    Share2,
    Tag,
    Twitter,
} from 'lucide-react';
import { useState } from 'react';

import PageContainer from '@/components/page-container';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import MainLayout from '@/layouts/main-layout';

// Types for news data
interface NewsArticle {
    id: number;
    slug: string;
    title: string;
    content: string;
    excerpt: string;
    category: string;
    published_at: string;
    author: string;
    image?: string;
    tags: string[];
    view_count: number;
}

interface RelatedNews {
    id: number;
    title: string;
    slug: string;
    category: string;
    published_at: string;
    image?: string;
}

// Mock article data
const mockArticle: NewsArticle = {
    id: 1,
    slug: 'pengadilan-agama-penajam-menggelar-sosialisasi-e-court',
    title: 'Pengadilan Agama Penajam Menggelar Sosialisasi E-Court',
    content: `
        <p>Pengadilan Agama Penajam mengadakan sosialisasi mengenai layanan E-Court yang dapat diakses secara online. Kegiatan ini merupakan upaya untuk meningkatkan pelayanan kepada masyarakat dalam melakukan berbagai keperluan perkara.</p>

        <p>Sosialisasi ini diikuti oleh sejumlah advokat, panitera, dan pihak-pihak terkait lainnya. Dalam kesempatan tersebut, dijelaskan berbagai fitur yang tersedia dalam sistem E-Court, mulai dari pendaftaran perkara online, pembayaran biaya perkara melalui transfer, hingga pengajuan permohonan upaya hukum.</p>

        <h2>Tujuan Sosialisasi</h2>
        <p>Kepala Pengadilan Agama Penajam dalam arahannya menegaskan bahwa sosialisasi ini bertujuan untuk:</p>
        <ul>
            <li>Meningkatkan pemahaman masyarakat tentang layanan E-Court</li>
            <li>Mempercepat proses pendaftaran perkara</li>
            <li>Mengurangi tatap muka langsung di pengadilan</li>
            <li>Mempermudah akses keadilan bagi masyarakat</li>
        </ul>

        <h2>Fitur E-Court</h2>
        <p>Sistem E-Court menghadirkan berbagai kemudahan bagi masyarakat, antara lain:</p>
        <ol>
            <li>Pendaftaran perkara secara online tanpa perlu datang ke pengadilan</li>
            <li>Pembayaran biaya perkara melalui berbagai channel pembayaran</li>
            <li>Pengawasan perkembangan perkara secara real-time</li>
            <li>Pengajuan permohonan persidangan secara elektronik</li>
        </ol>

        <p>Dengan adanya sistem ini, diharapkan masyarakat dapat merasakan kemudahan dalam mengakses layanan pengadilan tanpa terkendan oleh jarak dan waktu. Pengadilan Agama Penajam berkomitmen untuk terus meningkatkan kualitas pelayanannya demi terwujudnya akses keadilan yang mudah, cepat, dan terjangkau.</p>
    `,
    excerpt:
        'Dalam rangka meningkatkan pelayanan kepada masyarakat, Pengadilan Agama Penajam mengadakan sosialisasi mengenai layanan E-Court yang dapat diakses secara online.',
    category: 'Berita',
    published_at: '2024-01-15',
    author: 'Humas Pengadilan Agama Penajam',
    tags: ['E-Court', 'Pelayanan', 'Modernisasi', 'Sosialisasi'],
    view_count: 1250,
};

const relatedNews: RelatedNews[] = [
    {
        id: 2,
        title: 'Pengumuman Seleksi Calon Hakim',
        slug: 'pengumuman-seleksi-calon-hakim',
        category: 'Pengumuman',
        published_at: '2024-01-10',
    },
    {
        id: 3,
        title: 'Rapat Koordinasi Pembangunan Zona Integritas',
        slug: 'rapat-koordinasi-zona-integritas',
        category: 'Berita',
        published_at: '2024-01-05',
    },
    {
        id: 4,
        title: 'Pentingnya Mediasi dalam Penyelesaian Sengketa',
        slug: 'pentingnya-mediasi',
        category: 'Artikel',
        published_at: '2023-12-28',
    },
];

// Safe HTML sanitizer - allows only basic formatting tags
const sanitizeHtml = (html: string): string => {
    const allowedTags = [
        'p', 'br', 'strong', 'em', 'u', 's', 'a', 'ul', 'ol', 'li',
        'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'code',
        'span', 'div',
    ];

    return html
        .replace(/<([a-z][a-z0-9]*)\b[^>]*>/gi, (match, tag) => {
            if (allowedTags.includes(tag.toLowerCase())) {
                return match.replace(/(\s[a-zA-Z0-9-]+)(="[^"]*")?/g, (attrMatch, attrName, attrValue) => {
                    // Allow class and href attributes
                    if ((attrName.toLowerCase() === 'class' || attrName.toLowerCase() === 'href') && attrValue) {
                        return attrMatch;
                    }
                    return '';
                });
            }
            return '';
        })
        .replace(/<\/([a-z][a-z0-9]*)\b[^>]*>/gi, (match, tag) => {
            if (allowedTags.includes(tag.toLowerCase())) {
                return match;
            }
            return '';
        })
        .replace(/\n{3,}/g, '\n\n')
        .trim();
};

export default function NewsDetail() {
    const [showShareMenu, setShowShareMenu] = useState(false);

    const formatDate = (dateStr: string) => {
        const date = new Date(dateStr);
        return date.toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    const copyLink = () => {
        navigator.clipboard.writeText(window.location.href);
    };

    const shareViaWhatsApp = () => {
        const url = encodeURIComponent(window.location.href);
        const text = encodeURIComponent(mockArticle.title);
        window.open(`https://wa.me/?text=${text}%20${url}`, '_blank');
    };

    const shareViaFacebook = () => {
        const url = encodeURIComponent(window.location.href);
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
    };

    const shareViaTwitter = () => {
        const url = encodeURIComponent(window.location.href);
        const text = encodeURIComponent(mockArticle.title);
        window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
    };

    return (
        <MainLayout>
            <Head title={mockArticle.title}>
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700"
                    rel="stylesheet"
                />
            </Head>

            {/* Article Header */}
            <section className="bg-gradient-to-b from-primary/5 to-background py-8 md:py-12">
                <PageContainer size="md">
                    <Link
                        href="/news"
                        className="mb-4 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ChevronLeft className="h-4 w-4" />
                        Kembali ke Berita
                    </Link>

                    <div className="mb-4 flex flex-wrap items-center gap-2">
                        <Badge>{mockArticle.category}</Badge>
                        <span className="text-sm text-muted-foreground">
                            {formatDate(mockArticle.published_at)}
                        </span>
                    </div>

                    <h1 className="mb-4 text-3xl font-bold leading-tight tracking-tight md:text-4xl lg:text-5xl">
                        {mockArticle.title}
                    </h1>

                    <div className="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                        <div className="flex items-center gap-1">
                            <div className="flex h-6 w-6 items-center justify-center rounded-full bg-primary/10 text-primary">
                                {mockArticle.author.charAt(0)}
                            </div>
                            <span>{mockArticle.author}</span>
                        </div>
                        <div className="flex items-center gap-1">
                            <Clock className="h-4 w-4" />
                            <span>5 menit membaca</span>
                        </div>
                        <div className="flex items-center gap-1">
                            <MessageSquare className="h-4 w-4" />
                            <span>{mockArticle.view_count.toLocaleString()}x dilihat</span>
                        </div>
                    </div>
                </PageContainer>
            </section>

            {/* Article Content */}
            <section className="py-8">
                <PageContainer size="md">
                    <Card>
                        <CardContent className="pt-6">
                            {/* Share Buttons */}
                            <div className="mb-6 flex items-center justify-between border-b pb-4">
                                <div className="flex items-center gap-2">
                                    <span className="text-sm text-muted-foreground">Bagikan:</span>
                                    <div className="flex gap-1">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            className="h-8 w-8"
                                            onClick={shareViaWhatsApp}
                                        >
                                            <WhatsApp className="h-4 w-4 text-green-500" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            className="h-8 w-8"
                                            onClick={shareViaFacebook}
                                        >
                                            <Facebook className="h-4 w-4 text-blue-600" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            className="h-8 w-8"
                                            onClick={shareViaTwitter}
                                        >
                                            <Twitter className="h-4 w-4 text-sky-500" />
                                        </Button>
                                    </div>
                                </div>
                                <div className="relative">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => setShowShareMenu(!showShareMenu)}
                                    >
                                        <Share2 className="mr-1 h-4 w-4" />
                                        Lainnya
                                    </Button>
                                    {showShareMenu && (
                                        <div className="absolute right-0 top-full z-10 mt-1 w-48 rounded-md border bg-background py-1 shadow-lg">
                                            <button
                                                className="flex w-full items-center gap-2 px-4 py-2 text-sm hover:bg-muted"
                                                onClick={copyLink}
                                            >
                                                <LinkIcon className="h-4 w-4" />
                                                Salin Tautan
                                            </button>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Article Body */}
                            <article
                                className="prose prose-sm max-w-none dark:prose-invert"
                                dangerouslySetInnerHTML={{ __html: sanitizeHtml(mockArticle.content) }}
                            />

                            {/* Tags */}
                            <div className="mt-8 border-t pt-6">
                                <div className="flex flex-wrap items-center gap-2">
                                    <Tag className="h-4 w-4 text-muted-foreground" />
                                    {mockArticle.tags.map((tag) => (
                                        <Badge key={tag} variant="secondary">
                                            {tag}
                                        </Badge>
                                    ))}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </PageContainer>
            </section>

            {/* Related News */}
            <section className="py-8">
                <PageContainer size="md">
                    <Separator className="mb-8" />
                    <h2 className="mb-6 text-2xl font-bold">Berita Terkait</h2>
                    <div className="grid gap-4 md:grid-cols-3">
                        {relatedNews.map((news) => (
                            <Card key={news.id} className="overflow-hidden transition-shadow hover:shadow-md">
                                <CardContent className="p-0">
                                    <div className="h-2 bg-primary" />
                                    <div className="p-4">
                                        <div className="mb-2 flex items-center gap-2 text-xs text-muted-foreground">
                                            <span className="rounded-full bg-primary/10 px-2 py-1 text-primary">
                                                {news.category}
                                            </span>
                                            <span className="flex items-center gap-1">
                                                <Calendar className="h-3 w-3" />
                                                {formatDate(news.published_at)}
                                            </span>
                                        </div>
                                        <h3 className="mb-2 font-semibold line-clamp-2">
                                            {news.title}
                                        </h3>
                                        <Link
                                            href={`/news/${news.slug}`}
                                            className="text-sm text-primary hover:underline"
                                        >
                                            Baca Selengkapnya
                                        </Link>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </PageContainer>
            </section>

            {/* Comments Section (Optional) */}
            <section className="py-8">
                <PageContainer size="md">
                    <Separator className="mb-8" />
                    <h2 className="mb-6 text-2xl font-bold">Komentar</h2>
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center py-8">
                                <MessageSquare className="mx-auto h-12 w-12 text-muted-foreground/50" />
                                <h3 className="mt-4 text-lg font-semibold">Belum ada komentar</h3>
                                <p className="mt-2 text-sm text-muted-foreground">
                                    Jadilah yang pertama memberikan komentar pada berita ini.
                                </p>
                                <Button className="mt-4">Tulis Komentar</Button>
                            </div>
                        </CardContent>
                    </Card>
                </PageContainer>
            </section>
        </MainLayout>
    );
}
