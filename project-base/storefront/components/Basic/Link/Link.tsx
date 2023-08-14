import { Button } from 'components/Forms/Button/Button';
import { AnchorHTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { ExtendedNextLink } from '../ExtendedNextLink/ExtendedNextLink';
import { twMergeCustom } from 'helpers/twMerge';

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
    const classNameTwClass = twMergeCustom(
        'inline-flex cursor-pointer items-center text-greyDark outline-none hover:text-primary',
        isButton ? 'no-underline hover:no-underline' : 'underline hover:underline',
    );

    const props = {
        className: classNameTwClass,
        href: isExternal ? href : undefined,
        rel,
        target,
        'data-testid': getDataTestId(isExternal, isButton),
    };

    const content = isButton ? <Button className={className}>{children}</Button> : children;

    if (isExternal) {
        return <a {...props}>{content}</a>;
    }

    return (
        <ExtendedNextLink {...props} href={href} passHref type="static">
            {content}
        </ExtendedNextLink>
    );
};
