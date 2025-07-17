import { useRouter } from 'next/router';
import { getYIQContrastTextColor } from 'utils/colors/colors';
import { twMergeCustom } from 'utils/twMerge';

type FlagProps = {
    href?: string;
    type?: 'blog' | 'discount';
    rgbBgColor?: string;
    ariaLabel?: string;
};

export const Flag: FC<FlagProps> = ({ children, href, className, type, rgbBgColor, ariaLabel }) => {
    const router = useRouter();

    const textColor = rgbBgColor && getYIQContrastTextColor(rgbBgColor);

    const flagTwClass = twMergeCustom(
        'inline-flex rounded-flag px-1.5 py-1 text-xs transition-all font-secondary font-semibold text-flag-text',
        textColor,
        href && 'cursor-pointer',
        type === 'blog' && 'bg-secondary-500',
        type === 'blog' && href && 'hover:bg-background-accent-more',
        type === 'discount' && 'bg-price-discounted',
        className,
    );

    const handleClick = (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
        if (href) {
            e.preventDefault();
            router.push(href);
        }
    };

    const handleKeyDown = (e: React.KeyboardEvent<HTMLButtonElement>) => {
        if (href && e.key === 'Enter') {
            e.preventDefault();
            router.push(href);
        }
    };

    if (href) {
        return (
            <button
                aria-label={ariaLabel}
                className={flagTwClass}
                style={{ backgroundColor: rgbBgColor }}
                tabIndex={0}
                onClick={handleClick}
                onKeyDown={handleKeyDown}
            >
                {children}
            </button>
        );
    }

    return (
        <div aria-label={ariaLabel} className={flagTwClass} style={{ backgroundColor: rgbBgColor }}>
            {children}
        </div>
    );
};
