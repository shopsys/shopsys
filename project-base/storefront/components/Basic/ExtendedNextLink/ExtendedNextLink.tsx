import { SLUG_TYPE_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
// eslint-disable-next-line no-restricted-imports
import NextLink, { LinkProps } from 'next/link';
import { FriendlyPagesDestinations, FriendlyPagesTypes, FriendlyPagesTypesKeys } from 'types/friendlyUrl';

type ExtendedNextLinkProps = {
    type: FriendlyPagesTypesKeys | 'static';
    queryParams?: Record<string, string>;
} & Omit<LinkProps, 'prefetch'>;

export const ExtendedNextLink: FC<ExtendedNextLinkProps> = ({ children, href, type, queryParams, as, ...props }) => {
    const isStatic = type === 'static';

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
            {...props}
        >
            {children}
        </NextLink>
    );
};
