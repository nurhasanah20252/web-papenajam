import { Link } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';
import * as React from 'react';
import { useState } from 'react';

import { useActiveUrl } from '@/hooks/use-active-url';
import { cn } from '@/lib/utils';
import { type MenuItem } from '@/types';

interface MobileMenuProps {
    items: MenuItem[];
    onItemClick?: () => void;
    className?: string;
}

export default function MobileMenu({ items, onItemClick, className }: MobileMenuProps) {
    const { urlIsActive } = useActiveUrl();
    const [openSubmenus, setOpenSubmenus] = useState<Record<number, boolean>>({});

    const toggleSubmenu = (id: number) => {
        setOpenSubmenus((prev) => ({
            ...prev,
            [id]: !prev[id],
        }));
    };

    const renderMenuItem = (item: MenuItem) => {
        const hasChildren = item.children && item.children.length > 0;
        const isActive = urlIsActive(item.url);

        if (hasChildren) {
            return (
                <div key={item.id} className="space-y-1">
                    <button
                        onClick={() => toggleSubmenu(item.id)}
                        className={cn(
                            'flex w-full items-center justify-between rounded-md px-3 py-2 text-base font-medium transition-colors hover:bg-accent hover:text-accent-foreground',
                            isActive ? 'text-primary' : 'text-muted-foreground',
                        )}
                    >
                        {item.title}
                        <ChevronDown
                            className={cn(
                                'h-4 w-4 transition-transform duration-200',
                                openSubmenus[item.id] && 'rotate-180',
                            )}
                        />
                    </button>
                    {openSubmenus[item.id] && (
                        <div className="ml-4 space-y-1 border-l pl-4">
                            {item.children?.map((child) => (
                                <Link
                                    key={child.id}
                                    href={child.url}
                                    target={child.target_blank ? '_blank' : undefined}
                                    className={cn(
                                        'block rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground',
                                        urlIsActive(child.url) ? 'text-primary' : 'text-muted-foreground',
                                    )}
                                    onClick={onItemClick}
                                >
                                    {child.title}
                                </Link>
                            ))}
                        </div>
                    )}
                </div>
            );
        }

        return (
            <Link
                key={item.id}
                href={item.url}
                target={item.target_blank ? '_blank' : undefined}
                className={cn(
                    'block rounded-md px-3 py-2 text-base font-medium transition-colors hover:bg-accent hover:text-accent-foreground',
                    isActive ? 'text-primary' : 'text-muted-foreground',
                )}
                onClick={onItemClick}
            >
                {item.title}
            </Link>
        );
    };

    return (
        <nav className={cn('flex flex-col gap-1', className)}>
            {items.map(renderMenuItem)}
        </nav>
    );
}
