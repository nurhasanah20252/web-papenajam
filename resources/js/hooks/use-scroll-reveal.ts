import { useEffect, useRef, useState } from 'react';

interface UseScrollRevealOptions {
    threshold?: number;
    rootMargin?: string;
    triggerOnce?: boolean;
}

interface UseScrollRevealReturn {
    ref: React.RefObject<HTMLDivElement>;
    isInView: boolean;
    hasRevealed: boolean;
}

export function useScrollReveal({
    threshold = 0.1,
    rootMargin = '0px',
    triggerOnce = true,
}: UseScrollRevealOptions = {}): UseScrollRevealReturn {
    const [isInView, setIsInView] = useState(false);
    const [hasRevealed, setHasRevealed] = useState(false);
    const ref = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const element = ref.current;
        if (!element) return;

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    setIsInView(true);
                    setHasRevealed(true);

                    if (triggerOnce) {
                        observer.unobserve(element);
                    }
                } else if (!triggerOnce) {
                    setIsInView(false);
                }
            },
            { threshold, rootMargin }
        );

        observer.observe(element);

        return () => {
            if (element) {
                observer.unobserve(element);
            }
        };
    }, [threshold, rootMargin, triggerOnce]);

    return { ref, isInView, hasRevealed };
}
