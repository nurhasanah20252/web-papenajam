import { type PropsWithChildren } from 'react';

import { Breadcrumbs } from '@/components/breadcrumbs';
import Footer from '@/components/footer';
import Header from '@/components/header';
import { type BreadcrumbItem } from '@/types';

interface MainLayoutProps extends PropsWithChildren {
    title?: string;
    breadcrumbs?: BreadcrumbItem[];
}

export default function MainLayout({
    children,
    title,
    breadcrumbs,
}: MainLayoutProps) {
    return (
        <div className="flex min-h-screen flex-col">
            <Header />
            {breadcrumbs && breadcrumbs.length > 1 && (
                <div className="border-b bg-muted/30">
                    <div className="mx-auto flex h-12 items-center px-4 md:max-w-7xl">
                        <Breadcrumbs breadcrumbs={breadcrumbs} />
                    </div>
                </div>
            )}
            <main className="flex-1">{children}</main>
            <Footer />
        </div>
    );
}
