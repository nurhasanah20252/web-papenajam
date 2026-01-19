import { type ReactNode, useMemo } from 'react';
import { motion } from 'framer-motion';

interface StaggerContainerProps {
    children: ReactNode;
    className?: string;
    staggerDelay?: number;
    delayOffset?: number;
}

export function StaggerContainer({
    children,
    className = '',
    staggerDelay = 0.1,
    delayOffset = 0,
}: StaggerContainerProps) {
    const containerVariants = useMemo(
        () => ({
            hidden: { opacity: 0 },
            visible: {
                opacity: 1,
                transition: {
                    staggerChildren: staggerDelay,
                    delayChildren: delayOffset,
                },
            },
        }),
        [staggerDelay, delayOffset]
    );

    return (
        <motion.div
            variants={containerVariants}
            initial="hidden"
            animate="visible"
            className={className}
        >
            {children}
        </motion.div>
    );
}

interface StaggerItemProps {
    children: ReactNode;
    className?: string;
    y?: number;
    x?: number;
    scale?: number;
}

export function StaggerItem({
    children,
    className = '',
    y = 20,
    x = 0,
    scale = 0.95,
}: StaggerItemProps) {
    const itemVariants = useMemo(
        () => ({
            hidden: { opacity: 0, y, x, scale },
            visible: {
                opacity: 1,
                y: 0,
                x: 0,
                scale: 1,
                transition: {
                    duration: 0.4,
                    ease: 'easeOut',
                },
            },
        }),
        [x, y, scale]
    );

    return (
        <motion.div variants={itemVariants} className={className}>
            {children}
        </motion.div>
    );
}
