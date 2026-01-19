import React from 'react';
import { Head } from '@inertiajs/react';
import MenuBuilder from '@/components/menu-builder/MenuBuilder';
import { Menu, MenuItem } from '@/types';

interface EditorProps {
    menu: Menu;
    items: MenuItem[];
    pages: any[];
}

export default function Editor({ menu, items, pages }: EditorProps) {
    return (
        <div className="min-h-screen bg-background p-8">
            <Head title={`Menu Editor: ${menu.name}`} />

            <div className="mx-auto max-w-6xl">
                <MenuBuilder menu={menu} initialItems={items} pages={pages} />
            </div>
        </div>
    );
}
