import { type ButtonHTMLAttributes, forwardRef } from 'react';
import { motion } from 'framer-motion';

import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';

interface AnimatedButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
    variant?: 'default' | 'outline' | 'ghost' | 'destructive' | 'link';
    size?: 'default' | 'sm' | 'lg' | 'icon';
    ripple?: boolean;
    glow?: boolean;
}

const AnimatedButton = forwardRef<HTMLButtonElement, AnimatedButtonProps>(
    ({ className, variant = 'default', size = 'default', ripple = true, glow = false, children, ...props }, ref) => {
        const MotionButton = motion(Button);

        return (
            <MotionButton
                ref={ref}
                variant={variant}
                size={size}
                className={cn(
                    'relative overflow-hidden',
                    glow && 'hover:shadow-lg hover:shadow-primary/50',
                    className
                )}
                whileHover={{ scale: 1.02 }}
                whileTap={{ scale: 0.98 }}
                transition={{ type: 'spring', stiffness: 400, damping: 17 }}
                {...props}
            >
                {children}
                {ripple && (
                    <span className="absolute inset-0 ripple-effect" />
                )}
            </MotionButton>
        );
    }
);

AnimatedButton.displayName = 'AnimatedButton';

export { AnimatedButton };
