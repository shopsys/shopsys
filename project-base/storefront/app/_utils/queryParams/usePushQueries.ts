import { usePathname, useRouter } from 'next/navigation';
import { UrlQueries } from 'types/urlQueries';
import { getUrlQueriesWithoutFalsyValues } from 'utils/parsing/getUrlQueriesWithoutFalsyValues';

export const usePushQueries = () => {
    const router = useRouter();
    const pathname = usePathname() ?? '/';

    return (queries: UrlQueries, isPush?: boolean, pathnameOverride?: string) => {
        // remove queries which are not set or removed
        const filteredQueries = getUrlQueriesWithoutFalsyValues(queries);

        const searchParams = new URLSearchParams(filteredQueries);

        router[isPush ? 'push' : 'replace'](
            `${pathnameOverride ?? pathname}${searchParams.size > 0 ? `?${searchParams}` : ''}`,
            {
                scroll: false,
            },
        );
    };
};
