import { Link } from '@inertiajs/react';
import * as React from 'react';

import {
    NavigationMenu,
    NavigationMenuContent,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuTrigger,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import { useActiveUrl } from '@/hooks/use-active-url';
import { cn } from '@/lib/utils';
import { type MenuItem } from '@/types';

interface DesktopNavigationMenuProps {
    items: MenuItem[];
    className?: string;
}

export default function DesktopNavigationMenu({ items, className }: DesktopNavigationMenuProps) {
    const { urlIsActive } = useActiveUrl();

    return (
        <NavigationMenu className={className}>
            <NavigationMenuList>
                {items.map((item) => (
                    <NavigationMenuItem key={item.id}>
                        {item.children && item.children.length > 0 ? (
                            <>
                                <NavigationMenuTrigger
                                    className={cn(
                                        urlIsActive(item.url) && 'text-primary',
                                    )}
                                >
                                    {item.title}
                                </NavigationMenuTrigger>
                                <NavigationMenuContent>
                                    <ul className="grid w-[400px] gap-3 p-4 md:w-[500px] md:grid-cols-2 lg:w-[600px]">
                                        {item.children.map((child) => (
                                            <li key={child.id}>
                                                <NavigationMenuLink asChild>
                                                    <Link
                                                        href={child.url}
                                                        target={child.target_blank ? '_blank' : undefined}
                                                        className={cn(
                                                            'block select-none space-y-1 rounded-md p-3 leading-none no-underline outline-none transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground',
                                                            urlIsActive(child.url) && 'bg-accent/50 text-primary',
                                                        )}
                                                    >
                                                        <div className="text-sm font-medium leading-none">
                                                            {child.title}
                                                        </div>
                                                        {/* Optional description if added later */}
                                                    </Link>
                                                </NavigationMenuLink>
                                            </li>
                                        ))}
                                    </ul>
                                </NavigationMenuContent>
                            </>
                        ) : (
                            <Link
                                href={item.url}
                                target={item.target_blank ? '_blank' : undefined}
                                className={cn(
                                    navigationMenuTriggerStyle(),
                                    urlIsActive(item.url) && 'text-primary',
                                )}
                            >
                                {item.title}
                            </Link>
                        )}
                    </NavigationMenuItem>
                ))}
            </NavigationMenuList>
        </NavigationMenu>
    );
}
