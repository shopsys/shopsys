import { useRouter } from 'next/router';
import { twMergeCustom } from 'utils/twMerge';

export type FlagTypes = 'blog' | 'custom' | 'discount';

type FlagProps = {
    href?: string;
    type?: FlagTypes;
};

export const Flag: FC<FlagProps> = ({ children, href, className, type = 'custom' }) => {
    const router = useRouter();

    const flagTwClass = twMergeCustom(
        'inline-flex rounded-sm px-1.5 py-0.5 text-xs transition-all',
        className,
        type === 'blog' &&
            'bg-textSubtle font-secondary font-semiBold text-xs !text-textInverted no-underline hover:bg-backgroundAccentMore hover:text-textInverted hover:!no-underline',
        type === 'discount' && 'bg-priceDiscounted text-textInverted font-semiBold py-1',
    );

    return (
        <div className={flagTwClass} onClick={() => href && router.push(href)}>
            {children}
        </div>
    );
};
