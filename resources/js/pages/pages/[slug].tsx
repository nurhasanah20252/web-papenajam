import { Head } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import { useMemo } from 'react';

import PageContainer from '@/components/page-container';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import MainLayout from '@/layouts/main-layout';
import { type BreadcrumbItem } from '@/types';

// Types for page data
interface Page {
    id: number;
    slug: string;
    title: string;
    content: string;
    excerpt?: string;
    template: 'default' | 'sidebar' | 'landing' | 'contact';
    meta_title?: string;
    meta_description?: string;
    published_at: string;
    updated_at: string;
    featured_image?: string;
    author?: string;
    tags: string[];
}

interface PageNavigation {
    id: number;
    title: string;
    slug: string;
    children?: PageNavigation[];
}

// Mock CMS page data
const mockPage: Page = {
    id: 1,
    slug: 'profil-pengadilan',
    title: 'Profil Pengadilan Agama Penajam',
    content: `
        <h2>Sejarah Pengadilan Agama Penajam</h2>
        <p>Pengadilan Agama Penajam merupakan lembaga peradilan tingkat pertama yang berada di wilayah hukum Kabupaten Penajam Paser Utara, Provinsi Kalimantan Timur. Pengadilan ini dibentuk berdasarkan Undang-Undang Nomor 7 Tahun 1989 tentang Peradilan Agama sebagaimana telah diubah dengan Undang-Undang Nomor 3 Tahun 2006 dan Undang-Undang Nomor 50 Tahun 2009.</p>

        <h2>Visi dan Misi</h2>
        <h3>Visi</h3>
        <p>Terwujudnya Pengadilan Agama Penajam yang Agung, Profesional, dan Terpercaya dalam rangka Menegakkan Keadilan untuk Masyarakat.</p>

        <h3>Misi</h3>
        <ul>
            <li>Meningkatkan kualitas pelayanan hukum yang profesional, efektif, dan efisien</li>
            <li>Mewujudkan kekuasaan kehakiman yang merdeka, jujur, dan adil</li>
            <li>Meningkatkan kepercayaan masyarakat melalui pelayanan yang prima</li>
            <li>Mengoptimalkan pemanfaatan teknologi informasi dalam proses peradilan</li>
            <li>Membangun sumber daya manusia yang profesional dan berintegritas</li>
        </ul>

        <h2>Struktur Organisasi</h2>
        <p>Pengadilan Agama Penajam dipimpin oleh seorang Ketua yang bertanggung jawab atas pelaksanaan tugas dan fungsi pengadilan. Berikut adalah struktur organisasi Pengadilan Agama Penajam:</p>

        <h3>Kepaniteraan</h3>
        <p>Kepaniteraan Pengadilan Agama Penajam bertugas menyelenggarakan administrasi perkara dan sumpah/janji. Kepaniteraan dipimpin oleh Panitera yang bertanggung jawab kepada Ketua Pengadilan.</p>

        <h3>Kesekretariatan</h3>
        <p>Kesekretariatan Pengadilan Agama Penajam bertugas menyelenggarakan administrasi umum yang meliputi kepegawaian, keuangan, perencanaan, dan perlengkapan. Kesekretariatan dipimpin oleh Sekretaris yang bertanggung jawab kepada Ketua Pengadilan.</p>

        <h2>Wilayah Hukum</h2>
        <p>Pengadilan Agama Penajam memiliki wilayah hukum yang meliputi seluruh wilayah Kabupaten Penajam Paser Utara, yang terdiri dari empat kecamatan:</p>
        <ol>
            <li>Kecamatan Penajam</li>
            <li>Kecamatan Waru</li>
            <li>Kecamatan Babulu</li>
            <li>Kecamatan Sepaku</li>
        </ol>

        <h2>Jam Pelayanan</h2>
        <p>Pengadilan Agama Penajam memberikan pelayanan kepada masyarakat pada hari Senin hingga Kamis pukul 08.00-16.30 WITA, dan hari Jumat pukul 08.00-17.00 WITA. Sidang pertama umumnya dilaksanakan pada pukul 09.00 WITA.</p>
    `,
    excerpt:
        'Profil Pengadilan Agama Penajam meliputi sejarah, visi, misi, struktur organisasi, wilayah hukum, dan informasi pelayanan.',
    template: 'sidebar',
    meta_title: 'Profil Pengadilan Agama Penajam',
    meta_description:
        'Informasi lengkap tentang Pengadilan Agama Penajam termasuk sejarah, visi, misi, struktur organisasi, dan jam pelayanan.',
    published_at: '2024-01-01',
    updated_at: '2024-01-15',
    author: 'Admin',
    tags: ['Profil', 'Pengadilan', 'Penajam'],
};

const pageNavigation: PageNavigation[] = [
    {
        id: 1,
        title: 'Profil',
        slug: 'profil-pengadilan',
        children: [
            { id: 2, title: 'Sejarah', slug: 'sejarah' },
            { id: 3, title: 'Visi & Misi', slug: 'visi-misi' },
            { id: 4, title: 'Struktur Organisasi', slug: 'struktur-organisasi' },
            { id: 5, title: 'Kehumasan', slug: 'kehumasan' },
        ],
    },
    {
        id: 6,
        title: 'Layanan',
        slug: 'layanan',
        children: [
            { id: 7, title: 'Pendaftaran Perkara', slug: 'pendaftaran-perkara' },
            { id: 8, title: 'E-Court', slug: 'e-court' },
            { id: 9, title: 'Prosedur', slug: 'prosedur' },
        ],
    },
    {
        id: 10,
        title: 'Informasi',
        slug: 'informasi',
        children: [
            { id: 11, title: 'Jam Layanan', slug: 'jam-layanan' },
            { id: 12, title: 'Lokasi', slug: 'lokasi' },
            { id: 13, title: 'Kontak', slug: 'kontak' },
        ],
    },
];

// Safe HTML sanitizer - allows only basic formatting tags
const sanitizeHtml = (html: string): string => {
    const allowedTags = [
        'p', 'br', 'strong', 'em', 'u', 's', 'a', 'ul', 'ol', 'li',
        'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'code',
        'span', 'div', 'table', 'thead', 'tbody', 'tr', 'th', 'td',
    ];

    const allowedAttributes: Record<string, string[]> = {
        a: ['href', 'target', 'rel'],
        span: ['class'],
        div: ['class'],
        td: ['colspan', 'rowspan'],
        th: ['colspan', 'rowspan'],
    };

    return html
        .replace(/<([a-z][a-z0-9]*)\b[^>]*>/gi, (match, tag) => {
            if (allowedTags.includes(tag.toLowerCase())) {
                // Strip all attributes except allowed ones
                return match.replace(/(\s[a-zA-Z0-9-]+)(="[^"]*")?/g, (attrMatch, attrName, attrValue) => {
                    const tagLower = tag.toLowerCase();
                    const allowedAttrs = allowedAttributes[tagLower] || [];

                    // Check if this attribute is allowed for this tag
                    if (attrValue && allowedAttrs.some(a => attrName.toLowerCase().startsWith(a.toLowerCase()))) {
                        return attrMatch;
                    }
                    // Allow class attribute generally
                    if (attrName.toLowerCase() === 'class' && attrValue) {
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
        // Clean up empty tags
        .replace(/<[^>]*>\s*<\/[^>]*>/g, '')
        // Remove remaining unclosed tags
        .replace(/<[a-z][a-z0-9]*[^>]*>/gi, '')
        // Clean up excessive whitespace
        .replace(/\n{3,}/g, '\n\n')
        .trim();
};

// Flatten navigation for breadcrumbs
const flattenNavigation = (
    items: PageNavigation[],
): { title: string; slug: string }[] => {
    const result: { title: string; slug: string }[] = [];

    const traverse = (navItems: PageNavigation[]) => {
        for (const item of navItems) {
            result.push({ title: item.title, slug: item.slug });
            if (item.children) {
                traverse(item.children);
            }
        }
    };

    traverse(items);
    return result;
};

interface PagesSlugProps {
    page: Page;
    navigation: PageNavigation[];
}

export default function PagesSlug({ page, navigation }: PagesSlugProps) {
    const breadcrumbs: BreadcrumbItem[] = useMemo(() => {
        const flatNav = flattenNavigation(navigation);
        const currentIndex = flatNav.findIndex((item) => item.slug === page.slug);

        const items: BreadcrumbItem[] = [
            { title: 'Beranda', href: '/' },
            { title: 'Halaman', href: '/pages' },
        ];

        if (currentIndex >= 0) {
            for (let i = 0; i <= currentIndex && i < flatNav.length; i++) {
                items.push({
                    title: flatNav[i].title,
                    href: `/pages/${flatNav[i].slug}`,
                });
            }
        }

        return items;
    }, [page.slug, navigation]);

    const renderSidebar = () => (
        <nav className="space-y-1">
            {navigation.map((section) => (
                <div key={section.id} className="mb-4">
                    <h3 className="mb-2 text-sm font-semibold uppercase tracking-wider text-muted-foreground">
                        {section.title}
                    </h3>
                    <div className="space-y-1">
                        {section.children?.map((child) => (
                            <Link
                                key={child.id}
                                href={`/pages/${child.slug}`}
                                className={`flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors ${
                                    child.slug === page.slug
                                        ? 'bg-primary text-primary-foreground'
                                        : 'hover:bg-muted'
                                }`}
                            >
                                <ChevronRight className="h-3 w-3" />
                                {child.title}
                            </Link>
                        ))}
                    </div>
                </div>
            ))}
        </nav>
    );

    const renderContent = () => (
        <div className="prose prose-sm max-w-none dark:prose-invert">
            <h1 className="text-3xl font-bold tracking-tight md:text-4xl">{page.title}</h1>

            {page.excerpt && (
                <p className="lead text-xl text-muted-foreground">{page.excerpt}</p>
            )}

            <Separator className="my-6" />

            <div
                className="cms-content"
                dangerouslySetInnerHTML={{ __html: sanitizeHtml(page.content) }}
            />

            {page.tags.length > 0 && (
                <>
                    <Separator className="my-6" />
                    <div className="flex flex-wrap items-center gap-2">
                        <span className="text-sm text-muted-foreground">Tags:</span>
                        {page.tags.map((tag) => (
                            <Badge key={tag} variant="secondary">
                                {tag}
                            </Badge>
                        ))}
                    </div>
                </>
            )}

            <div className="mt-8 flex items-center justify-between text-sm text-muted-foreground">
                <span>Dipublikasikan: {new Date(page.published_at).toLocaleDateString('id-ID')}</span>
                <span>Diperbarui: {new Date(page.updated_at).toLocaleDateString('id-ID')}</span>
            </div>
        </div>
    );

    const renderTemplate = () => {
        switch (page.template) {
            case 'sidebar':
                return (
                    <div className="grid gap-8 lg:grid-cols-4">
                        <aside className="lg:col-span-1">
                            <Card>
                                <CardContent className="pt-6">{renderSidebar()}</CardContent>
                            </Card>
                        </aside>
                        <main className="lg:col-span-3">{renderContent()}</main>
                    </div>
                );

            case 'landing':
                return (
                    <div>
                        {page.featured_image && (
                            <div className="mb-8 h-64 w-full overflow-hidden rounded-xl bg-muted">
                                <img
                                    src={page.featured_image}
                                    alt={page.title}
                                    className="h-full w-full object-cover"
                                />
                            </div>
                        )}
                        {renderContent()}
                    </div>
                );

            case 'contact':
                return (
                    <div className="grid gap-8 lg:grid-cols-2">
                        <div>{renderContent()}</div>
                        <Card>
                            <CardContent className="pt-6">
                                <h3 className="mb-4 text-lg font-semibold">Hubungi Kami</h3>
                                <div className="space-y-4">
                                    <div>
                                        <p className="font-medium">Alamat</p>
                                        <p className="text-sm text-muted-foreground">
                                            Jl. Jend. Sudirman No. 1, Penajam, Kabupaten Penajam Paser
                                            Utara, Kalimantan Timur 76111
                                        </p>
                                    </div>
                                    <div>
                                        <p className="font-medium">Telepon</p>
                                        <p className="text-sm text-muted-foreground">
                                            (0542) 123456
                                        </p>
                                    </div>
                                    <div>
                                        <p className="font-medium">Email</p>
                                        <p className="text-sm text-muted-foreground">
                                            info@pa-penajam.go.id
                                        </p>
                                    </div>
                                    <div>
                                        <p className="font-medium">Jam Layanan</p>
                                        <p className="text-sm text-muted-foreground">
                                            Senin - Kamis: 08.00 - 16.30 WITA
                                            <br />
                                            Jumat: 08.00 - 17.00 WITA
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                );

            default:
                return renderContent();
        }
    };

    return (
        <MainLayout breadcrumbs={breadcrumbs}>
            <Head title={page.meta_title || page.title}>
                <meta name="description" content={page.meta_description} />
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700"
                    rel="stylesheet"
                />
            </Head>

            {/* Hero */}
            {page.template === 'landing' && (
                <section className="bg-gradient-to-b from-primary/5 to-background py-12 md:py-16">
                    <PageContainer>
                        <div className="text-center">
                            <h1 className="text-4xl font-bold tracking-tight text-foreground md:text-5xl">
                                {page.title}
                            </h1>
                            {page.excerpt && (
                                <p className="mt-4 text-lg text-muted-foreground">{page.excerpt}</p>
                            )}
                        </div>
                    </PageContainer>
                </section>
            )}

            {/* Content */}
            <section className="py-8">
                <PageContainer size={page.template === 'default' ? 'md' : 'xl'}>
                    {renderTemplate()}
                </PageContainer>
            </section>
        </MainLayout>
    );
}
