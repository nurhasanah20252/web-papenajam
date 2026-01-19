import { type PropsWithChildren } from 'react';
import { motion } from 'framer-motion';

interface PageTransitionProps extends PropsWithChildren {
    className?: string;
}

const variants = {
    hidden: { opacity: 0, y: 20 },
    enter: { opacity: 1, y: 0 },
    exit: { opacity: 0, y: -20 },
};

export default function PageTransition({
    children,
    className,
}: PageTransitionProps) {
    return (
        <motion.div
            initial="hidden"
            animate="enter"
            exit="exit"
            variants={variants}
            transition={{
                duration: 0.3,
                ease: 'easeInOut',
            }}
            className={className}
        >
            {children}
        </motion.div>
    );
}
