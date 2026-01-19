import { useMemo } from 'react';
import { usePage } from '@inertiajs/react';

import { type MenuLocation, type MenuItem, type MenuCondition, type SharedData } from '@/types';

export function useMenu(location?: MenuLocation) {
    const page = usePage<SharedData>();
    const menus = page.props.menus || {};

    /**
     * Get menu items by location
     */
    const getMenuByLocation = (menuLocation: MenuLocation): MenuItem[] => {
        return menus[menuLocation] || [];
    };

    /**
     * Check if menu item should be visible based on conditions
     */
    const isMenuItemVisible = (item: MenuItem): boolean => {
        if (!item.is_active) {
            return false;
        }

        if (!item.conditions) {
            return true;
        }

        const { auth_required, roles, custom } = item.conditions;

        // Check auth requirement
        if (auth_required === true && !page.props.auth.user) {
            return false;
        }

        if (auth_required === false && page.props.auth.user) {
            return false;
        }

        // Check roles
        if (roles && roles.length > 0 && page.props.auth.user) {
            const userRoles = page.props.auth.user.roles || [];
            const hasRequiredRole = roles.some((role) => userRoles.includes(role));
            if (!hasRequiredRole) {
                return false;
            }
        }

        // Custom conditions can be evaluated here
        // This is a placeholder for more complex logic

        return true;
    };

    /**
     * Filter menu items based on visibility conditions
     */
    const filterMenuItems = (items: MenuItem[]): MenuItem[] => {
        return items
            .filter((item) => isMenuItemVisible(item))
            .map((item) => ({
                ...item,
                children: item.children ? filterMenuItems(item.children) : [],
            }))
            .filter((item) => !item.children || item.children.length > 0 || !item.children);
    };

    /**
     * Get filtered menu items for a specific location
     */
    const menuItems = useMemo(() => {
        if (!location) {
            return menus;
        }

        const items = getMenuByLocation(location);
        return filterMenuItems(items);
    }, [location, menus]);

    /**
     * Get active menu item based on current URL
     */
    const getActiveMenuItem = (items: MenuItem[]): MenuItem | null => {
        const currentPath = window.location.pathname;

        for (const item of items) {
            if (item.url === currentPath) {
                return item;
            }

            if (item.children && item.children.length > 0) {
                const activeChild = getActiveMenuItem(item.children);
                if (activeChild) {
                    return activeChild;
                }
            }
        }

        return null;
    };

    const activeItem = useMemo(() => {
        const items = location ? getMenuByLocation(location) : Object.values(menus).flat();
        return getActiveMenuItem(items);
    }, [location, menus]);

    /**
     * Get breadcrumbs from menu structure
     */
    const getBreadcrumbs = (items: MenuItem[], targetUrl: string): MenuItem[] => {
        const currentPath = window.location.pathname;

        for (const item of items) {
            if (item.url === currentPath) {
                return [item];
            }

            if (item.children && item.children.length > 0) {
                const childBreadcrumbs = getBreadcrumbs(item.children, targetUrl);
                if (childBreadcrumbs.length > 0) {
                    return [item, ...childBreadcrumbs];
                }
            }
        }

        return [];
    };

    const breadcrumbs = useMemo(() => {
        if (!location) return [];
        const items = getMenuByLocation(location);
        return getBreadcrumbs(items, window.location.pathname);
    }, [location, menus]);

    return {
        menuItems,
        activeItem,
        breadcrumbs,
        getMenuByLocation,
        isMenuItemVisible,
        menus,
    };
}
