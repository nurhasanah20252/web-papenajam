import { SVGProps } from 'react';

export default function CourtLogoIcon({
    className = 'h-10 w-10',
    ...props
}: SVGProps<SVGSVGElement>) {
    return (
        <svg
            viewBox="0 0 100 100"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
            {...props}
        >
            {/* Scales/Libra symbol representing justice */}
            <circle
                cx="50"
                cy="50"
                r="45"
                stroke="currentColor"
                strokeWidth="4"
                fill="none"
            />
            {/* Center pillar */}
            <rect
                x="46"
                y="20"
                width="8"
                height="60"
                fill="currentColor"
            />
            {/* Horizontal beam */}
            <rect
                x="15"
                y="28"
                width="70"
                height="6"
                rx="2"
                fill="currentColor"
            />
            {/* Left scale pan */}
            <path
                d="M 20 34 L 20 48 Q 20 55 27 55 Q 34 55 34 48 L 34 34"
                stroke="currentColor"
                strokeWidth="3"
                fill="none"
            />
            {/* Right scale pan */}
            <path
                d="M 66 34 L 66 48 Q 66 55 73 55 Q 80 55 80 48 L 80 34"
                stroke="currentColor"
                strokeWidth="3"
                fill="none"
            />
            {/* Chain lines for scale pans */}
            <line
                x1="27"
                y1="34"
                x2="20"
                y2="28"
                stroke="currentColor"
                strokeWidth="2"
            />
            <line
                x1="34"
                y1="34"
                x2="34"
                y2="28"
                stroke="currentColor"
                strokeWidth="2"
            />
            <line
                x1="66"
                y1="34"
                x2="66"
                y2="28"
                stroke="currentColor"
                strokeWidth="2"
            />
            <line
                x1="73"
                y1="34"
                x2="80"
                y2="28"
                stroke="currentColor"
                strokeWidth="2"
            />
        </svg>
    );
}
