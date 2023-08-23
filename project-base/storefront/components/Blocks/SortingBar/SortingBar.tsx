import { isElementVisible } from 'helpers/isElementVisible';
import { SortIcon } from 'components/Basic/Icon/IconsSvg';
import { mobileFirstSizes } from 'components/Theme/mediaQueries';
import { ProductOrderingModeEnumApi } from 'graphql/generated';
import { DEFAULT_SORT } from 'helpers/filterOptions/seoCategories';
import { getUrlQueriesWithoutDynamicPageQueries } from 'helpers/parsing/urlParsing';
import useTranslation from 'next-translate/useTranslation';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import { useQueryParams } from 'hooks/useQueryParams';
import { useRouter } from 'next/router';
import { MouseEventHandler, useState } from 'react';
import { twJoin } from 'tailwind-merge';

type SortingBarProps = {
    totalCount: number;
    sorting: ProductOrderingModeEnumApi | null;
    customSortOptions?: ProductOrderingModeEnumApi[];
};

const TEST_IDENTIFIER = 'blocks-sortingbar';

const DEFAULT_SORT_OPTIONS = [
    ProductOrderingModeEnumApi.PriorityApi,
    ProductOrderingModeEnumApi.PriceAscApi,
    ProductOrderingModeEnumApi.PriceDescApi,
];

export const SortingBar: FC<SortingBarProps> = ({ sorting, totalCount, customSortOptions }) => {
    const { t } = useTranslation();
    const { sort: sortSelected, updateSort } = useQueryParams();
    const { width } = useGetWindowSize();
    const [isMobileSortBarVisible, setMobileSortBarVisible] = useState(true);
    const [toggleSortMenu, setToggleSortMenu] = useState(false);

    const sortOptionsLabels = {
        [ProductOrderingModeEnumApi.PriorityApi]: t('priority'),
        [ProductOrderingModeEnumApi.PriceAscApi]: t('price ascending'),
        [ProductOrderingModeEnumApi.PriceDescApi]: t('price descending'),
        [ProductOrderingModeEnumApi.RelevanceApi]: t('relevance'),
        [ProductOrderingModeEnumApi.NameAscApi]: t('name ascending'),
        [ProductOrderingModeEnumApi.NameDescApi]: t('name descending'),
    };

    const sortOptions = customSortOptions || DEFAULT_SORT_OPTIONS;
    const selectedSortOption = sortSelected || sorting || DEFAULT_SORT;

    useResizeWidthEffect(
        width,
        mobileFirstSizes.vl,
        () => setMobileSortBarVisible(false),
        () => setMobileSortBarVisible(true),
        () => setMobileSortBarVisible(isElementVisible([{ min: 0, max: 1024 }], width)),
    );

    const router = useRouter();
    const asPathWithoutQueryParams = router.asPath.split('?')[0];

    return (
        <div
            className="relative w-full border-greyLighter vl:static vl:inline-block vl:border-b"
            data-testid={TEST_IDENTIFIER}
        >
            <div
                className={twJoin(
                    'flex w-full flex-col rounded bg-border vl:static vl:top-1 vl:flex-row vl:items-center vl:justify-between vl:rounded-none vl:bg-opacity-0',
                    toggleSortMenu && 'rounded-b-none',
                )}
            >
                {isMobileSortBarVisible ? (
                    <>
                        <div
                            className="flex items-center justify-center py-2"
                            onClick={() => setToggleSortMenu((prev) => !prev)}
                            data-testid={TEST_IDENTIFIER + '-selected'}
                        >
                            <SortIcon className="w-5 align-middle" />
                            <div className="pl-2 text-justify font-bold text-dark">
                                <div className="uppercase leading-5">{t('Sort')}</div>
                                <div
                                    className="text-sm leading-3 text-primary"
                                    data-testid={TEST_IDENTIFIER + '-selected-value'}
                                >
                                    {selectedSortOption}
                                </div>
                            </div>
                        </div>

                        {toggleSortMenu && (
                            <div className="absolute top-full z-[1] w-full rounded-b bg-border">
                                {sortOptions
                                    .filter((sortOption) => sortOption !== selectedSortOption)
                                    .map((sortOption, index) => (
                                        <SortingBarItem
                                            key={sortOption}
                                            isActive={sortOption === selectedSortOption}
                                            onClick={() => {
                                                setToggleSortMenu((prev) => !prev);
                                                updateSort(sortOption);
                                            }}
                                            dataTestId={TEST_IDENTIFIER + '-' + index}
                                        >
                                            {sortOptionsLabels[sortOption]}
                                        </SortingBarItem>
                                    ))}
                            </div>
                        )}
                    </>
                ) : (
                    <>
                        <div className="flex vl:gap-3">
                            {sortOptions.map((sortOption, index) => {
                                // eslint-disable-next-line @typescript-eslint/no-unused-vars
                                const { page, ...queriesWithoutPage } = getUrlQueriesWithoutDynamicPageQueries(
                                    router.query,
                                );
                                const sortParams = new URLSearchParams({
                                    ...queriesWithoutPage,
                                    sort: sortOption,
                                }).toString();
                                const sortHref = `${asPathWithoutQueryParams}?${sortParams}`;

                                return (
                                    <SortingBarItem
                                        key={sortOption}
                                        isActive={sortOption === selectedSortOption}
                                        onClick={() => updateSort(sortOption)}
                                        dataTestId={TEST_IDENTIFIER + '-' + index}
                                        href={sortHref}
                                    >
                                        {sortOptionsLabels[sortOption]}
                                    </SortingBarItem>
                                );
                            })}
                        </div>
                        <div>
                            <strong>{totalCount} </strong>
                            {t('Products count', { count: totalCount })}
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};

const SortingBarItem: FC<{ isActive: boolean; href?: string; onClick?: () => void }> = ({
    dataTestId,
    children,
    isActive,
    href,
    onClick,
}) => {
    const handleOnClick: MouseEventHandler<HTMLAnchorElement> = (e) => {
        e.preventDefault();

        if (onClick) {
            onClick();
        }
    };

    return (
        <a
            className={twJoin(
                'block border-b-2 py-4 px-2 text-center text-xs uppercase text-dark no-underline transition hover:text-dark hover:no-underline vl:py-2',
                isActive ? 'cursor-default hover:text-dark vl:border-primary' : 'border-none',
            )}
            data-testid={dataTestId}
            href={href}
            onClick={handleOnClick}
        >
            {children}
        </a>
    );
};
