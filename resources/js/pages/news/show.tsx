import { Head, Link } from '@inertiajs/react';
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
import DOMPurify from 'isomorphic-dompurify';

// WhatsApp icon component
const WhatsApp = ({ className }: { className?: string }) => (
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="currentColor"
        className={className}
    >
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
    </svg>
);

import PageContainer from '@/components/page-container';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import MainLayout from '@/layouts/main-layout';

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface Author {
    id: number;
    name: string;
}

interface NewsArticle {
    id: number;
    slug: string;
    title: string;
    content: string | null;
    excerpt: string | null;
    featured_image: string | null;
    category: Category | null;
    author: Author | null;
    published_at: string;
    tags: string[] | null;
    views_count: number;
    created_at: string;
    updated_at: string;
}

interface RelatedNews {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    featured_image: string | null;
    category: Category | null;
    published_at: string;
}

interface Props {
    news: NewsArticle;
    relatedNews: RelatedNews[];
    latestNews: RelatedNews[];
}

const formatDate = (dateStr: string): string => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getReadingTime = (content: string | null): string => {
    if (!content) return '1 menit membaca';

    // Estimate reading time based on word count
    // Average reading speed is 200-250 words per minute
    const textContent = content.replace(/<[^>]*>/g, ''); // Remove HTML tags
    const wordCount = textContent.split(/\s+/).length;
    const minutes = Math.max(1, Math.ceil(wordCount / 200));

    return `${minutes} menit membaca`;
};

export default function NewsShow({ news, relatedNews, latestNews }: Props) {
    const [showShareMenu, setShowShareMenu] = useState(false);

    const copyLink = () => {
        navigator.clipboard.writeText(window.location.href);
        alert('Tautan berhasil disalin!');
    };

    const shareViaWhatsApp = () => {
        const url = encodeURIComponent(window.location.href);
        const text = encodeURIComponent(news.title);
        window.open(`https://wa.me/?text=${text}%20${url}`, '_blank');
    };

    const shareViaFacebook = () => {
        const url = encodeURIComponent(window.location.href);
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
    };

    const shareViaTwitter = () => {
        const url = encodeURIComponent(window.location.href);
        const text = encodeURIComponent(news.title);
        window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
    };

    const getMetaDescription = (): string => {
        return news.excerpt || news.title;
    };

    const sanitizeContent = (html: string): string => {
        return DOMPurify.sanitize(html, {
            ALLOWED_TAGS: [
                'p',
                'br',
                'strong',
                'em',
                'u',
                's',
                'a',
                'ul',
                'ol',
                'li',
                'h2',
                'h3',
                'h4',
                'h5',
                'h6',
                'blockquote',
                'pre',
                'code',
                'span',
                'div',
            ],
            ALLOWED_ATTR: ['href', 'class', 'target', 'rel'],
            ALLOW_DATA_ATTR: false,
        });
    };

    return (
        <MainLayout>
            <Head title={news.title}>
                <meta name="description" content={getMetaDescription()} />
                <meta property="og:title" content={news.title} />
                <meta property="og:description" content={getMetaDescription()} />
                {news.featured_image && <meta property="og:image" content={news.featured_image} />}
                <meta property="og:type" content="article" />
                <meta property="article:published_time" content={news.published_at} />
                {news.category && <meta property="article:section" content={news.category.name} />}
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
                        {news.category && <Badge>{news.category.name}</Badge>}
                        <span className="text-sm text-muted-foreground">{formatDate(news.published_at)}</span>
                    </div>

                    <h1 className="mb-4 text-3xl font-bold leading-tight tracking-tight md:text-4xl lg:text-5xl">
                        {news.title}
                    </h1>

                    {news.excerpt && <p className="mb-6 text-lg text-muted-foreground">{news.excerpt}</p>}

                    <div className="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                        {news.author && (
                            <div className="flex items-center gap-2">
                                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold">
                                    {news.author.name.charAt(0)}
                                </div>
                                <span className="font-medium">{news.author.name}</span>
                            </div>
                        )}
                        <div className="flex items-center gap-1">
                            <Clock className="h-4 w-4" />
                            <span>{getReadingTime(news.content)}</span>
                        </div>
                        <div className="flex items-center gap-1">
                            <MessageSquare className="h-4 w-4" />
                            <span>{news.views_count.toLocaleString()}x dilihat</span>
                        </div>
                    </div>
                </PageContainer>
            </section>

            {/* Featured Image */}
            {news.featured_image && (
                <section>
                    <PageContainer size="md">
                        <div className="overflow-hidden rounded-lg">
                            <img src={news.featured_image} alt={news.title} className="h-auto w-full" />
                        </div>
                    </PageContainer>
                </section>
            )}

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
                            {news.content && (
                                <article
                                    className="prose prose-sm max-w-none dark:prose-invert"
                                    dangerouslySetInnerHTML={{
                                        __html: sanitizeContent(news.content),
                                    }}
                                />
                            )}

                            {/* Tags */}
                            {news.tags && news.tags.length > 0 && (
                                <div className="mt-8 border-t pt-6">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <Tag className="h-4 w-4 text-muted-foreground" />
                                        {news.tags.map((tag) => (
                                            <Link key={tag} href={`/news/tag/${tag}`}>
                                                <Badge
                                                    variant="secondary"
                                                    className="cursor-pointer hover:bg-secondary/80"
                                                >
                                                    {tag}
                                                </Badge>
                                            </Link>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </PageContainer>
            </section>

            {/* Related News */}
            {relatedNews.length > 0 && (
                <section className="py-8">
                    <PageContainer size="md">
                        <Separator className="mb-8" />
                        <h2 className="mb-6 text-2xl font-bold">Berita Terkait</h2>
                        <div className="grid gap-4 md:grid-cols-3">
                            {relatedNews.map((item) => (
                                <Link key={item.id} href={`/news/${item.slug}`}>
                                    <Card className="overflow-hidden transition-shadow hover:shadow-md">
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
                                        <div className="p-4">
                                            <div className="mb-2 flex items-center gap-2 text-xs text-muted-foreground">
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
                                            {item.excerpt && (
                                                <p className="text-sm text-muted-foreground line-clamp-2">
                                                    {item.excerpt}
                                                </p>
                                            )}
                                        </div>
                                    </Card>
                                </Link>
                            ))}
                        </div>
                    </PageContainer>
                </section>
            )}

            {/* Latest News */}
            {latestNews.length > 0 && (
                <section className="py-8">
                    <PageContainer size="md">
                        <Separator className="mb-8" />
                        <h2 className="mb-6 text-2xl font-bold">Berita Terbaru</h2>
                        <div className="grid gap-4 md:grid-cols-3">
                            {latestNews.map((item) => (
                                <Link key={item.id} href={`/news/${item.slug}`}>
                                    <Card className="overflow-hidden transition-shadow hover:shadow-md">
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
                                        <div className="p-4">
                                            <div className="mb-2 flex items-center gap-2 text-xs text-muted-foreground">
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
                                        </div>
                                    </Card>
                                </Link>
                            ))}
                        </div>
                    </PageContainer>
                </section>
            )}
        </MainLayout>
    );
}
