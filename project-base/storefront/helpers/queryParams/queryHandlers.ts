import { UrlQueries } from 'hooks/useQueryParams';
import { FriendlyPagesDestinations } from 'types/friendlyUrl';

/**
 * Next.js is saving id of dynamic path in query (categorySlug from /categories/[categorySlug]).
 * For query manipulation we need some way to remove this query (which we are not using),
 * so we are passing queries without those for dynamic paths.
 
 * This variable simply return all dynamic ids which we are using and which are saved in query.
 */
const ignoredUrlQueries: (string | undefined)[] = Object.values(FriendlyPagesDestinations).map(
    (pagePath) => pagePath.match(/\[(\w+)\]/)?.[1],
);

export const getFilteredQueries = (queries: UrlQueries) => {
    const filteredQueries = { ...queries };

    (Object.keys(filteredQueries) as Array<keyof typeof filteredQueries>).forEach((key) => {
        if (!filteredQueries[key] || ignoredUrlQueries.includes(key)) {
            delete filteredQueries[key];
        }
    });

    return filteredQueries;
};
