'use client';

import { useCurrentLoadMoreQuery } from 'app/_utils/queryParams/useCurrentLoadMoreQuery';
import { useCurrentPageQuery } from 'app/_utils/queryParams/useCurrentPageQuery';
import { useUpdatePaginationQuery } from 'app/_utils/queryParams/useUpdatePaginationQuery';
import { Button } from 'components/Forms/Button/Button';
import { useTranslation } from 'components/providers/TranslationProvider';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { usePathname, useSearchParams } from 'next/navigation';
import { Fragment, MouseEventHandler, RefObject, forwardRef, useCallback, useRef } from 'react';
import { twJoin } from 'tailwind-merge';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { usePagination } from 'utils/ui/usePagination';

type PaginationProps = {
    totalCount: number;
    paginationScrollTargetRef: RefObject<HTMLDivElement> | null;
    hasNextPage?: boolean;
    isWithLoadMore?: boolean;
    pageSize?: number;
    type?: 'default' | 'blog';
};

export const Pagination: FC<PaginationProps> = ({
    totalCount,
    paginationScrollTargetRef,
    hasNextPage,
    isWithLoadMore,
    pageSize = DEFAULT_PAGE_SIZE,
    type = 'defualt',
}) => {
    // Use a ref to track if we're in the middle of a URL update
    const isUpdatingUrlRef = useRef(false);
    const searchParams = useSearchParams();
    const pathname = usePathname();
    const isDesktop = useMediaMin('sm');
    const currentPage = useCurrentPageQuery();
    const currentLoadMore = useCurrentLoadMoreQuery();
    const updatePagination = useUpdatePaginationQuery();
    const currentPageWithLoadMore = Math.min(currentPage + currentLoadMore, Math.ceil(totalCount / pageSize));
    const paginationButtons = usePagination(totalCount, currentPageWithLoadMore, !isDesktop, pageSize);
    const { t } = useTranslation();

    // Define a safe URL update function at the component level
    const safeUpdateUrl = useCallback(
        (newLoadMore: number) => {
            // Prevent duplicate updates
            if (isUpdatingUrlRef.current) {
                return;
            }

            // Mark that we're updating
            isUpdatingUrlRef.current = true;

            // Use setTimeout to ensure this happens outside React's render cycle
            setTimeout(() => {
                const params = new URLSearchParams(searchParams?.toString() ?? '');
                params.set('lm', newLoadMore.toString());

                // Update the URL without triggering a navigation
                window.history.pushState(null, '', `${pathname}?${params.toString()}`);

                // Reset our flags
                isUpdatingUrlRef.current = false;
            }, 0);
        },
        [searchParams, pathname, currentPage],
    );

    // Now use our safe function in onLoadMore
    function onLoadMore() {
        safeUpdateUrl(currentLoadMore + 1);
    }

    if (!paginationButtons || paginationButtons.length === 1) {
        return null;
    }

    const onChangePage = (pageNumber: number) => () => {
        if (paginationScrollTargetRef?.current) {
            paginationScrollTargetRef.current.scrollIntoView();
        }
        updatePagination(pageNumber);
    };

    const seenProducts = currentPageWithLoadMore * pageSize;
    const remainingProducts = totalCount - seenProducts;
    const loadMoreCount = remainingProducts > pageSize ? pageSize : remainingProducts;
    const loadMoreTranslation =
        type === 'blog' ? t('articles count', { count: loadMoreCount }) : t('products count', { count: loadMoreCount });

    return (
        <div className="vl:flex-row flex flex-col items-center justify-between gap-5">
            {isWithLoadMore && hasNextPage && (
                <Button className="px-3" variant="inverted" onClick={onLoadMore}>
                    {t('Load more')} {loadMoreCount} {loadMoreTranslation}
                </Button>
            )}

            <div className="ml-auto flex gap-1">
                {paginationButtons.map((pageNumber, index, array) => {
                    const urlPageNumber = pageNumber > 1 ? pageNumber.toString() : undefined;
                    const pageParams = urlPageNumber
                        ? new URLSearchParams({
                              ...Object.fromEntries(searchParams?.entries() ?? []),
                              page: urlPageNumber,
                          }).toString()
                        : undefined;
                    const pageHref = `${pathname}${pageParams ? `?${pageParams}` : ''}`;

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
            onClick?.();
        };

        const Tag = isActive ? 'span' : 'a';

        return (
            <Tag
                href={href}
                className={twJoin(
                    'flex size-8 items-center justify-center rounded-lg border-2 font-bold no-underline hover:no-underline md:size-12',
                    (isActive || isDotButton) && 'border-none hover:cursor-default',
                    isActive
                        ? 'border-actionInvertedBorderActive bg-actionInvertedBackgroundActive text-actionInvertedTextActive'
                        : 'border-actionInvertedBorder bg-actionInvertedBackground text-actionInvertedText hover:border-actionInvertedBorderHovered hover:bg-actionInvertedBackgroundHovered hover:text-actionInvertedTextHovered',
                )}
                onClick={handleOnClick}
            >
                {children}
            </Tag>
        );
    },
);

PaginationButton.displayName = 'PaginationButton';
