import { FC, Fragment, useEffect, useMemo, useState } from 'react';
import { initialState, userActions } from 'redux/slices/user';
import { PaginationButtonStyled, PaginationWrapperStyled } from './Pagination.style';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { getNewPagination } from 'utils/Pagination/getNewPagination';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { mobileFirstSizes } from 'components/Theme/mediaQueries';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { usePagination } from 'hooks/ui/usePagination';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';
import { useRouter } from 'next/router';

type PaginationProps = {
    totalCount: number;
};

const Pagination: FC<PaginationProps> = (props): JSX.Element | null => {
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

    const paginationButtons = useMemo(() => {
        return usePagination(
            props.totalCount,
            paginationState.currentPage,
            isMobilePaginationVisible,
            initialState.pagination.pageSize,
        );
    }, [props.totalCount, paginationState.currentPage, isMobilePaginationVisible, initialState.pagination.pageSize]);
    useEffect(() => {
        dispatch(userActions.setPagination({ ...initialState.pagination }));
    }, [router.asPath]);

    if (paginationButtons === undefined || paginationButtons === null) {
        return null;
    }

    if (paginationState.currentPage > paginationButtons[paginationButtons.length - 1]) {
        dispatch(userActions.setPagination({ ...initialState.pagination }));
    }

    const updateUrlWithCurrentPage = (currentPage: number) => {
        const queryParams = new URLSearchParams(window.location.search);
        if (paginationButtons.length < 2 || currentPage === initialState.pagination.currentPage) {
            queryParams.delete('page');
        } else {
            queryParams.set('page', currentPage.toString());
        }
        let newState = document.location.pathname;
        if (queryParams.toString().length > 0) {
            newState = '?' + queryParams.toString();
        }
        history.replaceState(history.state, document.title, newState);
    };

    if (typeof window !== 'undefined') {
        updateUrlWithCurrentPage(paginationState.currentPage);
    }

    let previousPageButton: number | null = null;

    return (
        <PaginationWrapperStyled>
            {paginationButtons.map((pageButton: number) => {
                let dotKey!: string;
                if (
                    !isMobilePaginationVisible &&
                    previousPageButton !== null &&
                    previousPageButton !== pageButton - 1
                ) {
                    dotKey = previousPageButton + '-' + pageButton;
                }
                previousPageButton = pageButton;

                return (
                    <Fragment key={pageButton}>
                        {dotKey && (
                            <PaginationButtonStyled key={dotKey} dotButton={true}>
                                &#8230;
                            </PaginationButtonStyled>
                        )}
                        <PaginationButtonStyled
                            key={pageButton}
                            active={paginationState.currentPage === pageButton}
                            dotButton={false}
                            onClick={() => {
                                dispatch(
                                    userActions.setPagination({
                                        ...getNewPagination(pageButton, initialState.pagination.pageSize),
                                    }),
                                );
                            }}
                        >
                            {pageButton}
                        </PaginationButtonStyled>
                    </Fragment>
                );
            })}
        </PaginationWrapperStyled>
    );
};

export default Pagination;
