import { useRef, useState } from 'react';
import { motion } from 'framer-motion';

import { cn } from '@/lib/utils';

interface OptimizedImageProps {
    src: string;
    alt: string;
    width?: number;
    height?: number;
    className?: string;
    containerClassName?: string;
    loading?: 'lazy' | 'eager';
    placeholder?: 'blur' | 'empty';
    aspectRatio?: string;
}

export default function OptimizedImage({
    src,
    alt,
    width,
    height,
    className,
    containerClassName,
    loading = 'lazy',
    placeholder = 'blur',
    aspectRatio,
}: OptimizedImageProps) {
    const [isLoaded, setIsLoaded] = useState(false);
    const [isError, setIsError] = useState(false);
    const imgRef = useRef<HTMLImageElement>(null);

    const handleLoad = () => {
        setIsLoaded(true);
    };

    const handleError = () => {
        setIsError(true);
        setIsLoaded(true);
    };

    return (
        <div
            className={cn(
                'relative overflow-hidden bg-muted',
                containerClassName
            )}
            style={{
                aspectRatio: aspectRatio || `${width}/${height}`,
            }}
        >
            {!isLoaded && placeholder === 'blur' && (
                <div className="absolute inset-0 animate-pulse bg-muted" />
            )}

            {isError && (
                <div className="absolute inset-0 flex items-center justify-center text-muted-foreground">
                    <svg
                        className="h-12 w-12"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={1.5}
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                    </svg>
                </div>
            )}

            <motion.img
                ref={imgRef}
                src={src}
                alt={alt}
                width={width}
                height={height}
                loading={loading}
                onLoad={handleLoad}
                onError={handleError}
                initial={{ opacity: 0 }}
                animate={{ opacity: isLoaded ? 1 : 0 }}
                transition={{ duration: 0.3 }}
                className={cn('h-full w-full object-cover', className)}
            />
        </div>
    );
}
