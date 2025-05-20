import { useRouter } from 'next/router';
import { getYIQContrastTextColor } from 'utils/colors/colors';
import { twMergeCustom } from 'utils/twMerge';

type FlagProps = {
    href?: string;
    type?: 'blog' | 'discount';
    rgbBgColor?: string;
};

export const Flag: FC<FlagProps> = ({ children, href, className, type, rgbBgColor }) => {
    const router = useRouter();

    const textColor = rgbBgColor && getYIQContrastTextColor(rgbBgColor);

    const flagTwClass = twMergeCustom(
        'inline-flex rounded-flag px-1.5 py-1 text-xs transition-all font-secondary font-semibold text-flag-text',
        textColor,
        href && 'cursor-pointer',
        type === 'blog' && 'bg-secondary-500 hover:bg-background-accent-more',
        type === 'discount' && 'bg-price-discounted',
        className,
    );

    const handleClick = (e: React.MouseEvent<HTMLDivElement>) => {
        if (href) {
            e.preventDefault();
            router.push(href);
        }
    };

    return (
        <div className={flagTwClass} style={{ backgroundColor: rgbBgColor }} onClick={handleClick}>
            {children}
        </div>
    );
};
