import { Button } from 'components/Forms/Button/Button';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { getUrlQueriesWithoutDynamicPageQueries } from 'helpers/parsing/urlParsing';
import { useMediaMin } from 'hooks/ui/useMediaMin';
import { usePagination } from 'hooks/ui/usePagination';
import { useQueryParams } from 'hooks/useQueryParams';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { Fragment, MouseEventHandler, RefObject, forwardRef } from 'react';
import { twJoin } from 'tailwind-merge';

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
    const { currentPage, updatePagination, loadMore, currentLoadMore } = useQueryParams();
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
                    <Button className="h-11 px-3 vl:h-7" variant="primary" onClick={loadMore}>
                        {t('Load more')}
                    </Button>
                )}
            </div>
            <div className="order-1 my-3 flex w-full justify-center gap-1 vl:order-2 vl:w-2/5 vl:justify-end">
                {paginationButtons.map((pageNumber, index, array) => {
                    // eslint-disable-next-line @typescript-eslint/no-unused-vars
                    const { page, ...queryParamsWithoutPage } = queryParams;
                    const pageParams =
                        pageNumber > 1
                            ? new URLSearchParams({ ...queryParams, page: pageNumber.toString() }).toString()
                            : new URLSearchParams(queryParamsWithoutPage).toString();
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
                    'flex h-11 w-11 items-center  justify-center rounded border font-bold no-underline hover:no-underline vl:h-7 vl:w-7',
                    (isActive || isDotButton) && 'hover:cursor-default',
                    isActive
                        ? 'border-none bg-primary text-white hover:bg-primaryDarker hover:text-white'
                        : 'border-white bg-white',
                )}
                onClick={handleOnClick}
            >
                {children}
            </Tag>
        );
    },
);

PaginationButton.displayName = 'PaginationButton';
