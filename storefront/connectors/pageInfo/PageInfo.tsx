import { PageInfoApi } from 'graphql/generated';
import { PageInfoType } from 'types/product';

export const mapPageInfoApiData = (pageInfoApiData: PageInfoApi | undefined): PageInfoType => {
    return {
        startCursor:
            pageInfoApiData?.startCursor !== undefined && pageInfoApiData.startCursor !== null
                ? pageInfoApiData.startCursor
                : '',
        endCursor:
            pageInfoApiData?.endCursor !== undefined && pageInfoApiData.endCursor !== null
                ? pageInfoApiData.endCursor
                : '',
        hasNextPage: pageInfoApiData?.hasNextPage !== undefined ? pageInfoApiData.hasNextPage : false,
        hasPreviousPage: pageInfoApiData?.hasPreviousPage !== undefined ? pageInfoApiData.hasPreviousPage : false,
    };
};
