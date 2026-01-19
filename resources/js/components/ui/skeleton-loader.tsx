import { cn } from '@/lib/utils';

interface SkeletonLoaderProps {
    className?: string;
    count?: number;
}

export function CardSkeleton({ className }: SkeletonLoaderProps) {
    return (
        <div
            className={cn(
                'rounded-lg border bg-card text-card-foreground shadow-sm',
                className
            )}
        >
            <div className="p-6 space-y-4">
                <div className="h-2 w-1/3 animate-pulse rounded bg-muted" />
                <div className="space-y-2">
                    <div className="h-4 w-full animate-pulse rounded bg-muted" />
                    <div className="h-4 w-5/6 animate-pulse rounded bg-muted" />
                </div>
                <div className="h-8 w-24 animate-pulse rounded bg-muted" />
            </div>
        </div>
    );
}

export function NewsCardSkeleton({ className }: SkeletonLoaderProps) {
    return (
        <div
            className={cn(
                'rounded-lg border bg-card text-card-foreground shadow-sm overflow-hidden',
                className
            )}
        >
            <div className="h-2 w-full animate-pulse bg-primary" />
            <div className="p-6 space-y-4">
                <div className="flex items-center gap-2">
                    <div className="h-6 w-16 animate-pulse rounded-full bg-primary/10" />
                    <div className="h-4 w-24 animate-pulse rounded bg-muted" />
                </div>
                <div className="space-y-2">
                    <div className="h-5 w-full animate-pulse rounded bg-muted" />
                    <div className="h-4 w-5/6 animate-pulse rounded bg-muted" />
                    <div className="h-4 w-4/6 animate-pulse rounded bg-muted" />
                </div>
                <div className="h-8 w-32 animate-pulse rounded bg-muted" />
            </div>
        </div>
    );
}

export function TableSkeleton({ count = 5 }: SkeletonLoaderProps) {
    return (
        <div className="space-y-3">
            {Array.from({ length: count }).map((_, i) => (
                <div
                    key={i}
                    className="flex items-center space-x-4 p-4 border rounded-lg"
                >
                    <div className="h-12 w-12 animate-pulse rounded-full bg-muted" />
                    <div className="space-y-2 flex-1">
                        <div className="h-4 w-3/4 animate-pulse rounded bg-muted" />
                        <div className="h-3 w-1/2 animate-pulse rounded bg-muted" />
                    </div>
                    <div className="h-8 w-24 animate-pulse rounded bg-muted" />
                </div>
            ))}
        </div>
    );
}

export function HeroSkeleton({ className }: SkeletonLoaderProps) {
    return (
        <div
            className={cn(
                'rounded-lg bg-muted/50 p-8 md:p-12 space-y-4',
                className
            )}
        >
            <div className="space-y-4 max-w-2xl">
                <div className="h-12 w-3/4 animate-pulse rounded bg-muted" />
                <div className="space-y-2">
                    <div className="h-5 w-full animate-pulse rounded bg-muted" />
                    <div className="h-5 w-5/6 animate-pulse rounded bg-muted" />
                    <div className="h-5 w-4/6 animate-pulse rounded bg-muted" />
                </div>
                <div className="flex gap-4 pt-4">
                    <div className="h-10 w-32 animate-pulse rounded bg-muted" />
                    <div className="h-10 w-32 animate-pulse rounded bg-muted" />
                </div>
            </div>
        </div>
    );
}
