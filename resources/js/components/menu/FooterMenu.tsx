import { Link } from '@inertiajs/react';
import * as React from 'react';

import { cn } from '@/lib/utils';
import { type MenuItem } from '@/types';

interface FooterMenuProps {
    items: MenuItem[];
    title?: string;
    className?: string;
}

export default function FooterMenu({ items, title, className }: FooterMenuProps) {
    if (items.length === 0) {
        return null;
    }

    return (
        <div className={className}>
            {title && (
                <h3 className="mb-4 text-sm font-semibold uppercase tracking-wider text-foreground">
                    {title}
                </h3>
            )}
            <ul className="space-y-3 text-sm">
                {items.map((item) => (
                    <li key={item.id}>
                        {item.target_blank ? (
                            <a
                                href={item.url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="text-muted-foreground transition-colors hover:text-primary"
                            >
                                {item.title}
                            </a>
                        ) : (
                            <Link
                                href={item.url}
                                className="text-muted-foreground transition-colors hover:text-primary"
                            >
                                {item.title}
                            </Link>
                        )}
                    </li>
                ))}
            </ul>
        </div>
    );
}
