import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Button, ButtonBaseProps } from 'components/Forms/Button/Button';
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
    size?: 'small';
} & (
        | {
              isButton: true;
              buttonVariant?: ButtonBaseProps['variant'];
          }
        | {
              isButton?: false;
              buttonVariant?: never;
          }
    );

const linkPlaceholderTwClassSegments = [
    'inline-flex cursor-pointer items-center text-link-default hover:text-link-hovered rounded-sm',
    'underline hover:underline',
];

export const linkPlaceholderTwClass = linkPlaceholderTwClassSegments.join(' ');

export const Link: FC<LinkProps> = ({
    isExternal,
    isButton,
    children,
    href,
    rel,
    target,
    className,
    tid,
    buttonVariant,
}) => {
    const classNameTwClass = twMergeCustom(
        linkPlaceholderTwClassSegments[0],
        isButton ? 'no-underline hover:no-underline' : linkPlaceholderTwClassSegments[1],
        className,
    );

    const props = {
        className: classNameTwClass,
        href: isExternal ? href : undefined,
        rel,
        target,
        tabIndex: 0,
    };

    const content = isButton ? (
        <Button className={className} variant={buttonVariant}>
            {children}
        </Button>
    ) : (
        children
    );

    if (isExternal) {
        return (
            <a {...props} data-tid={tid ?? TIDs.basic_link}>
                {content}
            </a>
        );
    }

    return (
        <ExtendedNextLink {...props} passHref href={href} tid={tid ?? TIDs.basic_link}>
            {content}
        </ExtendedNextLink>
    );
};
