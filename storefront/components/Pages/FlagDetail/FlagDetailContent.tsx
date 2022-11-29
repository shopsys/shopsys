import { FlagDetailProductsWrapper } from './FlagDetailProductsWrapper';
import { Heading } from 'components/Basic/Heading/Heading';
import { PaginationProvider } from 'components/Blocks/Pagination/PaginationProvider';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { getNewPagination } from 'helpers/pagination/getNewPagination';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useRemoveSortFromUrlIfDefault } from 'hooks/filter/useRemoveSortFromUrlIfDefault';
import { useRouter } from 'next/router';
import { FC, useRef } from 'react';
import { FlagDetailType } from 'types/flag';

type FlagDetailContentProps = {
    flag: FlagDetailType;
};

export const FlagDetailContent: FC<FlagDetailContentProps> = ({ flag }) => {
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    const router = useRouter();
    const currentPage = parsePageNumberFromQuery(router.query[PAGE_QUERY_PARAMETER_NAME]);
    useRemoveSortFromUrlIfDefault(flag.productConnection.orderingMode, flag.productConnection.defaultOrderingMode);

    return (
        <PaginationProvider key={flag.uuid} {...getNewPagination(currentPage)}>
            <Webline>
                <Heading type={'h1'}>{flag.name}</Heading>
            </Webline>
            <Webline>
                <div ref={containerWrapRef}>
                    <SortingBar
                        sorting={flag.productConnection.orderingMode}
                        totalCount={flag.productConnection.totalCount}
                    />
                    <FlagDetailProductsWrapper flag={flag} containerWrapRef={containerWrapRef} />
                </div>
            </Webline>
        </PaginationProvider>
    );
};
