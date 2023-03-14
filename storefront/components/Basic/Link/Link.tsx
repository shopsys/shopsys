import { Button } from 'components/Forms/Button/Button';
import NextLink from 'next/link';
import { AnchorHTMLAttributes } from 'react';
import { twMerge } from 'tailwind-merge';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

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
            className={twMerge(
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
        <NextLink href={href} passHref>
            {content}
        </NextLink>
    );
};
