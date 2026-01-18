import { type PropsWithChildren } from 'react';

import { cn } from '@/lib/utils';

interface PageHeaderProps extends PropsWithChildren {
    title: string;
    description?: string;
    className?: string;
    titleClassName?: string;
    descriptionClassName?: string;
}

export default function PageHeader({
    title,
    description,
    className,
    titleClassName,
    descriptionClassName,
    children,
}: PageHeaderProps) {
    return (
        <div className={cn('mb-8', className)}>
            <div className="flex flex-col gap-2 md:flex-row md:items-start md:justify-between md:gap-4">
                <div className="flex-1">
                    <h1
                        className={cn(
                            'text-3xl font-bold tracking-tight text-foreground md:text-4xl',
                            titleClassName,
                        )}
                    >
                        {title}
                    </h1>
                    {description && (
                        <p
                            className={cn(
                                'mt-2 text-lg text-muted-foreground',
                                descriptionClassName,
                            )}
                        >
                            {description}
                        </p>
                    )}
                </div>
                {children && (
                    <div className="flex items-center gap-2">{children}</div>
                )}
            </div>
        </div>
    );
}
