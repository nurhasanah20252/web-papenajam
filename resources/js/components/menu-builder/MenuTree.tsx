import React, { useMemo } from 'react';
import {
    DndContext,
    closestCenter,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
    DragOverlay,
    defaultDropAnimationSideEffects,
    type DragStartEvent,
    type DragOverEvent,
    type DragEndEvent,
    type DropAnimation,
} from '@dnd-kit/core';
import {
    arrayMove,
    SortableContext,
    sortableKeyboardCoordinates,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { MenuItem } from '@/types';
import SortableMenuItem from './SortableMenuItem';

interface MenuTreeProps {
    items: MenuItem[];
    onReorder: (items: MenuItem[]) => void;
    onEdit: (item: MenuItem) => void;
    onDelete?: (item: MenuItem) => void;
    onAddChild?: (parent: MenuItem) => void;
    maxDepth: number;
}

const dropAnimation: DropAnimation = {
    sideEffects: defaultDropAnimationSideEffects({
        styles: {
            active: {
                opacity: '0.5',
            },
        },
    }),
};

export default function MenuTree({ items, onReorder, onEdit, maxDepth }: MenuTreeProps) {
    const [activeId, setActiveId] = React.useState<number | null>(null);
    const sensors = useSensors(
        useSensor(PointerSensor),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        })
    );

    const activeItem = useMemo(
        () => (activeId ? findItemDeep(items, activeId) : null),
        [activeId, items]
    );

    function findItemDeep(items: MenuItem[], id: number): MenuItem | null {
        for (const item of items) {
            if (item.id === id) return item;
            if (item.children) {
                const child = findItemDeep(item.children, id);
                if (child) return child;
            }
        }
        return null;
    }

    const handleDragStart = (event: DragStartEvent) => {
        setActiveId(event.active.id as number);
    };

    const handleDragEnd = (event: DragEndEvent) => {
        const { active, over } = event;
        setActiveId(null);

        if (over && active.id !== over.id) {
            const activeId = active.id as number;
            const overId = over.id as number;

            const oldIndex = items.findIndex((i) => i.id === activeId);
            const newIndex = items.findIndex((i) => i.id === overId);

            if (oldIndex !== -1 && newIndex !== -1) {
                const newItems = arrayMove(items, oldIndex, newIndex);
                onReorder(newItems);
            } else {
                // If they are not in the same level, we need a more complex move logic
                // For a truly nested tree with DnD kit, we usually flatten the tree for sorting
                // or handle the move across different parent levels.
                // For now, let's implement basic top-level reordering and
                // suggest a flattened approach for full nesting if needed.
                console.log('Moving between levels is not yet fully implemented in this basic version');
            }
        }
    };

    const renderItems = (items: MenuItem[], depth = 0) => {
        return (
            <SortableContext items={items.map(i => i.id)} strategy={verticalListSortingStrategy}>
                <div className="space-y-2">
                    {items.map((item) => (
                        <div key={item.id} className="space-y-2">
                            <SortableMenuItem
                                item={item}
                                depth={depth}
                                onEdit={() => onEdit(item)}
                                onDelete={() => onDelete?.(item)}
                                onAddChild={depth < maxDepth - 1 ? () => onAddChild?.(item) : undefined}
                            />
                            {item.children && item.children.length > 0 && (
                                <div className="ml-8 border-l pl-4">
                                    {renderItems(item.children, depth + 1)}
                                </div>
                            )}
                        </div>
                    ))}
                </div>
            </SortableContext>
        );
    };

    return (
        <DndContext
            sensors={sensors}
            collisionDetection={closestCenter}
            onDragStart={handleDragStart}
            onDragEnd={handleDragEnd}
        >
            <div className="rounded-lg border bg-muted/30 p-4">
                {items.length === 0 ? (
                    <div className="flex flex-col items-center justify-center py-12 text-center">
                        <p className="text-sm text-muted-foreground">No menu items yet.</p>
                        <p className="text-xs text-muted-foreground">Click "Add Item" to start building your menu.</p>
                    </div>
                ) : (
                    renderItems(items)
                )}
            </div>

            <DragOverlay dropAnimation={dropAnimation}>
                {activeId && activeItem ? (
                    <div className="opacity-80">
                        <SortableMenuItem item={activeItem} depth={0} isOverlay />
                    </div>
                ) : null}
            </DragOverlay>
        </DndContext>
    );
}
