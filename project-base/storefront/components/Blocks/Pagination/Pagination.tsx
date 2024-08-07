import { Button } from 'components/Forms/Button/Button';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { Fragment, MouseEventHandler, RefObject, forwardRef } from 'react';
import { twJoin } from 'tailwind-merge';
import { getUrlQueriesWithoutDynamicPageQueries } from 'utils/parsing/getUrlQueriesWithoutDynamicPageQueries';
import { useCurrentLoadMoreQuery } from 'utils/queryParams/useCurrentLoadMoreQuery';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';
import { useUpdateLoadMoreQuery } from 'utils/queryParams/useUpdateLoadMoreQuery';
import { useUpdatePaginationQuery } from 'utils/queryParams/useUpdatePaginationQuery';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { usePagination } from 'utils/ui/usePagination';

type PaginationProps = {
    totalCount: number;
    paginationScrollTargetRef: RefObject<HTMLDivElement> | null;
    hasNextPage?: boolean;
    isWithLoadMore?: boolean;
};

export const Pagination: FC<PaginationProps> = ({
    totalCount,
    paginationScrollTargetRef,
    hasNextPage,
    isWithLoadMore,
}) => {
    const router = useRouter();
    const isDesktop = useMediaMin('sm');
    const currentPage = useCurrentPageQuery();
    const currentLoadMore = useCurrentLoadMoreQuery();
    const updatePagination = useUpdatePaginationQuery();
    const loadMore = useUpdateLoadMoreQuery();
    const currentPageWithLoadMore = Math.min(currentPage + currentLoadMore, Math.ceil(totalCount / DEFAULT_PAGE_SIZE));
    const paginationButtons = usePagination(totalCount, currentPageWithLoadMore, !isDesktop, DEFAULT_PAGE_SIZE);
    const { t } = useTranslation();

    if (!paginationButtons || paginationButtons.length === 1) {
        return null;
    }

    const asPathWithoutQueryParams = router.asPath.split('?')[0];
    const queryParams = getUrlQueriesWithoutDynamicPageQueries(router.query);

    const onChangePage = (pageNumber: number) => () => {
        if (paginationScrollTargetRef?.current) {
            paginationScrollTargetRef.current.scrollIntoView();
        }
        updatePagination(pageNumber);
    };

    return (
        <div className="flex w-full flex-col justify-between vl:flex-row vl:justify-between">
            <div className="w-2/5" />

            <div className="order-2 my-3 flex justify-center vl:order-1 vl:w-1/5">
                {isWithLoadMore && hasNextPage && (
                    <Button className="px-3" variant="inverted" onClick={loadMore}>
                        {t('Load more')}
                    </Button>
                )}
            </div>

            <div className="order-1 my-3 flex w-full justify-center gap-1 vl:order-2 vl:w-2/5 vl:justify-end items-center">
                {paginationButtons.map((pageNumber, index, array) => {
                    const urlPageNumber = pageNumber > 1 ? pageNumber.toString() : undefined;
                    const pageParams = urlPageNumber
                        ? new URLSearchParams({ ...queryParams, page: urlPageNumber }).toString()
                        : undefined;
                    const pageHref = `${asPathWithoutQueryParams}${pageParams ? `?${pageParams}` : ''}`;

                    return (
                        <Fragment key={pageNumber}>
                            {isDotKey(array[index - 1] ?? null, pageNumber) && (
                                <PaginationButton isDotButton>&#8230;</PaginationButton>
                            )}
                            {currentPageWithLoadMore === pageNumber ? (
                                <PaginationButton isActive>{pageNumber}</PaginationButton>
                            ) : (
                                <PaginationButton href={pageHref} onClick={onChangePage(pageNumber)}>
                                    {pageNumber}
                                </PaginationButton>
                            )}
                        </Fragment>
                    );
                })}
            </div>
        </div>
    );
};

const isDotKey = (prevPage: number | null, currentPage: number): boolean => {
    return prevPage !== null && prevPage !== currentPage - 1;
};

type PaginationButtonProps = {
    isActive?: boolean;
    isDotButton?: boolean;
    href?: string;
    onClick?: () => void;
};

const PaginationButton: FC<PaginationButtonProps> = forwardRef(
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    ({ children, isActive, isDotButton, href, onClick }, _) => {
        const handleOnClick: MouseEventHandler<HTMLAnchorElement> = (e) => {
            e.preventDefault();

            if (onClick) {
                onClick();
            }
        };

        const Tag = isActive ? 'span' : 'a';

        return (
            <Tag
                href={href}
                className={twJoin(
                    'flex h-12 w-12 items-center justify-center rounded border-2 font-bold no-underline hover:no-underline',
                    (isActive || isDotButton) && 'hover:cursor-default',
                    isActive
                        ? 'bg-actionInvertedBackgroundActive text-actionInvertedTextActive border-actionInvertedBorderActive'
                        : 'bg-actionInvertedBackground text-actionInvertedText border-actionInvertedBackground hover:border-actionInvertedBorderHovered hover:text-actionInvertedTextHovered',
                )}
                onClick={handleOnClick}
            >
                {children}
            </Tag>
        );
    },
);

PaginationButton.displayName = 'PaginationButton';
