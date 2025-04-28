import { FriendlyPagesDestinations } from 'types/friendlyUrl';
import { UrlQueries } from 'types/urlQueries';

export const getUrlQueriesWithoutDynamicPageQueries = (queries: UrlQueries | URLSearchParams | null) => {
    if (!queries) {
        return {};
    }

    let urlQueries: UrlQueries;

    if (queries instanceof URLSearchParams) {
        urlQueries = Object.fromEntries(queries.entries());
    } else {
        urlQueries = queries;
    }

    const filteredQueries = { ...urlQueries };

    const friendlyPageDynamicSegments = Object.values(FriendlyPagesDestinations).map(
        (pagePath) => pagePath.match(/\[(\w+)\]/)?.[1],
    );

    (Object.keys(filteredQueries) as Array<keyof typeof filteredQueries>).forEach((key) => {
        if (friendlyPageDynamicSegments.includes(key)) {
            delete filteredQueries[key];
        }
    });

    return filteredQueries;
};
