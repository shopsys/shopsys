import { useRouter } from 'next/router';
import { twMergeCustom } from 'utils/twMerge';

export type FlagTypes = 'blog' | 'dynamic' | 'custom';

type FlagProps = {
    href?: string;
    type?: FlagTypes;
};

export const Flag: FC<FlagProps> = ({ children, href, className, type = 'custom' }) => {
    const router = useRouter();

    const flagTwClass = twMergeCustom(
        'inline-flex rounded px-1.5 py-0.5 text-xs transition-all',
        className,
        type === 'blog' &&
            'bg-textSubtle py-[3px] px-[6px] font-secondary font-semiBold text-[12px] leading-[14px] !text-textInverted no-underline hover:bg-backgroundAccentMore hover:text-textInverted hover:!no-underline',
        type === 'dynamic' && '!text-textInverted text-zero py-1.5 rounded-full vl:text-xs vl:py-0.5 vl:rounded',
    );

    return (
        <div className={flagTwClass} onClick={() => href && router.push(href)}>
            {children}
        </div>
    );
};
