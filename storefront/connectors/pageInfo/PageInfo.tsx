import { PageInfoApi } from 'graphql/generated';

export const mapPageInfoApiData = (pageInfoApiData: PageInfoApi | undefined) => {
    return {
        startCursor:
            pageInfoApiData?.startCursor !== undefined && pageInfoApiData.startCursor !== null
                ? pageInfoApiData.startCursor
                : '',
        endCursor:
            pageInfoApiData?.endCursor !== undefined && pageInfoApiData.endCursor !== null
                ? pageInfoApiData.endCursor
                : '',
        hasNextPage:
            pageInfoApiData?.hasNextPage !== undefined && pageInfoApiData.hasNextPage !== null
                ? pageInfoApiData.hasNextPage
                : false,
        hasPreviousPage:
            pageInfoApiData?.hasPreviousPage !== undefined && pageInfoApiData.hasPreviousPage !== null
                ? pageInfoApiData.hasPreviousPage
                : false,
    };
};
