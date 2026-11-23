import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { getLinkClassName, LinkStyleOptions } from 'components/Basic/Link/linkUtils';
import { NewTabHint, useGetNewTabAriaLabel } from 'components/Basic/Link/NewTabHint';
import { ButtonBaseProps } from 'components/Forms/Button/Button';
import { getButtonClassName } from 'components/Forms/Button/buttonUtils';
import { TIDs } from 'cypress/tids';
import { AnchorHTMLAttributes } from 'react';
import { addRelNoopenerWhenTargetIsBlank } from 'utils/links/addRelNoopenerWhenTargetIsBlank';
import { twMergeCustom } from 'utils/twMerge';

type NativePropsAnchor = Omit<AnchorHTMLAttributes<HTMLAnchorElement>, 'href' | 'type'> & { href: string };

/**
 * A text link that navigates. Pick `variant` and `tone` (see `getLinkClassName`) instead of styling it by hand;
 * an action that only looks like a link is a `TextButton`, a link that looks like a button is a `LinkButton`.
 */
type LinkProps = NativePropsAnchor &
    LinkStyleOptions & {
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
    variant,
    tone,
    tabIndex = 0,
    ...nativeProps
}) => {
    const getNewTabAriaLabel = useGetNewTabAriaLabel();

    const classNameTwClass = twMergeCustom(
        isButton
            ? [getButtonClassName(buttonVariant, size, false), 'no-underline hover:no-underline']
            : getLinkClassName({ variant, tone }),
        className,
    );

    const props = {
        ...nativeProps,
        className: classNameTwClass,
        href: isExternal ? href : undefined,
        rel: addRelNoopenerWhenTargetIsBlank(rel, target),
        target,
        tabIndex,
    };

    if (isExternal) {
        return (
            <a
                {...props}
                aria-label={getNewTabAriaLabel(nativeProps['aria-label'], target)}
                data-tid={tid ?? TIDs.basic_link}
            >
                {children}
                {target === '_blank' && <NewTabHint />}
            </a>
        );
    }

    return (
        <ExtendedNextLink {...props} passHref href={href} tid={tid ?? TIDs.basic_link}>
            {children}
        </ExtendedNextLink>
    );
};
