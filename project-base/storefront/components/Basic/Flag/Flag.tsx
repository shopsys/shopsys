import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { twMergeCustom } from 'utils/twMerge';

type FlagProps = { href?: string };

export const Flag: FC<FlagProps> = ({ children, href, className }) => {
    const flagTwClass = twMergeCustom(
        'inline-flex rounded py-1 px-2 text-xs uppercase',
        className,
        'bg-backgroundAccent !text-textInverted no-underline',
        'hover:bg-backgroundAccentMore hover:text-textInverted hover:!no-underline',
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
