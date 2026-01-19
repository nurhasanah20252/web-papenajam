import React, { useState } from 'react';
import { Menu, MenuItem } from '@/types';
import MenuTree from './MenuTree';
import MenuItemForm from './MenuItemForm';
import { Button } from '@/components/ui/button';
import { Plus, Save } from 'lucide-react';
import { router } from '@inertiajs/react';
import axios from 'axios';

interface MenuBuilderProps {
    menu: Menu;
    initialItems: MenuItem[];
    pages?: any[];
}

export default function MenuBuilder({ menu, initialItems, pages = [] }: MenuBuilderProps) {
    const [items, setItems] = useState<MenuItem[]>(initialItems);
    const [editingItem, setEditingItem] = useState<MenuItem | null>(null);
    const [parentId, setParentId] = useState<number | null>(null);
    const [isFormOpen, setIsFormOpen] = useState(false);
    const [isSaving, setIsSaving] = useState(false);

    const handleAddItem = () => {
        setEditingItem(null);
        setParentId(null);
        setIsFormOpen(true);
    };

    const handleAddChild = (parent: MenuItem) => {
        setEditingItem(null);
        setParentId(parent.id);
        setIsFormOpen(true);
    };

    const handleEditItem = (item: MenuItem) => {
        setEditingItem(item);
        setParentId(item.parent_id || null);
        setIsFormOpen(true);
    };

    const handleSaveItem = async (formData: any) => {
        setIsSaving(true);

        // Map form data to backend expected fields
        const itemData: any = {
            title: formData.title,
            url_type: formData.url_type,
            icon: formData.icon,
            target_blank: formData.target_blank,
            is_active: formData.is_active,
            parent_id: formData.parent_id || parentId,
            order: editingItem ? editingItem.order : items.length,
        };

        // Handle URL mapping
        if (formData.url_type === 'route') {
            itemData.route_name = formData.url;
        } else if (formData.url_type === 'page') {
            itemData.page_id = parseInt(formData.url);
        } else {
            itemData.custom_url = formData.url;
        }

        try {
            if (editingItem) {
                await axios.put(route('admin.menus.items.update', { menu: menu.id, item: editingItem.id }), itemData);
            } else {
                await axios.post(route('admin.menus.items.store', { menu: menu.id }), itemData);
            }
            router.reload({ only: ['items'] });
            setIsFormOpen(false);
            setParentId(null);
        } catch (error) {
            console.error('Failed to save item:', error);
            alert('Failed to save menu item. Please check your input.');
        } finally {
            setIsSaving(false);
        }
    };

    const handleDeleteItem = async (item: MenuItem) => {
        if (!confirm('Are you sure you want to delete this menu item and all its children?')) {
            return;
        }

        try {
            await axios.delete(route('admin.menus.items.destroy', { menu: menu.id, item: item.id }));
            router.reload({ only: ['items'] });
        } catch (error) {
            console.error('Failed to delete item:', error);
            alert('Failed to delete menu item.');
        }
    };

    const handleReorder = async (newItems: MenuItem[]) => {
        setItems(newItems);

        // Flatten for reorder API
        const flattened: any[] = [];
        const process = (list: MenuItem[], parentId: number | null = null) => {
            list.forEach((item, index) => {
                flattened.push({
                    id: item.id,
                    order: index,
                    parent_id: parentId,
                });
                if (item.children && item.children.length > 0) {
                    process(item.children, item.id);
                }
            });
        };
        process(newItems);

        try {
            await axios.put(route('admin.menus.update-structure', { menu: menu.id }), {
                items: flattened,
            });
        } catch (error) {
            console.error('Failed to update structure:', error);
            // Revert on failure?
        }
    };

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <div>
                    <h2 className="text-2xl font-bold tracking-tight">{menu.name}</h2>
                    <p className="text-muted-foreground">
                        Manage menu items and their structure for {menu.location}.
                    </p>
                </div>
                <div className="flex gap-2">
                    <Button variant="outline" onClick={() => router.get(route('filament.admin.resources.menus.index'))}>
                        Back
                    </Button>
                    <Button onClick={handleAddItem}>
                        <Plus className="mr-2 h-4 w-4" />
                        Add Item
                    </Button>
                </div>
            </div>

            <div className="grid gap-6 md:grid-cols-12">
                <div className="md:col-span-8">
                    <MenuTree
                        items={items}
                        onReorder={handleReorder}
                        onEdit={handleEditItem}
                        onAddChild={handleAddChild}
                        onDelete={handleDeleteItem}
                        maxDepth={menu.max_depth || 3}
                    />
                </div>
                <div className="md:col-span-4">
                    <div className="rounded-lg border bg-card p-6 shadow-sm">
                        <h3 className="mb-4 text-lg font-medium">Menu Settings</h3>
                        <div className="space-y-4">
                            <div>
                                <label className="text-sm font-medium text-muted-foreground">Main Location</label>
                                <p className="text-sm font-medium capitalize">{menu.location}</p>
                            </div>
                            {menu.locations && menu.locations.length > 0 && (
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Additional Locations</label>
                                    <div className="mt-1 flex flex-wrap gap-1">
                                        {menu.locations.map(loc => (
                                            <span key={loc} className="inline-flex items-center rounded-full bg-secondary px-2.5 py-0.5 text-xs font-semibold capitalize">
                                                {loc}
                                            </span>
                                        ))}
                                    </div>
                                </div>
                            )}
                            <div>
                                <label className="text-sm font-medium text-muted-foreground">Max Depth</label>
                                <p className="text-sm font-medium">{menu.max_depth || 'Unlimited'}</p>
                            </div>
                            <div className="pt-4">
                                <Button
                                    variant="secondary"
                                    className="w-full"
                                    onClick={() => router.get(route('filament.admin.resources.menus.edit', { record: menu.id }))}
                                >
                                    Edit Settings
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <MenuItemForm
                open={isFormOpen}
                onOpenChange={setIsFormOpen}
                item={editingItem}
                onSave={handleSaveItem}
                menuId={menu.id}
                pages={pages}
            />
        </div>
    );
}
