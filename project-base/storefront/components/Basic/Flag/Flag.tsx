import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { twMergeCustom } from 'utils/twMerge';

export type FlagTypes = 'blog' | 'dynamic' | 'custom';

type FlagProps = {
    href?: string;
    type?: FlagTypes;
};

export const Flag: FC<FlagProps> = ({ children, href, className, type = 'custom' }) => {
    const flagTwClass = twMergeCustom(
        'inline-flex rounded px-1.5 py-0.5 text-xs',
        className,
        type === 'blog' &&
            'bg-inputPlaceholder !text-textInverted no-underline hover:bg-inputPlaceholderHovered hover:text-textInverted hover:!no-underline',
        type === 'dynamic' && '!text-textInverted text-zero py-1.5 rounded-full vl:text-xs vl:py-0.5 vl:rounded',
    );

    if (href) {
        return (
            <ExtendedNextLink className={flagTwClass} href={href} type="blogCategory">
                {children}
            </ExtendedNextLink>
        );
    }

    return <div className={flagTwClass}>{children}</div>;
};
