import { Link } from '@inertiajs/react';
import { ChevronDown, ExternalLink } from 'lucide-react';
import { HTMLAttributes } from 'react';

import { cn } from '@/lib/utils';
import { type MenuItem as MenuItemType } from '@/types';

interface MenuItemProps extends HTMLAttributes<HTMLLIElement> {
    item: MenuItemType;
    variant?: 'desktop' | 'mobile';
    isActive?: boolean;
    onLinkClick?: () => void;
}

export default function MenuItem({
    item,
    variant = 'desktop',
    isActive = false,
    onLinkClick,
    className,
    ...props
}: MenuItemProps) {
    const hasChildren = item.children && item.children.length > 0;

    const baseClasses = cn(
        'flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors',
        'hover:bg-accent hover:text-accent-foreground',
        isActive ? 'text-primary bg-accent/50' : 'text-muted-foreground',
        item.css_classes,
        className,
    );

    const renderLink = () => {
        const linkContent = (
            <>
                {item.icon && <span className={cn('h-4 w-4', item.icon)} aria-hidden="true" />}
                <span>{item.title}</span>
                {item.url_type === 'external' && (
                    <ExternalLink className="h-3 w-3" aria-hidden="true" />
                )}
            </>
        );

        if (item.target_blank || item.url_type === 'external') {
            return (
                <a
                    href={item.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className={baseClasses}
                    onClick={onLinkClick}
                >
                    {linkContent}
                </a>
            );
        }

        return (
            <Link href={item.url} className={baseClasses} onClick={onLinkClick}>
                {linkContent}
            </Link>
        );
    };

    const renderButton = ({ onClick }: { onClick?: () => void }) => (
        <button
            className={cn(
                'flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                'hover:bg-accent hover:text-accent-foreground',
                isActive ? 'text-primary bg-accent/50' : 'text-muted-foreground',
                item.css_classes,
            )}
            onClick={onClick}
            type="button"
        >
            {item.icon && <span className={cn('h-4 w-4', item.icon)} aria-hidden="true" />}
            <span>{item.title}</span>
            <ChevronDown
                className={cn(
                    'h-4 w-4 transition-transform',
                    variant === 'desktop' && 'group-hover:rotate-180',
                )}
                aria-hidden="true"
            />
        </button>
    );

    return (
        <li className={cn('relative', hasChildren && 'group', className)} {...props}>
            {hasChildren ? renderButton({ onClick: onLinkClick }) : renderLink()}
        </li>
    );
}
