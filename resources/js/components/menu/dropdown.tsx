import { Link } from '@inertiajs/react';
import { ExternalLink } from 'lucide-react';
import { HTMLAttributes } from 'react';

import { cn } from '@/lib/utils';
import { type MenuItem as MenuItemType } from '@/types';
import MenuItem from './menu-item';

interface DropdownProps extends HTMLAttributes<HTMLUListElement> {
    items: MenuItemType[];
    variant?: 'desktop' | 'mobile';
    onItemClick?: () => void;
}

export default function Dropdown({
    items,
    variant = 'desktop',
    onItemClick,
    className,
    ...props
}: DropdownProps) {
    if (!items || items.length === 0) {
        return null;
    }

    const baseClasses = cn(
        'min-w-[200px] rounded-md border bg-background py-2 shadow-lg',
        variant === 'desktop' &&
            'absolute left-0 top-full mt-1 hidden opacity-0 transition-all group-hover:visible group-hover:opacity-100 lg:invisible lg:absolute lg:translate-y-2 lg:group-hover:translate-y-0',
        variant === 'mobile' && 'relative ml-4 mt-1 border-l pl-4',
        className,
    );

    return (
        <ul className={baseClasses} {...props}>
            {items.map((item) => {
                const hasChildren = item.children && item.children.length > 0;
                const isActive = false; // TODO: Implement active state checking

                if (hasChildren) {
                    return (
                        <li key={item.id} className="relative group">
                            <button
                                className={cn(
                                    'flex w-full items-center justify-between rounded-md px-3 py-2 text-sm font-medium transition-colors',
                                    'hover:bg-accent hover:text-accent-foreground',
                                    isActive ? 'text-primary' : 'text-muted-foreground',
                                    item.css_classes,
                                )}
                                type="button"
                            >
                                <span className="flex items-center gap-2">
                                    {item.icon && (
                                        <span className={cn('h-4 w-4', item.icon)} aria-hidden="true" />
                                    )}
                                    {item.title}
                                </span>
                            </button>
                            <Dropdown items={item.children} variant={variant} onItemClick={onItemClick} />
                        </li>
                    );
                }

                const linkContent = (
                    <>
                        {item.icon && <span className={cn('h-4 w-4', item.icon)} aria-hidden="true" />}
                        <span>{item.title}</span>
                        {item.url_type === 'external' && (
                            <ExternalLink className="h-3 w-3 ml-auto" aria-hidden="true" />
                        )}
                    </>
                );

                return (
                    <li key={item.id} className="px-2">
                        {item.target_blank || item.url_type === 'external' ? (
                            <a
                                href={item.url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className={cn(
                                    'flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                                    'hover:bg-accent hover:text-accent-foreground',
                                    isActive ? 'text-primary' : 'text-muted-foreground',
                                    item.css_classes,
                                )}
                                onClick={onItemClick}
                            >
                                {linkContent}
                            </a>
                        ) : (
                            <Link
                                href={item.url}
                                className={cn(
                                    'flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                                    'hover:bg-accent hover:text-accent-foreground',
                                    isActive ? 'text-primary' : 'text-muted-foreground',
                                    item.css_classes,
                                )}
                                onClick={onItemClick}
                            >
                                {linkContent}
                            </Link>
                        )}
                    </li>
                );
            })}
        </ul>
    );
}
