import NextLink from 'next/link';
import { AnchorHTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<AnchorHTMLAttributes<HTMLAnchorElement>, 'href', never>;

type FlagProps = NativeProps & {
    classNames?: string;
};

const getDataTestId = (dataTestId?: string) => dataTestId ?? 'basic-flag';

export const Flag: FC<FlagProps> = ({ children, dataTestId, href, classNames }) => (
    <NextLink href={href} passHref>
        <a
            className={twMergeCustom(
                'mb-2 mr-3 inline-flex rounded-sm bg-primaryLight py-1 px-2 text-xs uppercase text-dark no-underline hover:text-dark hover:no-underline',
                classNames,
            )}
            data-testid={getDataTestId(dataTestId)}
        >
            {children}
        </a>
    </NextLink>
);
