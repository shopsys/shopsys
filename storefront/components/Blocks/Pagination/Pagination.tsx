import { PaginationButtonStyled, PaginationWrapperStyled } from './Pagination.style';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { mobileFirstSizes } from 'components/Theme/mediaQueries';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { usePagination } from 'hooks/ui/usePagination';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';
import NextLink from 'next/link';
import { useRouter } from 'next/router';
import { FC, Fragment, RefObject, useCallback, useEffect, useState } from 'react';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { initialState, userActions } from 'redux/slices/user';
import { getNewPagination } from 'utils/Pagination/getNewPagination';

type PaginationProps = {
    totalCount: number;
    containerWrapRef: RefObject<HTMLDivElement> | null;
};

const isDotKey = (prevPage: number | null, currentPage: number): boolean => {
    return prevPage !== null && prevPage !== currentPage - 1;
};

const TEST_IDENTIFIER = 'blocks-pagination';

export const Pagination: FC<PaginationProps> = ({ totalCount, containerWrapRef }) => {
    const router = useRouter();
    const dispatch = useShopsysDispatch();
    const { width } = useGetWindowSize();
    const [isMobilePaginationVisible, setMobilePaginationVisible] = useState(false);
    useResizeWidthEffect(
        width,
        mobileFirstSizes.sm,
        () => setMobilePaginationVisible(false),
        () => setMobilePaginationVisible(true),
        () => setMobilePaginationVisible(isElementVisible([{ min: 0, max: 480 }], width)),
    );
    const paginationState = useShopsysSelector((state) => state.user.pagination);

    const paginationButtons = usePagination(
        totalCount,
        paginationState.currentPage,
        isMobilePaginationVisible,
        initialState.pagination.pageSize,
    );

    const asPathWithoutQueryParams = router.asPath.split('?')[0];
    const queryParamsWithoutPage = { ...router.query };
    delete queryParamsWithoutPage.all;
    delete queryParamsWithoutPage.page;

    useEffect(() => {
        dispatch(userActions.setPagination({ ...initialState.pagination }));
    }, [dispatch, asPathWithoutQueryParams]);

    const onChangePage = useCallback(
        (page: number) => () => {
            dispatch(
                userActions.setPagination({
                    ...getNewPagination(page, initialState.pagination.pageSize),
                }),
            );
            if (containerWrapRef !== null && containerWrapRef.current !== null) {
                containerWrapRef.current.scrollIntoView();
            }
        },
        [containerWrapRef, dispatch],
    );

    if (paginationButtons === null || paginationButtons.length === 1) {
        return null;
    }

    if (paginationState.currentPage > paginationButtons[paginationButtons.length - 1]) {
        dispatch(userActions.setPagination({ ...initialState.pagination }));
    }

    return (
        <PaginationWrapperStyled data-testid={TEST_IDENTIFIER}>
            {paginationButtons.map((pageNumber, index, array) => (
                <Fragment key={pageNumber}>
                    {isDotKey(array[index - 1] ?? null, pageNumber) && (
                        <PaginationButtonStyled dotButton>&#8230;</PaginationButtonStyled>
                    )}
                    {paginationState.currentPage === pageNumber ? (
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
