import { X } from 'lucide-react';
import { useEffect } from 'react';

import { cn } from '@/lib/utils';
import { type MenuItem as MenuItemType, type MenuLocation } from '@/types';
import Dropdown from './dropdown';
import MenuItem from './menu-item';

interface MobileMenuProps {
    isOpen: boolean;
    onClose: () => void;
    items: MenuItemType[];
    location?: MenuLocation;
    className?: string;
}

export default function MobileMenu({
    isOpen,
    onClose,
    items,
    location,
    className,
}: MobileMenuProps) {
    // Prevent body scroll when menu is open
    useEffect(() => {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }

        return () => {
            document.body.style.overflow = '';
        };
    }, [isOpen]);

    // Handle escape key
    useEffect(() => {
        const handleEscape = (e: KeyboardEvent) => {
            if (e.key === 'Escape' && isOpen) {
                onClose();
            }
        };

        document.addEventListener('keydown', handleEscape);
        return () => document.removeEventListener('keydown', handleEscape);
    }, [isOpen, onClose]);

    if (!isOpen) {
        return null;
    }

    const renderMenuItem = (item: MenuItemType, depth: number = 0) => {
        const hasChildren = item.children && item.children.length > 0;
        const isActive = false; // TODO: Implement active state checking

        const linkClasses = cn(
            'flex w-full items-center gap-3 rounded-md px-4 py-3 text-base font-medium transition-colors',
            'hover:bg-accent hover:text-accent-foreground',
            isActive ? 'text-primary bg-accent/50' : 'text-muted-foreground',
            depth > 0 && 'ml-4 text-sm',
            item.css_classes,
        );

        if (hasChildren) {
            return (
                <li key={item.id} className="flex flex-col">
                    <button
                        className={cn(
                            'flex w-full items-center justify-between rounded-md px-4 py-3 text-base font-medium transition-colors',
                            'hover:bg-accent hover:text-accent-foreground',
                            isActive ? 'text-primary' : 'text-muted-foreground',
                        )}
                        onClick={() => {
                            // Toggle submenu - implement state management
                            const submenu = document.getElementById(`submenu-${item.id}`);
                            if (submenu) {
                                submenu.classList.toggle('hidden');
                            }
                        }}
                        type="button"
                    >
                        <span className="flex items-center gap-3">
                            {item.icon && (
                                <span className={cn('h-5 w-5', item.icon)} aria-hidden="true" />
                            )}
                            {item.title}
                        </span>
                        <svg
                            className="h-4 w-4 transition-transform"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M19 9l-7 7-7-7"
                            />
                        </svg>
                    </button>
                    <ul id={`submenu-${item.id}`} className="hidden ml-4 mt-1 flex flex-col">
                        {item.children?.map((child) => renderMenuItem(child, depth + 1))}
                    </ul>
                </li>
            );
        }

        const linkContent = (
            <>
                {item.icon && <span className={cn('h-5 w-5', item.icon)} aria-hidden="true" />}
                <span>{item.title}</span>
            </>
        );

        return (
            <li key={item.id}>
                {item.target_blank || item.url_type === 'external' ? (
                    <a
                        href={item.url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className={linkClasses}
                        onClick={onClose}
                    >
                        {linkContent}
                    </a>
                ) : (
                    <a href={item.url} className={linkClasses} onClick={onClose}>
                        {linkContent}
                    </a>
                )}
            </li>
        );
    };

    return (
        <>
            {/* Backdrop */}
            <div
                className={cn(
                    'fixed inset-0 z-50 bg-black/50 backdrop-blur-sm transition-opacity',
                    isOpen ? 'opacity-100' : 'opacity-0 pointer-events-none',
                )}
                onClick={onClose}
                aria-hidden="true"
            />

            {/* Menu Panel */}
            <div
                className={cn(
                    'fixed inset-y-0 right-0 z-50 w-full max-w-md bg-background shadow-xl transition-transform duration-300 ease-in-out',
                    isOpen ? 'translate-x-0' : 'translate-x-full',
                )}
            >
                <div className="flex h-full flex-col">
                    {/* Header */}
                    <div className="flex items-center justify-between border-b px-6 py-4">
                        <h2 className="text-lg font-semibold">Menu</h2>
                        <button
                            onClick={onClose}
                            className="rounded-md p-2 hover:bg-accent hover:text-accent-foreground"
                            type="button"
                            aria-label="Close menu"
                        >
                            <X className="h-5 w-5" />
                        </button>
                    </div>

                    {/* Menu Items */}
                    <nav className="flex-1 overflow-y-auto px-4 py-6" aria-label="Mobile navigation">
                        <ul className="flex flex-col gap-1">
                            {items.map((item) => renderMenuItem(item))}
                        </ul>
                    </nav>

                    {/* Footer */}
                    <div className="border-t px-6 py-4">
                        <p className="text-sm text-muted-foreground">
                            © {new Date().getFullYear()} PA Penajam
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}
