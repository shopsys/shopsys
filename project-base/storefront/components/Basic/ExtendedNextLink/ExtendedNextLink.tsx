import { SLUG_TYPE_QUERY_PARAMETER_NAME } from 'helpers/queryParamNames';
// eslint-disable-next-line no-restricted-imports
import NextLink, { LinkProps } from 'next/link';
import { ComponentPropsWithoutRef, MouseEventHandler } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { FriendlyPagesDestinations, FriendlyPagesTypes, FriendlyPagesTypesKeys } from 'types/friendlyUrl';

const STATIC_PAGES = [
    'static',
    'homepage',
    'stores',
    'wishlist',
    'comparison',
    'orders',
    'order',
    'productMainVariant',
] as const;

type StaticPageType = (typeof STATIC_PAGES)[number];

export type ExtendedLinkPageType = FriendlyPagesTypesKeys | StaticPageType;

type ExtendedNextLinkProps = {
    type: ExtendedLinkPageType;
    queryParams?: Record<string, string>;
} & Omit<ComponentPropsWithoutRef<'a'>, keyof LinkProps> &
    Omit<LinkProps, 'prefetch'>;

export const ExtendedNextLink: FC<ExtendedNextLinkProps> = ({
    children,
    href,
    type,
    queryParams,
    as,
    onClick,
    ...props
}) => {
    const isStatic =
        type === 'static' ||
        type === 'homepage' ||
        type === 'stores' ||
        type === 'wishlist' ||
        type === 'comparison' ||
        type === 'orders' ||
        type === 'order' ||
        type === 'productMainVariant';
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);

    const handleOnClick: MouseEventHandler<HTMLAnchorElement> = (e) => {
        const isWithoutOpeningInNewTab = !e.ctrlKey && !e.metaKey;

        if (isWithoutOpeningInNewTab) {
            onClick?.(e);

            if (type !== 'static') {
                updatePageLoadingState({ isPageLoading: true, redirectPageType: type });
            }
        }
    };

    return (
        <NextLink
            as={isStatic ? as : href}
            prefetch={false}
            href={
                isStatic
                    ? href
                    : {
                          pathname: FriendlyPagesDestinations[type],
                          query: { [SLUG_TYPE_QUERY_PARAMETER_NAME]: FriendlyPagesTypes[type], ...queryParams },
                      }
            }
            onClick={handleOnClick}
            {...props}
        >
            {children}
        </NextLink>
    );
};
