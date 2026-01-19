import { useState } from 'react';

import { useMenu } from '@/hooks/use-menu';
import { cn } from '@/lib/utils';
import { type MenuLocation } from '@/types';
import Dropdown from './menu/dropdown';
import MenuItem from './menu/menu-item';
import MobileMenu from './menu/mobile-menu';

interface MenuProps {
    location: MenuLocation;
    className?: string;
    variant?: 'desktop' | 'mobile';
    orientation?: 'horizontal' | 'vertical';
    onLinkClick?: () => void;
    isMobileMenuOpen?: boolean;
    onMobileMenuClose?: () => void;
}

export default function Menu({
    location,
    className,
    variant = 'desktop',
    orientation = 'horizontal',
    onLinkClick,
    isMobileMenuOpen = false,
    onMobileMenuClose,
}: MenuProps) {
    const { menuItems } = useMenu(location);
    const [openSubmenus, setOpenSubmenus] = useState<Record<string, boolean>>({});

    const toggleSubmenu = (key: string) => {
        setOpenSubmenus((prev) => ({
            ...prev,
            [key]: !prev[key],
        }));
    };

    // Handle mobile menu
    if (variant === 'mobile') {
        return (
            <MobileMenu
                isOpen={isMobileMenuOpen}
                onClose={onMobileMenuClose || (() => {})}
                items={menuItems}
                location={location}
                className={className}
            />
        );
    }

    const navClasses = cn(
        'flex',
        orientation === 'horizontal' ? 'flex-row items-center gap-1' : 'flex-col gap-1',
        className,
    );

    const renderMenuItem = (item: any, depth: number = 0) => {
        const hasChildren = item.children && item.children.length > 0;
        const submenuKey = `${item.id}-${item.url}`;

        if (variant === 'desktop' && hasChildren) {
            return (
                <li key={item.id} className="relative group">
                    <MenuItem item={item} variant="desktop" />
                    <Dropdown items={item.children} variant="desktop" onItemClick={onLinkClick} />
                </li>
            );
        }

        if (variant === 'mobile' && hasChildren) {
            return (
                <li key={item.id}>
                    <MenuItem
                        item={item}
                        variant="mobile"
                        onLinkClick={() => toggleSubmenu(submenuKey)}
                    />
                    {openSubmenus[submenuKey] && (
                        <Dropdown items={item.children} variant="mobile" onItemClick={onLinkClick} />
                    )}
                </li>
            );
        }

        return (
            <li key={item.id}>
                <MenuItem item={item} variant={variant} onLinkClick={onLinkClick} />
            </li>
        );
    };

    return (
        <nav className={navClasses} aria-label={`${location} menu`}>
            <ul className={cn('flex', orientation === 'horizontal' ? 'flex-row items-center gap-1' : 'flex-col gap-1')}>
                {menuItems.map((item) => renderMenuItem(item))}
            </ul>
        </nav>
    );
}

// Export sub-components for direct use if needed
export { MenuItem, Dropdown, MobileMenu };
