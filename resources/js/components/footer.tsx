import { Link } from '@inertiajs/react';
import {
    Phone,
    Mail,
    MapPin,
    Facebook,
    Instagram,
    Youtube,
    Clock,
} from 'lucide-react';

import { cn } from '@/lib/utils';

import CourtLogo from './court-logo';

const footerLinks = {
    layanan: [
        { title: 'Pendaftaran Perkara', href: '/services/registration' },
        { title: 'Pemeriksaan Perkara', href: '/services/hearing' },
        { title: 'Putusan', href: '/services/decision' },
        { title: 'Kasasi', href: '/services/appeal' },
    ],
    informasi: [
        { title: 'Berita Pengadilan', href: '/news' },
        { title: 'Pengumuman', href: '/announcements' },
        { title: 'Jadwal Sidang', href: '/court-schedule' },
        { title: 'Zona Integritas', href: '/integrity-zone' },
    ],
    link_terkait: [
        { title: 'MA-RI', href: 'https://mahkamahagung.go.id' },
        { title: 'PTA Balikpapan', href: 'https://pta-balikpapan.go.id' },
        { title: 'JDIH', href: 'https://jdih.mahkamahagung.go.id' },
        { title: 'SIPP', href: 'https://sipp.pn.penajam.net' },
    ],
};

export default function Footer() {
    const currentYear = new Date().getFullYear();

    return (
        <footer className="border-t bg-muted/50">
            <div className="mx-auto max-w-7xl px-4 py-12 md:py-16">
                <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-5">
                    {/* Brand and contact info */}
                    <div className="lg:col-span-2">
                        <CourtLogo />
                        <p className="mt-4 text-sm text-muted-foreground">
                            Pengadilan Agama Penajam siap memberikan layanan
                            hukum yang profesional, transparan, dan akuntabel
                            kepada masyarakat.
                        </p>
                        <div className="mt-6 space-y-3 text-sm">
                            <div className="flex items-start gap-3">
                                <MapPin className="mt-0.5 h-5 w-5 shrink-0 text-primary" />
                                <span>
                                    Jl. Provinsi KM. 8,5 No. 10, Penajam,
                                    Kalimantan Timur 76143
                                </span>
                            </div>
                            <div className="flex items-center gap-3">
                                <Phone className="h-5 w-5 shrink-0 text-primary" />
                                <span>(0541) 2901234</span>
                            </div>
                            <div className="flex items-center gap-3">
                                <Mail className="h-5 w-5 shrink-0 text-primary" />
                                <span>pa.penajam@gmail.com</span>
                            </div>
                            <div className="flex items-start gap-3">
                                <Clock className="mt-0.5 h-5 w-5 shrink-0 text-primary" />
                                <div>
                                    <div>Senin - Kamis: 07:30 - 16:00</div>
                                    <div>Jumat: 07:30 - 11:30</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Service links */}
                    <div>
                        <h3 className="mb-4 text-sm font-semibold uppercase tracking-wider text-foreground">
                            Layanan
                        </h3>
                        <ul className="space-y-3 text-sm">
                            {footerLinks.layanan.map((link) => (
                                <li key={link.href}>
                                    <Link
                                        href={link.href}
                                        className="text-muted-foreground transition-colors hover:text-primary"
                                    >
                                        {link.title}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </div>

                    {/* Information links */}
                    <div>
                        <h3 className="mb-4 text-sm font-semibold uppercase tracking-wider text-foreground">
                            Informasi
                        </h3>
                        <ul className="space-y-3 text-sm">
                            {footerLinks.informasi.map((link) => (
                                <li key={link.href}>
                                    <Link
                                        href={link.href}
                                        className="text-muted-foreground transition-colors hover:text-primary"
                                    >
                                        {link.title}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </div>

                    {/* Related links */}
                    <div>
                        <h3 className="mb-4 text-sm font-semibold uppercase tracking-wider text-foreground">
                            Link Terkait
                        </h3>
                        <ul className="space-y-3 text-sm">
                            {footerLinks.link_terkait.map((link) => (
                                <li key={link.href}>
                                    <a
                                        href={link.href}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-muted-foreground transition-colors hover:text-primary"
                                    >
                                        {link.title}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>

                {/* Bottom section */}
                <div className="mt-12 flex flex-col items-center justify-between gap-4 border-t pt-8 md:flex-row">
                    <p className="text-sm text-muted-foreground">
                        Hak Cipta {currentYear} Pengadilan Agama Penajam.
                        All rights reserved.
                    </p>
                    <div className="flex items-center gap-4">
                        <a
                            href="https://facebook.com"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-muted-foreground transition-colors hover:text-primary"
                            aria-label="Facebook"
                        >
                            <Facebook className="h-5 w-5" />
                        </a>
                        <a
                            href="https://instagram.com"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-muted-foreground transition-colors hover:text-primary"
                            aria-label="Instagram"
                        >
                            <Instagram className="h-5 w-5" />
                        </a>
                        <a
                            href="https://youtube.com"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-muted-foreground transition-colors hover:text-primary"
                            aria-label="Youtube"
                        >
                            <Youtube className="h-5 w-5" />
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    );
}
