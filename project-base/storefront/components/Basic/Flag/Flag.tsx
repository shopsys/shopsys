import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { twMergeCustom } from 'utils/twMerge';

type FlagProps = { href?: string };

export const Flag: FC<FlagProps> = ({ children, href, className }) => {
    const flagTwClass = twMergeCustom(
        'inline-flex rounded bg-primaryLight py-1 px-2 text-xs uppercase text-dark no-underline hover:text-dark hover:no-underline',
        className,
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
