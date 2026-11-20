import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ExternalLinkIcon } from 'components/Basic/Icon/ExternalLinkIcon';
import { getLinkClassName, LinkStyleOptions } from 'components/Basic/Link/linkUtils';
import { ButtonBaseProps } from 'components/Forms/Button/Button';
import { getButtonClassName } from 'components/Forms/Button/buttonUtils';
import { TIDs } from 'cypress/tids';
import { AnchorHTMLAttributes } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { addRelNoopenerWhenTargetIsBlank } from 'utils/links/addRelNoopenerWhenTargetIsBlank';
import { twMergeCustom } from 'utils/twMerge';

type NativePropsAnchor = Omit<AnchorHTMLAttributes<HTMLAnchorElement>, 'href' | 'type'> & { href: string };

/**
 * A text link that navigates. Pick `placement` and `tone` (see `getLinkClassName`) instead of styling it by hand;
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

export const linkPlaceholderTwClass = getLinkClassName({ placement: 'standalone' });

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
    placement,
    tone,
    tabIndex = 0,
    ...nativeProps
}) => {
    const { t } = useTranslation();

    const isTextLinkInNewTab = !isButton && target === '_blank';
    const classNameTwClass = twMergeCustom(
        isButton
            ? [getButtonClassName(buttonVariant, size, false), 'no-underline hover:no-underline']
            : getLinkClassName({ placement, tone }),
        className,
    );

    // a link inside a sentence scales the icon with the text, a standalone action uses the shared 16px icon
    const externalIconClassName =
        placement === 'standalone' ? 'size-4 shrink-0' : 'ml-1 inline-block size-[0.875em] shrink-0 align-[-0.125em]';

    const content = isTextLinkInNewTab ? (
        <>
            {children}
            <ExternalLinkIcon aria-hidden="true" className={externalIconClassName} />
            <span className="sr-only">({t('Open in new tab')})</span>
        </>
    ) : (
        children
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
