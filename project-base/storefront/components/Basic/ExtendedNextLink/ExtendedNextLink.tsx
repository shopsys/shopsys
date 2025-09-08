import { useDomainConfig } from 'components/providers/DomainConfigProvider';
// eslint-disable-next-line no-restricted-imports
import NextLink, { LinkProps } from 'next/link';
import { ComponentPropsWithoutRef, MouseEventHandler } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { useSessionStore } from 'store/useSessionStore';
import {
    FriendlyPagesDestinations,
    FriendlyPagesTypes,
    FriendlyPagesTypesKey,
    FriendlyPagesTypesKeys,
} from 'types/friendlyUrl';
import { UrlObject } from 'url';
import { SLUG_TYPE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';
import { isTextSelected } from 'utils/ui/isTextSelected';

export type ExtendedNextLinkProps = Omit<ComponentPropsWithoutRef<'a'>, keyof LinkProps> &
    Omit<LinkProps, 'prefetch'> & {
        queryParams?: Record<string, string>;
        type?: PageType;
        skeletonType?: PageType;
        preventRedirectOnTextSelection?: boolean;
    };

export const ExtendedNextLink: FC<ExtendedNextLinkProps> = ({
    children,
    href,
    queryParams,
    as,
    onClick,
    type,
    skeletonType,
    className,
    tid,
    preventRedirectOnTextSelection = false,
    ...props
}) => {
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const { url } = useDomainConfig();

    const isDynamic = type && FriendlyPagesTypesKeys.includes(type as any);
    const urlHref = isDynamic
        ? {
              pathname: FriendlyPagesDestinations[type as FriendlyPagesTypesKey],
              query: {
                  [SLUG_TYPE_QUERY_PARAMETER_NAME]: FriendlyPagesTypes[type as FriendlyPagesTypesKey],
                  ...queryParams,
              },
          }
        : href;

    const handleOnClick: MouseEventHandler<HTMLAnchorElement> = (e) => {
        const mouseWheelClick = e.button === 1;
        const isTargetBlank = props.target === '_blank';
        const isWithoutOpeningInNewTab = !e.ctrlKey && !e.metaKey && !mouseWheelClick && !isTargetBlank;

        if (preventRedirectOnTextSelection && isTextSelected()) {
            e.preventDefault();
            e.stopPropagation();

            return;
        }

        if (isWithoutOpeningInNewTab) {
            onClick?.(e);

            const isLinkExternal = isHrefExternal(href, url);
            updatePageLoadingState({
                isPageLoading: !!type || !isLinkExternal,
                redirectPageType: type ?? skeletonType,
            });
        }
    };

    return (
        <NextLink
            as={isDynamic ? href : as}
            className={className}
            data-tid={tid}
            href={urlHref}
            prefetch={false}
            tabIndex={0}
            onClick={handleOnClick}
            {...props}
        >
            {children}
        </NextLink>
    );
};

const isHrefExternal = (href: string | UrlObject, baseUrl: string) => {
    const currentHostname = new URL(baseUrl).hostname;

    if (typeof href === 'object') {
        return currentHostname !== href.hostname;
    }

    try {
        return currentHostname !== new URL(href).hostname;
    } catch {
        return false;
    }
};
