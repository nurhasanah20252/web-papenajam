import { type PropsWithChildren } from 'react';
import { motion } from 'framer-motion';

import { Card, CardContent } from '@/components/ui/card';
import { cn } from '@/lib/utils';

interface AnimatedCardProps extends PropsWithChildren {
    className?: string;
    delay?: number;
    hover?: boolean;
}

export function AnimatedCard({
    children,
    className,
    delay = 0,
    hover = true,
}: AnimatedCardProps) {
    return (
        <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.4, delay }}
            whileHover={hover ? { y: -4, scale: 1.02 } : {}}
            className={cn('transition-shadow', className)}
        >
            <Card className="h-full hover:shadow-lg transition-shadow">
                <CardContent className="p-6">{children}</CardContent>
            </Card>
        </motion.div>
    );
}

interface AnimatedCardWithRevealProps extends PropsWithChildren {
    className?: string;
    delay?: number;
    hover?: boolean;
}

export function AnimatedCardWithReveal({
    children,
    className,
    delay = 0,
    hover = true,
}: AnimatedCardWithRevealProps) {
    return (
        <motion.div
            initial={{ opacity: 0, scale: 0.9 }}
            whileInView={{ opacity: 1, scale: 1 }}
            viewport={{ once: true, margin: '-50px' }}
            transition={{ duration: 0.4, delay }}
            whileHover={hover ? { y: -4 } : {}}
            className={cn('transition-shadow', className)}
        >
            <Card className="h-full hover:shadow-lg transition-shadow">
                <CardContent className="p-6">{children}</CardContent>
            </Card>
        </motion.div>
    );
}
