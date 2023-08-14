import { twMergeCustom } from 'helpers/twMerge';
import { ExtendedNextLink } from '../ExtendedNextLink/ExtendedNextLink';

const getDataTestId = (dataTestId?: string) => dataTestId ?? 'basic-flag';

type FlagProps = { href?: string };

export const Flag: FC<FlagProps> = ({ children, dataTestId, href, className }) => {
    const flagTwClass = twMergeCustom(
        'inline-flex rounded-sm bg-primaryLight py-1 px-2 text-xs uppercase text-dark no-underline hover:text-dark hover:no-underline',
        className,
    );

    if (href) {
        return (
            <ExtendedNextLink
                type="blogCategory"
                href={href}
                className={flagTwClass}
                data-testid={getDataTestId(dataTestId)}
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
