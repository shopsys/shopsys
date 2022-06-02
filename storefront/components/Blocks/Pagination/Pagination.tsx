import { PaginationButtonStyled, PaginationWrapperStyled } from './Pagination.style';
import { isElementVisible } from 'components/Helpers/isElementVisible';
import { mobileFirstSizes } from 'components/Theme/mediaQueries';
import { canUseDom } from 'helpers/canUseDom';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useLatest } from 'hooks/ui/useLatest';
import { usePagination } from 'hooks/ui/usePagination';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';
import { useRouter } from 'next/router';
import { FC, Fragment, RefObject, useEffect, useState } from 'react';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { initialState, userActions } from 'redux/slices/user';
import { getNewPagination } from 'utils/Pagination/getNewPagination';

type PaginationProps = {
    totalCount: number;
    containerWrapRef: RefObject<HTMLDivElement> | null;
};

const Pagination: FC<PaginationProps> = (props): JSX.Element | null => {
    const testIdentifier = 'blocks-pagination';

    const router = useRouter();
    const routerRef = useLatest(router);
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
        props.totalCount,
        paginationState.currentPage,
        isMobilePaginationVisible,
        initialState.pagination.pageSize,
    );

    const asPathWithoutQueryParams = router.asPath.split('?')[0];

    useEffect(() => {
        dispatch(userActions.setPagination({ ...initialState.pagination }));
    }, [dispatch, asPathWithoutQueryParams]);

    useEffect(() => {
        if (!canUseDom() || !routerRef.current) {
            return;
        }

        const query = routerRef.current.query;
        delete query.all;
        if (
            (paginationButtons && paginationButtons.length < 2) ||
            paginationState.currentPage === initialState.pagination.currentPage
        ) {
            delete query.page;
        } else {
            query.page = paginationState.currentPage.toString();
        }

        const pathname = routerRef.current.asPath.split('?')[0];
        routerRef.current.push({ pathname, query }, undefined, { shallow: true });
    }, [paginationButtons, paginationState.currentPage, routerRef]);

    if (paginationButtons === null || paginationButtons.length === 1) {
        return null;
    }

    if (paginationState.currentPage > paginationButtons[paginationButtons.length - 1]) {
        dispatch(userActions.setPagination({ ...initialState.pagination }));
    }

    const scrollToListTop = () => {
        if (props.containerWrapRef !== null && props.containerWrapRef.current !== null) {
            props.containerWrapRef.current.scrollIntoView();
        }
    };

    let previousPageButton: number | null = null;

    return (
        <PaginationWrapperStyled data-testid={testIdentifier}>
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
                            data-testid={testIdentifier + '-' + pageButton}
                            key={pageButton}
                            active={paginationState.currentPage === pageButton}
                            dotButton={false}
                            onClick={() => {
                                dispatch(
                                    userActions.setPagination({
                                        ...getNewPagination(pageButton, initialState.pagination.pageSize),
                                    }),
                                );
                                scrollToListTop();
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
