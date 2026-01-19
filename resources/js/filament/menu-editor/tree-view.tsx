import {
    DndContext,
    DragEndEvent,
    DragOverlay,
    DragStartEvent,
    closestCenter,
    useSensor,
    useSensors,
    PointerSensor,
} from '@dnd-kit/core';
import {
    SortableContext,
    arrayMove,
    verticalListSortingStrategy,
    useSortable,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { ChevronDown, ChevronRight, GripVertical, Plus, Trash2, Edit } from 'lucide-react';
import { ReactNode, useState } from 'react';

import { cn } from '@/lib/utils';
import { type MenuItem as MenuItemType } from '@/types';

interface TreeViewProps {
    items: MenuItemType[];
    onReorder?: (items: MenuItemType[]) => void;
    onEdit?: (item: MenuItemType) => void;
    onDelete?: (item: MenuItemType) => void;
    onAddChild?: (parentId: number) => void;
    maxDepth?: number;
}

interface SortableItemProps {
    id: string | number;
    item: MenuItemType;
    depth: number;
    onEdit?: (item: MenuItemType) => void;
    onDelete?: (item: MenuItemType) => void;
    onAddChild?: (parentId: number) => void;
    isDragging?: boolean;
}

function SortableItem({
    id,
    item,
    depth,
    onEdit,
    onDelete,
    onAddChild,
    isDragging,
}: SortableItemProps) {
    const { attributes, listeners, setNodeRef, transform, transition, isDragging: isSortableDragging } =
        useSortable({ id });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
    };

    const [isExpanded, setIsExpanded] = useState(true);
    const hasChildren = item.children && item.children.length > 0;

    return (
        <div ref={setNodeRef} style={style} className={cn('relative', isSortableDragging && 'opacity-50')}>
            <div
                className={cn(
                    'flex items-center gap-2 rounded-md border bg-card p-3 shadow-sm',
                    'hover:bg-accent hover:text-accent-foreground',
                    !item.is_active && 'opacity-50',
                )}
                style={{ paddingLeft: `${depth * 24 + 12}px` }}
            >
                {/* Drag Handle */}
                <button
                    type="button"
                    className="cursor-grab active:cursor-grabbing text-muted-foreground hover:text-foreground"
                    {...attributes}
                    {...listeners}
                >
                    <GripVertical className="h-4 w-4" />
                </button>

                {/* Expand/Collapse */}
                {hasChildren ? (
                    <button
                        type="button"
                        onClick={() => setIsExpanded(!isExpanded)}
                        className="text-muted-foreground hover:text-foreground"
                    >
                        {isExpanded ? (
                            <ChevronDown className="h-4 w-4" />
                        ) : (
                            <ChevronRight className="h-4 w-4" />
                        )}
                    </button>
                ) : (
                    <div className="w-4" />
                )}

                {/* Item Info */}
                <div className="flex-1">
                    <div className="flex items-center gap-2">
                        {item.icon && <span className={cn('h-4 w-4', item.icon)} />}
                        <span className="font-medium">{item.title}</span>
                        {!item.is_active && (
                            <span className="rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground">
                                Hidden
                            </span>
                        )}
                        <span className="rounded-full bg-primary/10 px-2 py-0.5 text-xs text-primary">
                            {item.url_type}
                        </span>
                    </div>
                    <div className="text-xs text-muted-foreground mt-1">{item.url}</div>
                </div>

                {/* Actions */}
                <div className="flex items-center gap-1">
                    {hasChildren && (
                        <button
                            type="button"
                            onClick={() => onAddChild?.(item.id)}
                            className="rounded p-1.5 hover:bg-accent"
                            title="Add child item"
                        >
                            <Plus className="h-4 w-4" />
                        </button>
                    )}
                    <button
                        type="button"
                        onClick={() => onEdit?.(item)}
                        className="rounded p-1.5 hover:bg-accent"
                        title="Edit item"
                    >
                        <Edit className="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        onClick={() => onDelete?.(item)}
                        className="rounded p-1.5 hover:bg-destructive hover:text-destructive-foreground"
                        title="Delete item"
                    >
                        <Trash2 className="h-4 w-4" />
                    </button>
                </div>
            </div>

            {/* Children */}
            {hasChildren && isExpanded && (
                <div className="mt-1">
                    {item.children?.map((child) => (
                        <SortableItem
                            key={child.id}
                            id={child.id}
                            item={child}
                            depth={depth + 1}
                            onEdit={onEdit}
                            onDelete={onDelete}
                            onAddChild={onAddChild}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}

export default function TreeView({
    items,
    onReorder,
    onEdit,
    onDelete,
    onAddChild,
    maxDepth = 3,
}: TreeViewProps) {
    const [activeId, setActiveId] = useState<string | number | null>(null);
    const [itemsState, setItemsState] = useState(items);

    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: {
                distance: 8,
            },
        }),
    );

    const handleDragStart = (event: DragStartEvent) => {
        setActiveId(event.active.id);
    };

    const handleDragEnd = (event: DragEndEvent) => {
        const { active, over } = event;

        if (over && active.id !== over.id) {
            setItemsState((items) => {
                const oldIndex = items.findIndex((item) => item.id === active.id);
                const newIndex = items.findIndex((item) => item.id === over.id);

                const newItems = arrayMove(items, oldIndex, newIndex);
                onReorder?.(newItems);
                return newItems;
            });
        }

        setActiveId(null);
    };

    return (
        <DndContext
            sensors={sensors}
            collisionDetection={closestCenter}
            onDragStart={handleDragStart}
            onDragEnd={handleDragEnd}
        >
            <SortableContext items={itemsState.map((item) => item.id)} strategy={verticalListSortingStrategy}>
                <div className="space-y-2">
                    {itemsState.map((item) => (
                        <SortableItem
                            key={item.id}
                            id={item.id}
                            item={item}
                            depth={0}
                            onEdit={onEdit}
                            onDelete={onDelete}
                            onAddChild={onAddChild}
                        />
                    ))}
                </div>
            </SortableContext>

            <DragOverlay>
                {activeId ? (
                    <div className="flex items-center gap-2 rounded-md border bg-card p-3 shadow-lg">
                        <GripVertical className="h-4 w-4 text-muted-foreground" />
                        <span>Dragging item...</span>
                    </div>
                ) : null}
            </DragOverlay>
        </DndContext>
    );
}
