import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ButtonBaseProps, getButtonClassName } from 'components/Forms/Button/Button';
import { TIDs } from 'cypress/tids';
import { AnchorHTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { addRelNoopenerWhenTargetIsBlank } from 'utils/links/addRelNoopenerWhenTargetIsBlank';
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
    size = 'small',
    buttonVariant,
}) => {
    const classNameTwClass = twMergeCustom(
        isButton
            ? [getButtonClassName(buttonVariant, size, false, false), 'no-underline hover:no-underline']
            : linkPlaceholderTwClassSegments,
        className,
    );

    const props = {
        className: classNameTwClass,
        href: isExternal ? href : undefined,
        rel: addRelNoopenerWhenTargetIsBlank(rel, target),
        target,
        tabIndex: 0,
    };

    if (isExternal) {
        return (
            <a {...props} data-tid={tid ?? TIDs.basic_link}>
                {children}
            </a>
        );
    }

    return (
        <ExtendedNextLink {...props} passHref href={href} tid={tid ?? TIDs.basic_link}>
            {children}
        </ExtendedNextLink>
    );
};
