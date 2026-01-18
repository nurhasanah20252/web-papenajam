import { Link, usePage } from '@inertiajs/react';
import {
    Menu,
    Search,
    X,
    ChevronDown,
    Phone,
    Mail,
    Clock,
} from 'lucide-react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { useActiveUrl } from '@/hooks/use-active-url';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';

import AppearanceToggleDropdown from './appearance-dropdown';
import CourtLogo from './court-logo';

interface NavItem {
    title: string;
    href: string;
    children?: { title: string; href: string }[];
}

const navItems: NavItem[] = [
    { title: 'Beranda', href: '/' },
    {
        title: 'Profil',
        href: '/about',
        children: [
            { title: 'Sejarah', href: '/about#sejarah' },
            { title: 'Visi & Misi', href: '/about#visi-misi' },
            { title: 'Struktur Organisasi', href: '/about#struktur' },
            { title: 'Kepaniteraan', href: '/about#kepaniteraan' },
            { title: 'Kesekretariatan', href: '/about#kesekretariatan' },
        ],
    },
    { title: 'Layanan', href: '/services' },
    { title: 'Berita', href: '/news' },
    { title: 'Pengumuman', href: '/announcements' },
    { title: 'Berkas Perkara', href: '/case-status' },
    { title: 'Kontak', href: '/contact' },
];

export default function Header() {
    const page = usePage<SharedData>();
    const { urlIsActive } = useActiveUrl();
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [openSubmenus, setOpenSubmenus] = useState<Record<string, boolean>>({});

    const toggleSubmenu = (key: string) => {
        setOpenSubmenus((prev) => ({
            ...prev,
            [key]: !prev[key],
        }));
    };

    return (
        <header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            {/* Top bar with contact info */}
            <div className="hidden bg-primary text-primary-foreground py-2 px-4 md:block">
                <div className="mx-auto flex max-w-7xl items-center justify-between text-sm">
                    <div className="flex items-center gap-6">
                        <span className="flex items-center gap-2">
                            <Clock className="h-4 w-4" />
                            <span>Senin - Kamis: 07:30 - 16:00, Jumat: 07:30 - 11:30</span>
                        </span>
                    </div>
                    <div className="flex items-center gap-4">
                        <span className="flex items-center gap-2">
                            <Phone className="h-4 w-4" />
                            <span>(0541) 2901234</span>
                        </span>
                        <span className="flex items-center gap-2">
                            <Mail className="h-4 w-4" />
                            <span>pa.penajam@gmail.com</span>
                        </span>
                    </div>
                </div>
            </div>

            {/* Main header */}
            <div className="mx-auto flex h-16 items-center justify-between px-4 md:max-w-7xl">
                {/* Logo */}
                <div className="flex items-center">
                    <CourtLogo />
                </div>

                {/* Desktop Navigation */}
                <nav className="hidden lg:flex lg:items-center lg:gap-1">
                    {navItems.map((item) => (
                        <div key={item.href} className="relative group">
                            {item.children ? (
                                <button
                                    className={cn(
                                        'flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground',
                                        urlIsActive(item.href)
                                            ? 'text-primary'
                                            : 'text-muted-foreground',
                                    )}
                                >
                                    {item.title}
                                    <ChevronDown className="h-4 w-4 transition-transform group-hover:rotate-180" />
                                </button>
                            ) : (
                                <Link
                                    href={item.href}
                                    className={cn(
                                        'rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground',
                                        urlIsActive(item.href)
                                            ? 'text-primary'
                                            : 'text-muted-foreground',
                                    )}
                                >
                                    {item.title}
                                </Link>
                            )}

                            {/* Dropdown for desktop */}
                            {item.children && (
                                <div className="absolute left-0 top-full mt-1 hidden min-w-[200px] rounded-md border bg-background py-2 shadow-lg opacity-0 transition-all group-hover:visible group-hover:opacity-100 lg:invisible lg:absolute lg:translate-y-2 lg:group-hover:translate-y-0">
                                    {item.children.map((child) => (
                                        <Link
                                            key={child.href}
                                            href={child.href}
                                            className="block px-4 py-2 text-sm text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                                        >
                                            {child.title}
                                        </Link>
                                    ))}
                                </div>
                            )}
                        </div>
                    ))}
                </nav>

                {/* Right side actions */}
                <div className="flex items-center gap-2">
                    <Button
                        variant="ghost"
                        size="icon"
                        className="hidden h-9 w-9 rounded-md sm:flex"
                    >
                        <Search className="h-5 w-5" />
                        <span className="sr-only">Search</span>
                    </Button>
                    <AppearanceToggleDropdown className="hidden sm:flex" />
                    <Sheet open={mobileMenuOpen} onOpenChange={setMobileMenuOpen}>
                        <SheetTrigger asChild>
                            <Button
                                variant="ghost"
                                size="icon"
                                className="lg:hidden h-9 w-9 rounded-md"
                            >
                                {mobileMenuOpen ? (
                                    <X className="h-5 w-5" />
                                ) : (
                                    <Menu className="h-5 w-5" />
                                )}
                                <span className="sr-only">Toggle menu</span>
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="right" className="flex flex-col">
                            <SheetHeader className="flex flex-row items-center justify-between pb-4">
                                <SheetTitle className="sr-only">
                                    Navigation Menu
                                </SheetTitle>
                                <div className="flex items-center gap-2">
                                    <CourtLogo />
                                </div>
                            </SheetHeader>

                            {/* Mobile Navigation */}
                            <nav className="flex flex-1 flex-col gap-1 overflow-y-auto">
                                {navItems.map((item) => (
                                    <div key={item.href}>
                                        {item.children ? (
                                            <div>
                                                <button
                                                    onClick={() =>
                                                        toggleSubmenu(item.href)
                                                    }
                                                    className={cn(
                                                        'flex w-full items-center justify-between rounded-md px-3 py-2 text-sm font-medium transition-colors',
                                                        urlIsActive(item.href)
                                                            ? 'text-primary'
                                                            : 'text-muted-foreground',
                                                    )}
                                                >
                                                    {item.title}
                                                    <ChevronDown
                                                        className={cn(
                                                            'h-4 w-4 transition-transform',
                                                            openSubmenus[item.href] &&
                                                                'rotate-180',
                                                        )}
                                                    />
                                                </button>
                                                {openSubmenus[item.href] && (
                                                    <div className="ml-4 mt-1 flex flex-col border-l pl-4">
                                                        {item.children.map(
                                                            (child) => (
                                                                <Link
                                                                    key={
                                                                        child.href
                                                                    }
                                                                    href={
                                                                        child.href
                                                                    }
                                                                    className="rounded-md px-3 py-2 text-sm text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                                                                    onClick={() =>
                                                                        setMobileMenuOpen(
                                                                            false,
                                                                        )
                                                                    }
                                                                >
                                                                    {child.title}
                                                                </Link>
                                                            ),
                                                        )}
                                                    </div>
                                                )}
                                            </div>
                                        ) : (
                                            <Link
                                                href={item.href}
                                                className={cn(
                                                    'rounded-md px-3 py-2 text-sm font-medium transition-colors',
                                                    urlIsActive(item.href)
                                                        ? 'text-primary'
                                                        : 'text-muted-foreground',
                                                )}
                                                onClick={() =>
                                                    setMobileMenuOpen(false)
                                                }
                                            >
                                                {item.title}
                                            </Link>
                                        )}
                                    </div>
                                ))}
                            </nav>

                            {/* Mobile footer with contact info */}
                            <div className="border-t pt-4">
                                <div className="space-y-2 text-sm text-muted-foreground">
                                    <div className="flex items-center gap-2">
                                        <Phone className="h-4 w-4" />
                                        <span>(0541) 2901234</span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <Mail className="h-4 w-4" />
                                        <span>pa.penajam@gmail.com</span>
                                    </div>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>
            </div>
        </header>
    );
}
