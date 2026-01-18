import { type PropsWithChildren } from 'react';

import { cn } from '@/lib/utils';

interface PageContainerProps extends PropsWithChildren {
    className?: string;
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'full';
}

const sizes = {
    sm: 'max-w-3xl',
    md: 'max-w-5xl',
    lg: 'max-w-7xl',
    xl: 'max-w-[96rem]',
    full: 'max-w-full',
};

export default function PageContainer({
    children,
    className,
    size = 'lg',
}: PageContainerProps) {
    return (
        <div className={cn('mx-auto w-full px-4 py-6 md:py-8', sizes[size], className)}>
            {children}
        </div>
    );
}
