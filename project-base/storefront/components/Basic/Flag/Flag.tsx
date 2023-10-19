import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { twMergeCustom } from 'helpers/twMerge';

const getDataTestId = (dataTestId?: string) => dataTestId ?? 'basic-flag';

type FlagProps = { href?: string };

export const Flag: FC<FlagProps> = ({ children, dataTestId, href, className }) => {
    const flagTwClass = twMergeCustom(
        'inline-flex rounded bg-primaryLight py-1 px-2 text-xs uppercase text-dark no-underline hover:text-dark hover:no-underline',
        className,
    );

    if (href) {
        return (
            <ExtendedNextLink
                className={flagTwClass}
                data-testid={getDataTestId(dataTestId)}
                href={href}
                type="blogCategory"
            >
                {children}
            </ExtendedNextLink>
        );
    }

    return (
        <div className={flagTwClass} data-testid={getDataTestId(dataTestId)}>
            {children}
        </div>
    );
};
