import React, { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import * as z from 'zod';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { MenuItem } from '@/types';

const menuItemSchema = z.object({
    title: z.string().min(1, 'Title is required').max(100),
    url_type: z.enum(['route', 'page', 'custom', 'external']),
    url: z.string().min(1, 'URL or identifier is required'),
    icon: z.string().optional(),
    target_blank: z.boolean().default(false),
    is_active: z.boolean().default(true),
    parent_id: z.number().nullable().optional(),
});

type MenuItemFormValues = z.infer<typeof menuItemSchema>;

interface MenuItemFormProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    item: MenuItem | null;
    onSave: (data: MenuItemFormValues) => void;
    menuId: number;
    pages?: any[];
}

export default function MenuItemForm({
    open,
    onOpenChange,
    item,
    onSave,
    menuId,
    pages = [],
}: MenuItemFormProps) {
    const {
        register,
        handleSubmit,
        reset,
        setValue,
        watch,
        formState: { errors },
    } = useForm<MenuItemFormValues>({
        resolver: zodResolver(menuItemSchema),
        defaultValues: {
            title: '',
            url_type: 'custom',
            url: '',
            icon: '',
            target_blank: false,
            is_active: true,
            parent_id: null,
        },
    });

    const urlType = watch('url_type');
    const targetBlank = watch('target_blank');
    const isActive = watch('is_active');

    useEffect(() => {
        if (item) {
            let url = '';
            if (item.url_type === 'route') url = item.route_name || '';
            else if (item.url_type === 'page') url = String(item.page_id || '');
            else url = item.custom_url || '';

            reset({
                title: item.title,
                url_type: item.url_type,
                url: url,
                icon: item.icon || '',
                target_blank: item.target_blank,
                is_active: item.is_active,
                parent_id: item.parent_id,
            });
        } else {
            reset({
                title: '',
                url_type: 'custom',
                url: '',
                icon: '',
                target_blank: false,
                is_active: true,
                parent_id: null,
            });
        }
    }, [item, reset, open]);

    const onSubmit = (data: MenuItemFormValues) => {
        onSave(data);
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>{item ? 'Edit Menu Item' : 'Add Menu Item'}</DialogTitle>
                    <DialogDescription>
                        Fill in the details for the menu item.
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={handleSubmit(onSubmit)} className="space-y-4 py-4">
                    <div className="space-y-2">
                        <Label htmlFor="title">Title</Label>
                        <Input
                            id="title"
                            placeholder="Home"
                            {...register('title')}
                        />
                        {errors.title && <p className="text-xs text-destructive">{errors.title.message}</p>}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="url_type">Type</Label>
                            <Select
                                value={urlType}
                                onValueChange={(value: any) => {
                                    setValue('url_type', value);
                                    if (value !== 'page') setValue('url', '');
                                }}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="custom">Custom URL</SelectItem>
                                    <SelectItem value="route">Route Name</SelectItem>
                                    <SelectItem value="page">Internal Page</SelectItem>
                                    <SelectItem value="external">External Link</SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.url_type && <p className="text-xs text-destructive">{errors.url_type.message}</p>}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="url">
                                {urlType === 'page' ? 'Select Page' : 'URL / Value'}
                            </Label>
                            {urlType === 'page' ? (
                                <Select
                                    value={watch('url')}
                                    onValueChange={(value) => setValue('url', value)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a page" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {pages.map((page) => (
                                            <SelectItem key={page.id} value={String(page.id)}>
                                                {page.title}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            ) : (
                                <Input
                                    id="url"
                                    placeholder={urlType === 'route' ? 'home' : '/'}
                                    {...register('url')}
                                />
                            )}
                            {errors.url && <p className="text-xs text-destructive">{errors.url.message}</p>}
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="icon">Icon (Lucide name)</Label>
                        <Input
                            id="icon"
                            placeholder="home"
                            {...register('icon')}
                        />
                        <p className="text-[0.8rem] text-muted-foreground">
                            Optional icon name from Lucide library.
                        </p>
                        {errors.icon && <p className="text-xs text-destructive">{errors.icon.message}</p>}
                    </div>

                    <div className="flex flex-col gap-4 pt-2">
                        <div className="flex items-center space-x-2">
                            <Checkbox
                                id="target_blank"
                                checked={targetBlank}
                                onCheckedChange={(checked) => setValue('target_blank', checked === true)}
                            />
                            <Label htmlFor="target_blank" className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Open in new tab
                            </Label>
                        </div>

                        <div className="flex items-start space-x-2">
                            <Checkbox
                                id="is_active"
                                checked={isActive}
                                onCheckedChange={(checked) => setValue('is_active', checked === true)}
                            />
                            <div className="grid gap-1.5 leading-none">
                                <Label htmlFor="is_active" className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    Active
                                </Label>
                                <p className="text-xs text-muted-foreground">
                                    If disabled, this item won't be visible in the menu.
                                </p>
                            </div>
                        </div>
                    </div>

                    <DialogFooter className="pt-4">
                        <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                            Cancel
                        </Button>
                        <Button type="submit">
                            {item ? 'Update Item' : 'Add Item'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
