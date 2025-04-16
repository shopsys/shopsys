import { UrlQueries } from 'types/urlQueries';

export const getUrlQueriesWithoutFalsyValues = (queries: UrlQueries | URLSearchParams | null) => {
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

    (Object.keys(filteredQueries) as Array<keyof typeof filteredQueries>).forEach((key) => {
        if (!filteredQueries[key]) {
            delete filteredQueries[key];
        }
    });

    return filteredQueries;
};
