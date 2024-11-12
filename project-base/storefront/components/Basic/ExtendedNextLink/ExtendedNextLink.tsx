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

export type ExtendedNextLinkProps = Omit<ComponentPropsWithoutRef<'a'>, keyof LinkProps> &
    Omit<LinkProps, 'prefetch'> & {
        queryParams?: Record<string, string>;
        type?: PageType;
        skeletonType?: PageType;
        onClickExtended?: MouseEventHandler<HTMLAnchorElement>;
    };

export const ExtendedNextLink: FC<ExtendedNextLinkProps> = ({
    children,
    href,
    queryParams,
    as,
    onClick,
    onClickExtended,
    type,
    skeletonType,
    ...props
}) => {
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const { url } = useDomainConfig();

    const isDynamic = type && FriendlyPagesTypesKeys.includes(type as any);

    const handleOnClick: MouseEventHandler<HTMLAnchorElement> = (e) => {
        const mouseWheelClick = e.button === 1;
        const isWithoutOpeningInNewTab = !e.ctrlKey && !e.metaKey && !mouseWheelClick;

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
            prefetch={false}
            href={
                isDynamic
                    ? {
                          pathname: FriendlyPagesDestinations[type as FriendlyPagesTypesKey],
                          query: {
                              [SLUG_TYPE_QUERY_PARAMETER_NAME]: FriendlyPagesTypes[type as FriendlyPagesTypesKey],
                              ...queryParams,
                          },
                      }
                    : href
            }
            onClick={onClickExtended ?? handleOnClick}
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
    } catch (e) {
        return false;
    }
};
