import { AccessibleLink } from 'components/Basic/AccessibleLink/AccessibleLink';
import { DrawerCloseButton } from 'components/Basic/Drawer/DrawerCloseButton';
import { OVERLAY_PORTAL_ROOT_ID } from 'components/Basic/Portal/portalConstants';
import { Button } from 'components/Forms/Button/Button';
import { TIDs } from 'cypress/tids';
import { TypeProductFilterOptionsFragment } from 'graphql/requests/productFilterOptions/fragments/ProductFilterOptionsFragment.generated';
import { TypeCategoryAutomatedFilterEnum, TypeProductOrderingModeEnum } from 'graphql/types';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { useEffect, useId } from 'react';
import { createPortal } from 'react-dom';
import { useSessionStore } from 'store/useSessionStore';
import { ParametersType } from 'types/productFilter';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getPriceRounded, isPriceVisible } from 'utils/mappers/price';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useUpdateFilterQuery } from 'utils/queryParams/useUpdateFilterQuery';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { FilterGroupGeneric } from './FilterGroupGeneric';
import { FilterGroupInStock } from './FilterGroupInStock';
import { FilterGroupParameters } from './FilterGroupParameters';
import { FilterGroupPrice } from './FilterGroupPrice';
import { FilterPanelDesktopContent, FilterPanelGroup } from './FilterPanelDesktopContent';
import { scrollToProductListControls } from './filterElementIds';

export type FilterPanelProps = {
    productFilterOptions: TypeProductFilterOptionsFragment;
    defaultOrderingMode?: TypeProductOrderingModeEnum | null;
    orderingMode: TypeProductOrderingModeEnum | null;
    originalSlug: string | null;
    slug: string;
    totalCount: number;
    categoryAutomatedFilters?: string[];
};

const Popup = dynamic(() => import('components/Layout/Popup/Popup').then((component) => component.Popup));

const DEFAULT_NUMBER_OF_SHOWN_ITEMS = 5;

export const FilterPanel: FC<FilterPanelProps> = ({
    productFilterOptions: filterOptions,
    totalCount,
    categoryAutomatedFilters,
}) => {
    const { t } = useTranslation();
    const router = useRouter();
    const { resetAllFilterQueries } = useUpdateFilterQuery();
    const currentFilter = useCurrentFilterQuery();
    const isDesktop = useMediaMin('vl') === true;
    const popupId = useId();
    const isFilterPanelOpen = useSessionStore((s) => s.isFilterPanelOpen);
    const setIsFilterPanelOpen = useSessionStore((s) => s.setIsFilterPanelOpen);
    const restoreStoredFocus = useSessionStore((s) => s.restoreStoredFocus);
    const defaultProductFiltersMap = useSessionStore((s) => s.defaultProductFiltersMap);
    const shouldDisplayInStockFilter = !categoryAutomatedFilters?.includes(TypeCategoryAutomatedFilterEnum.OnStock);

    const createFilterGroups = (isStandalone: boolean): FilterPanelGroup[] => {
        const idPrefix = isStandalone ? popupId : '';
        const filterGroups: FilterPanelGroup[] = [];
        if (shouldDisplayInStockFilter) {
            filterGroups.push({
                id: 'availability',
                title: t('Availability'),
                isActive: !!currentFilter?.onlyInStock,
                isAvailable: filterOptions.inStock > 0,
                content: <FilterGroupInStock idPrefix={idPrefix} inStockCount={filterOptions.inStock} />,
            });
        }
        if (isPriceVisible(filterOptions.minimalPrice)) {
            const isActive = currentFilter?.minimalPrice !== undefined || currentFilter?.maximalPrice !== undefined;
            filterGroups.push({
                id: 'price',
                title: t('Price'),
                isActive,
                isAvailable: getPriceRounded(filterOptions.minimalPrice) < getPriceRounded(filterOptions.maximalPrice),
                content: (
                    <FilterGroupPrice
                        idPrefix={idPrefix}
                        ariaLabel={t('Filter by price', { ns: 'accessibility' })}
                        initialMaxPrice={filterOptions.maximalPrice}
                        initialMinPrice={filterOptions.minimalPrice}
                        isActive={isActive}
                        isStandalone={isStandalone}
                        title={t('Price')}
                    />
                ),
            });
        }
        if (filterOptions.flags?.length) {
            const isActive = filterOptions.flags.some(
                ({ flag }) =>
                    currentFilter?.flags?.includes(flag.uuid) || defaultProductFiltersMap.flags.has(flag.uuid),
            );
            filterGroups.push({
                id: 'flags',
                title: t('Flags'),
                isActive,
                isAvailable: filterOptions.flags.some(({ count }) => count > 0),
                content: (
                    <FilterGroupGeneric
                        idPrefix={idPrefix}
                        ariaLabel={t('Filter by flags', { ns: 'accessibility' })}
                        defaultNumberOfShownItems={
                            isStandalone ? filterOptions.flags.length : DEFAULT_NUMBER_OF_SHOWN_ITEMS
                        }
                        filterField="flags"
                        isActive={isActive}
                        isStandalone={isStandalone}
                        options={filterOptions.flags.map(({ flag, ...rest }) => ({ ...flag, ...rest }))}
                        title={t('Flags')}
                    />
                ),
            });
        }
        if (filterOptions.brands?.length) {
            const isActive = filterOptions.brands.some(({ brand }) => currentFilter?.brands?.includes(brand.uuid));
            filterGroups.push({
                id: 'brands',
                title: t('Brands'),
                isActive,
                isAvailable: filterOptions.brands.some(({ count }) => count > 0),
                content: (
                    <FilterGroupGeneric
                        idPrefix={idPrefix}
                        ariaLabel={t('Filter by brands', { ns: 'accessibility' })}
                        defaultNumberOfShownItems={
                            isStandalone ? filterOptions.brands.length : DEFAULT_NUMBER_OF_SHOWN_ITEMS
                        }
                        filterField="brands"
                        isActive={isActive}
                        isStandalone={isStandalone}
                        options={filterOptions.brands.map(({ brand, ...rest }) => ({ ...brand, ...rest }))}
                        title={t('Brands')}
                    />
                ),
            });
        }
        filterOptions.parameters?.forEach((parameter, index) => {
            const selectedParameter = currentFilter?.parameters?.find(
                (currentParameter) => currentParameter.parameter === parameter.uuid,
            );
            const defaultSelectedValues = defaultProductFiltersMap.parameters.get(parameter.uuid);
            const isActive =
                'values' in parameter
                    ? parameter.values.some(
                          ({ uuid }) => selectedParameter?.values?.includes(uuid) || defaultSelectedValues?.has(uuid),
                      )
                    : selectedParameter?.minimalValue !== undefined || selectedParameter?.maximalValue !== undefined;
            const isAvailable =
                'values' in parameter ? parameter.values.some(({ count }) => count > 0) : parameter.isSelectable;
            const unitName = 'unit' in parameter ? parameter.unit?.name : undefined;
            filterGroups.push({
                id: parameter.uuid,
                title: parameter.name + (unitName ? ` (${unitName})` : ''),
                isActive,
                isAvailable,
                content: (
                    <FilterGroupParameters
                        idPrefix={idPrefix}
                        defaultNumberOfShownParameters={
                            isStandalone && 'values' in parameter
                                ? parameter.values.length
                                : DEFAULT_NUMBER_OF_SHOWN_ITEMS
                        }
                        isActive={isActive}
                        isStandalone={isStandalone}
                        parameter={parameter as ParametersType}
                        parameterIndex={index}
                        title={parameter.name}
                    />
                ),
            });
        });
        return filterGroups;
    };
    const filterGroups = createFilterGroups(false);
    const primaryGroupIds = [
        'availability',
        'price',
        'brands',
        ...(filterOptions.parameters
            ?.slice(0, filterOptions.visibleFilterParametersCount)
            .map((parameter) => parameter.uuid) ?? []),
    ];
    const primaryGroups = filterGroups.filter((group) => primaryGroupIds.includes(group.id));

    useEffect(() => {
        const closeOnNavigation = (_url: string, { shallow }: { shallow: boolean }) => {
            if (isDesktop && !shallow) {
                setIsFilterPanelOpen(false);
            }
        };
        router.events.on('routeChangeStart', closeOnNavigation);
        return () => router.events.off('routeChangeStart', closeOnNavigation);
    }, [isDesktop, router.events, setIsFilterPanelOpen]);

    const closeFilterPanel = () => {
        setIsFilterPanelOpen(false);
        scrollToProductListControls();
        if (isDesktop) {
            window.setTimeout(restoreStoredFocus);
        }
    };

    const handleClearAllFiltersClick = () => {
        resetAllFilterQueries();
        closeFilterPanel();
    };

    const footer = (
        <div
            className={
                isDesktop
                    ? 'flex shrink-0 items-center justify-between gap-4 border-border-less border-t pt-5'
                    : 'flex w-full flex-wrap items-center justify-between gap-x-5 gap-y-2 bg-background-more px-5 py-4'
            }
        >
            {currentFilter !== null && (
                <Button
                    aria-label={t('Clear all active filters', { ns: 'accessibility' })}
                    size="large"
                    tid={TIDs.clear_all_filters_button}
                    variant="tertiary"
                    onClick={handleClearAllFiltersClick}
                >
                    {t('Clear all')}
                </Button>
            )}
            <Button className="ml-auto" size="large" onClick={closeFilterPanel}>
                {t('Show')} {totalCount} {t('products count', { count: totalCount })}
            </Button>
        </div>
    );

    if (!isDesktop) {
        return (
            <section
                aria-label={t('Product filters', { ns: 'accessibility' })}
                className="relative z-aboveOverlay flex h-full flex-col bg-background-default pb-1"
                data-tid={TIDs.filter_panel}
                id="filter-panel"
            >
                <div className="grid grid-cols-[2.25rem_1fr_2.25rem] items-center p-5">
                    <span aria-hidden="true" />
                    <span className="min-w-0 truncate text-center font-secondary font-semibold">
                        {t('Product filter')}
                    </span>
                    <DrawerCloseButton
                        aria-label={t('Close filter panel', { ns: 'accessibility' })}
                        title={t('Close filter panel')}
                        onClick={closeFilterPanel}
                    />
                </div>
                <div className="h-full overflow-y-scroll px-5">
                    {shouldDisplayInStockFilter && <FilterGroupInStock inStockCount={filterOptions.inStock} />}
                    <div className="divide-y divide-border-less">
                        {filterGroups
                            .filter((group) => group.id !== 'availability')
                            .map((group) => (
                                <div key={group.id}>{group.content}</div>
                            ))}
                    </div>
                </div>
                {footer}
            </section>
        );
    }

    return (
        <>
            <section
                aria-label={t('Product filters', { ns: 'accessibility' })}
                data-tid={TIDs.filter_panel}
                id="filter-sidebar"
            >
                <AccessibleLink
                    className="rounded-md"
                    href="#product-list"
                    title={t('Skip filters', { ns: 'accessibility' })}
                />
                <div>
                    {primaryGroups.find((group) => group.id === 'availability')?.content}
                    <div className="divide-y divide-border-less">
                        {primaryGroups
                            .filter((group) => group.id !== 'availability')
                            .map((group) => (
                                <div key={group.id}>{group.content}</div>
                            ))}
                    </div>
                </div>
                <Button
                    aria-haspopup="dialog"
                    className="w-full"
                    size="large"
                    tid={TIDs.all_filters_button}
                    onClick={() => setIsFilterPanelOpen(true)}
                >
                    {t('All filters')} ({filterGroups.length})
                </Button>
            </section>
            {isFilterPanelOpen &&
                createPortal(
                    <Popup
                        className="h-[min(44rem,calc(100dvh-4rem))] max-h-[calc(100dvh-4rem)]"
                        contentClassName="flex min-h-0 flex-1 flex-col"
                        size="medium"
                        title={t('Product filter')}
                        onClose={closeFilterPanel}
                    >
                        <section
                            aria-label={t('Product filters', { ns: 'accessibility' })}
                            className="flex min-h-0 flex-1 flex-col"
                            data-tid={TIDs.filter_panel}
                            id="filter-panel"
                        >
                            <FilterPanelDesktopContent groups={createFilterGroups(true)} />
                            {footer}
                        </section>
                    </Popup>,
                    document.getElementById(OVERLAY_PORTAL_ROOT_ID) ?? document.body,
                )}
        </>
    );
};
