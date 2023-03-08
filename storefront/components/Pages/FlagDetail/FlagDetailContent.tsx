import { FlagDetailProductsWrapper } from './FlagDetailProductsWrapper';
import { Heading } from 'components/Basic/Heading/Heading';
import { PaginationProvider } from 'components/Blocks/Pagination/PaginationProvider';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { FlagDetailFragmentApi } from 'graphql/generated';
import { getNewPagination } from 'helpers/pagination/getNewPagination';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useRemoveSortFromUrlIfDefault } from 'hooks/filter/useRemoveSortFromUrlIfDefault';
import { useRouter } from 'next/router';
import { useRef } from 'react';

type FlagDetailContentProps = {
    flag: FlagDetailFragmentApi;
};

export const FlagDetailContent: FC<FlagDetailContentProps> = ({ flag }) => {
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    const router = useRouter();
    const currentPage = parsePageNumberFromQuery(router.query[PAGE_QUERY_PARAMETER_NAME]);
    useRemoveSortFromUrlIfDefault(flag.products.orderingMode, flag.products.defaultOrderingMode);

    return (
        <PaginationProvider key={flag.uuid} {...getNewPagination(currentPage)}>
            <Webline>
                <Heading type="h1">{flag.name}</Heading>
            </Webline>
            <Webline>
                <div ref={containerWrapRef}>
                    <SortingBar sorting={flag.products.orderingMode} totalCount={flag.products.totalCount} />
                    <FlagDetailProductsWrapper flag={flag} containerWrapRef={containerWrapRef} />
                </div>
            </Webline>
        </PaginationProvider>
    );
};
