'use client';

import { useAppConfig } from 'components/providers/AppConfigProvider';
// eslint-disable-next-line no-restricted-imports
import NextLink, { LinkProps } from 'next/link';
import { ComponentPropsWithoutRef, MouseEventHandler } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { FriendlyPagesTypes, FriendlyPagesTypesKey, FriendlyPagesTypesKeys } from 'types/friendlyUrl';
import { PageType } from 'types/simpleNavigation';
import { UrlObject } from 'url';
import { SLUG_TYPE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';
import { isTextSelected } from 'utils/ui/isTextSelected';

export type ExtendedNextLinkProps = Omit<Omit<ComponentPropsWithoutRef<'a'>, 'type'>, keyof LinkProps> &
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
    onClick,
    type,
    skeletonType,
    className,
    tid,
    preventRedirectOnTextSelection = false,
    ...props
}) => {
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const baseUrl = useAppConfig((appConfig) => appConfig.domainConfig).url;

    const isDynamic = type && FriendlyPagesTypesKeys.includes(type as any);

    let urlHref: string | UrlObject = href;

    if (isDynamic) {
        const friendlyPath = typeof href === 'string' ? href : (href as UrlObject).pathname;

        urlHref = {
            pathname: friendlyPath,
            query: {
                [SLUG_TYPE_QUERY_PARAMETER_NAME]: FriendlyPagesTypes[type as FriendlyPagesTypesKey],
                ...queryParams,
            },
        } satisfies UrlObject;
    }

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

            const isLinkExternal = isHrefExternal(href, baseUrl);
            updatePageLoadingState({
                isPageLoading: !!type || !isLinkExternal,
                redirectPageType: type ?? skeletonType,
            });
        }
    };

    return (
        <NextLink
            prefetch
            className={className}
            data-tid={tid}
            href={urlHref}
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
