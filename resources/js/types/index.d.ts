import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface MenuItem {
    id: number;
    title: string;
    url: string;
    url_type: 'route' | 'page' | 'custom' | 'external';
    route_name?: string | null;
    page_id?: number | null;
    custom_url?: string | null;
    icon?: string;
    css_classes?: string | null;
    order: number;
    target_blank: boolean;
    is_active: boolean;
    conditions?: MenuCondition | null;
    parent_id?: number | null;
    menu_id: number;
    depth?: number;
    children?: MenuItem[];
    created_at?: string;
    updated_at?: string;
}

export interface MenuCondition {
    roles?: string[];
    auth_required?: boolean;
    custom?: Record<string, unknown>;
}

export interface Menu {
    id: number;
    name: string;
    location: MenuLocation;
    max_depth?: number | null;
    description?: string | null;
    items?: MenuItem[];
    created_at?: string;
    updated_at?: string;
}

export type MenuLocation = 'header' | 'footer' | 'sidebar' | 'mobile';

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    auth: Auth;
    sidebarOpen: boolean;
    menus: {
        header: MenuItem[];
        footer: MenuItem[];
        mobile: MenuItem[];
        [key: string]: MenuItem[];
    };
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}
