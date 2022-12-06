import { PaginationButtonStyled, PaginationWrapperStyled } from './Pagination.style';
import { usePaginationContext } from './usePaginationContext';
import { getNewPagination } from 'helpers/pagination/getNewPagination';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useMediaMin } from 'hooks/ui/useMediaMin';
import { usePagination } from 'hooks/ui/usePagination';
import NextLink from 'next/link';
import { useRouter } from 'next/router';
import { FC, Fragment, RefObject, useCallback } from 'react';

type PaginationProps = {
    totalCount: number;
    containerWrapRef: RefObject<HTMLDivElement> | null;
};

const TEST_IDENTIFIER = 'blocks-pagination';
export const DEFAULT_PAGE_SIZE = 9;

export const Pagination: FC<PaginationProps> = ({ totalCount, containerWrapRef }) => {
    const router = useRouter();
    const isDesktop = useMediaMin('sm');
    const [{ page: currentPage }, dispatch] = usePaginationContext();
    const paginationButtons = usePagination(totalCount, currentPage, !isDesktop, DEFAULT_PAGE_SIZE);

    const asPathWithoutQueryParams = router.asPath.split('?')[0];
    const queryParamsWithoutPage = { ...router.query };
    delete queryParamsWithoutPage.all;
    delete queryParamsWithoutPage[PAGE_QUERY_PARAMETER_NAME];

    const onChangePage = useCallback(
        (page: number) => () => {
            dispatch({
                type: 'setPagination',
                payload: getNewPagination(page),
            });
            if (containerWrapRef !== null && containerWrapRef.current !== null) {
                containerWrapRef.current.scrollIntoView();
            }
        },
        [containerWrapRef, dispatch],
    );

    if (paginationButtons === null || paginationButtons.length === 1) {
        return null;
    }

    return (
        <PaginationWrapperStyled data-testid={TEST_IDENTIFIER}>
            {paginationButtons.map((pageNumber, index, array) => (
                <Fragment key={pageNumber}>
                    {isDotKey(array[index - 1] ?? null, pageNumber) && (
                        <PaginationButtonStyled dotButton>&#8230;</PaginationButtonStyled>
                    )}
                    {currentPage === pageNumber ? (
                        <PaginationButtonStyled data-testid={TEST_IDENTIFIER + '-' + pageNumber} active>
                            {pageNumber}
                        </PaginationButtonStyled>
                    ) : (
                        <NextLink
                            href={{
                                pathname: asPathWithoutQueryParams,
                                query: {
                                    ...queryParamsWithoutPage,
                                    ...(pageNumber !== 1 ? { page: pageNumber } : {}),
                                },
                            }}
                            passHref
                            shallow
                            scroll={false}
                        >
                            <PaginationButtonStyled
                                data-testid={TEST_IDENTIFIER + '-' + pageNumber}
                                onClick={onChangePage(pageNumber)}
                            >
                                {pageNumber}
                            </PaginationButtonStyled>
                        </NextLink>
                    )}
                </Fragment>
            ))}
        </PaginationWrapperStyled>
    );
};

const isDotKey = (prevPage: number | null, currentPage: number): boolean => {
    return prevPage !== null && prevPage !== currentPage - 1;
};
