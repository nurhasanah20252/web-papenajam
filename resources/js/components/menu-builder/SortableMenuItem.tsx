import React from 'react';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { MenuItem } from '@/types';
import { Button } from '@/components/ui/button';
import { GripVertical, Pencil, Trash2, ChevronRight, ChevronDown, Plus } from 'lucide-react';
import { cn } from '@/lib/utils';

interface SortableMenuItemProps {
    item: MenuItem;
    depth: number;
    onEdit?: () => void;
    onDelete?: () => void;
    onAddChild?: () => void;
    isOverlay?: boolean;
}

export default function SortableMenuItem({
    item,
    depth,
    onEdit,
    onDelete,
    onAddChild,
    isOverlay
}: SortableMenuItemProps) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({ id: item.id });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
    };

    const hasChildren = item.children && item.children.length > 0;

    return (
        <div
            ref={setNodeRef}
            style={style}
            className={cn(
                "group relative rounded-md border bg-card p-3 shadow-sm transition-shadow hover:shadow-md",
                isDragging && "opacity-50",
                isOverlay && "cursor-grabbing shadow-lg ring-2 ring-primary"
            )}
        >
            <div className="flex items-center gap-3">
                <button
                    {...attributes}
                    {...listeners}
                    className="cursor-grab text-muted-foreground hover:text-foreground active:cursor-grabbing"
                >
                    <GripVertical className="h-4 w-4" />
                </button>

                <div className="flex flex-1 items-center gap-2 overflow-hidden">
                    {hasChildren && (
                        <ChevronDown className="h-4 w-4 shrink-0 text-muted-foreground" />
                    )}
                    {!hasChildren && depth > 0 && (
                        <div className="w-4 shrink-0" />
                    )}

                    <div className="flex flex-col truncate">
                        <span className="truncate font-medium text-sm">{item.title}</span>
                        <span className="truncate text-xs text-muted-foreground">{item.url}</span>
                    </div>
                </div>

                <div className="flex items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                    <Button
                        variant="ghost"
                        size="icon"
                        className="h-8 w-8"
                        title="Add child item"
                        onClick={(e) => {
                            e.stopPropagation();
                            onAddChild?.();
                        }}
                    >
                        <Plus className="h-3.5 w-3.5" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        className="h-8 w-8"
                        onClick={(e) => {
                            e.stopPropagation();
                            onEdit?.();
                        }}
                    >
                        <Pencil className="h-3.5 w-3.5" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        className="h-8 w-8 text-destructive hover:bg-destructive/10 hover:text-destructive"
                        onClick={(e) => {
                            e.stopPropagation();
                            onDelete?.();
                        }}
                    >
                        <Trash2 className="h-3.5 w-3.5" />
                    </Button>
                </div>
            </div>
        </div>
    );
}
