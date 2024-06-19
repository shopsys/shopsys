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
import { SLUG_TYPE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';

export type ExtendedNextLinkProps = Omit<ComponentPropsWithoutRef<'a'>, keyof LinkProps> &
    Omit<LinkProps, 'prefetch'> & {
        queryParams?: Record<string, string>;
        type?: PageType;
    };

export const ExtendedNextLink: FC<ExtendedNextLinkProps> = ({
    children,
    href,
    queryParams,
    as,
    onClick,
    type,
    ...props
}) => {
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);

    const isDynamic = type && FriendlyPagesTypesKeys.includes(type as any);

    const handleOnClick: MouseEventHandler<HTMLAnchorElement> = (e) => {
        const isWithoutOpeningInNewTab = !e.ctrlKey && !e.metaKey;

        if (isWithoutOpeningInNewTab) {
            onClick?.(e);

            if (type) {
                updatePageLoadingState({ isPageLoading: true, redirectPageType: type });
            } else {
                updatePageLoadingState({ redirectPageType: undefined });
            }
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
            onClick={handleOnClick}
            {...props}
        >
            {children}
        </NextLink>
    );
};
