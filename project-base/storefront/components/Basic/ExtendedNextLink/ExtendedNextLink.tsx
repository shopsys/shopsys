import { UrlObject } from 'node:url';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
// biome-ignore lint/style/noRestrictedImports: This component is the single approved wrapper around next/link.
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
import { addRelNoopenerWhenTargetIsBlank } from 'utils/links/addRelNoopenerWhenTargetIsBlank';
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
    locale,
    onClick,
    type,
    skeletonType,
    className,
    tid,
    rel,
    target,
    preventRedirectOnTextSelection = false,
    ...props
}) => {
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const { url } = useDomainConfig();

    const isDynamic = type && FriendlyPagesTypesKeys.includes(type as any);
    const localHref = getLocalHref(href, url);
    const originalAs = isDynamic ? href : as;
    const localAs = getLocalHref(originalAs, url);
    // Absolute URLs already specify their locale; Next.js must not prepend the current one after conversion.
    const isAbsoluteTargetConverted = (originalAs ?? href) !== (localAs ?? localHref);
    const urlHref = isDynamic
        ? {
              pathname: FriendlyPagesDestinations[type as FriendlyPagesTypesKey],
              query: {
                  [SLUG_TYPE_QUERY_PARAMETER_NAME]: FriendlyPagesTypes[type as FriendlyPagesTypesKey],
                  ...queryParams,
              },
          }
        : localHref;

    const handleOnClick: MouseEventHandler<HTMLAnchorElement> = (e) => {
        const mouseWheelClick = e.button === 1;
        const isTargetBlank = target === '_blank';
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
            as={localAs}
            className={className}
            data-tid={tid}
            href={urlHref}
            locale={locale ?? (isAbsoluteTargetConverted ? false : undefined)}
            prefetch={false}
            rel={addRelNoopenerWhenTargetIsBlank(rel, target)}
            tabIndex={0}
            target={target}
            onClick={handleOnClick}
            {...props}
        >
            {children}
        </NextLink>
    );
};

// Next.js normalizes absolute local URLs differently during SSR and hydration.
const getLocalHref = <T extends string | UrlObject | undefined>(href: T, baseUrl: string): T | string => {
    if (typeof href !== 'string') {
        return href;
    }

    try {
        const parsedUrl = new URL(href);

        if (
            parsedUrl.origin === new URL(baseUrl).origin &&
            parsedUrl.pathname.startsWith('/') &&
            !parsedUrl.pathname.startsWith('//')
        ) {
            return `${parsedUrl.pathname}${parsedUrl.search}${parsedUrl.hash}`;
        }
    } catch {
        return href;
    }

    return href;
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
