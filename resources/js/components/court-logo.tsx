import { Link } from '@inertiajs/react';

import CourtLogoIcon from './court-logo-icon';

export default function CourtLogo() {
    return (
        <Link href="/" className="flex items-center gap-3">
            <CourtLogoIcon className="h-10 w-10 text-primary" />
            <div className="grid flex-1 text-left">
                <span className="truncate leading-tight font-semibold text-foreground">
                    Pengadilan Agama
                </span>
                <span className="truncate text-sm text-muted-foreground">
                    Penajam
                </span>
            </div>
        </Link>
    );
}
