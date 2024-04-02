import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Button } from 'components/Forms/Button/Button';
import { TIDs } from 'cypress/tids';
import { AnchorHTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
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
        tid: TIDs.basic_link,
    };

    const content = isButton ? <Button className={className}>{children}</Button> : children;

    if (isExternal) {
        return <a {...props}>{content}</a>;
    }

    return (
        <ExtendedNextLink {...props} passHref href={href}>
            {content}
        </ExtendedNextLink>
    );
};
