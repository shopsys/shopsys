import { UrlQueries } from 'types/urlQueries';

export const getUrlQueriesWithoutFalsyValues = (queries: UrlQueries) => {
    const filteredQueries = { ...queries };

    (Object.keys(filteredQueries) as Array<keyof typeof filteredQueries>).forEach((key) => {
        if (!filteredQueries[key]) {
            delete filteredQueries[key];
        }
    });

    return filteredQueries;
};
