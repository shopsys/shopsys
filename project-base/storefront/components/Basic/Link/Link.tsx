import { Button } from 'components/Forms/Button/Button';

import { AnchorHTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { ExtendedNextLink } from '../ExtendedNextLink/ExtendedNextLink';
import { twMergeCustom } from 'utils/twMerge';

type NativePropsAnchor = ExtractNativePropsFromDefault<
    AnchorHTMLAttributes<HTMLAnchorElement>,
    'href',
    'rel' | 'target'
>;

type LinkProps = NativePropsAnchor & {
    isExternal?: boolean;
    isButton?: boolean;
    size?: 'small';
};

const getDataTestId = (isExternal?: boolean, isButton?: boolean) =>
    'basic-link' + (isExternal ? '-external' : '') + (isButton ? '-button' : '');

export const Link: FC<LinkProps> = ({ isExternal, isButton, children, href, rel, target, className }) => {
    const content = (
        <a
            className={twMergeCustom(
                'inline-flex cursor-pointer items-center text-greyDark outline-none hover:text-primary',
                isButton ? 'no-underline hover:no-underline' : 'underline hover:underline',
            )}
            href={isExternal ? href : undefined}
            rel={rel}
            target={target}
            data-testid={getDataTestId(isExternal, isButton)}
        >
            {isButton ? <Button className={className}>{children}</Button> : children}
        </a>
    );

    if (isExternal) {
        return content;
    }

    return (
        <ExtendedNextLink href={href} passHref type="static">
            {content}
        </ExtendedNextLink>
    );
};
