import { AccessibleLink } from 'components/Basic/AccessibleLink/AccessibleLink';
import { DrawerCloseButton } from 'components/Basic/Drawer/DrawerCloseButton';
import { Button } from 'components/Forms/Button/Button';
import { TIDs } from 'cypress/tids';
import { TypeProductFilterOptionsFragment } from 'graphql/requests/productFilterOptions/fragments/ProductFilterOptionsFragment.generated';
import { TypeCategoryAutomatedFilterEnum, TypeProductOrderingModeEnum } from 'graphql/types';
import { useSessionStore } from 'store/useSessionStore';
import { ParametersType } from 'types/productFilter';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible } from 'utils/mappers/price';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useUpdateFilterQuery } from 'utils/queryParams/useUpdateFilterQuery';
import { FilterGroupGeneric } from './FilterGroupGeneric';
import { FilterGroupInStock } from './FilterGroupInStock';
import { FilterGroupParameters } from './FilterGroupParameters';
import { FilterGroupPrice } from './FilterGroupPrice';
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

const DEFAULT_NUMBER_OF_SHOWN_FLAGS = 5;
const DEFAULT_NUMBER_OF_SHOWN_BRANDS = 5;
const DEFAULT_NUMBER_OF_SHOWN_PARAMETERS = 5;

export const FilterPanel: FC<FilterPanelProps> = ({
    productFilterOptions: filterOptions,
    totalCount,
    categoryAutomatedFilters,
}) => {
    const { t } = useTranslation();
    const { resetAllFilterQueries } = useUpdateFilterQuery();
    const currentFilter = useCurrentFilterQuery();
    const activePriceFilter = currentFilter?.minimalPrice !== undefined || currentFilter?.maximalPrice !== undefined;
    const activeFlagFilter = !!currentFilter?.flags?.length;
    const activeBrandFilter = !!currentFilter?.brands?.length;
    const setIsFilterPanelOpen = useSessionStore((s) => s.setIsFilterPanelOpen);
    const shouldDisplayInStockFilter =
        !!filterOptions.inStock && !categoryAutomatedFilters?.includes(TypeCategoryAutomatedFilterEnum.OnStock);

    const closeFilterPanel = () => {
        setIsFilterPanelOpen(false);
        scrollToProductListControls();
    };

    const handleClearAllFiltersClick = () => {
        resetAllFilterQueries();
        closeFilterPanel();
    };

    return (
        <section
            aria-label={t('Product filters', { ns: 'accessibility' })}
            className="relative vl:z-above z-aboveOverlay flex h-full flex-col bg-background-default pb-1"
            data-tid={TIDs.filter_panel}
            id="filter-panel"
        >
            <AccessibleLink
                className="vl:block hidden rounded-md"
                href="#product-list"
                title={t('Skip filters', { ns: 'accessibility' })}
            />

            <div className="grid vl:hidden grid-cols-[2.25rem_1fr_2.25rem] items-center p-5">
                <span aria-hidden="true" />
                <span className="min-w-0 truncate text-center font-secondary font-semibold">{t('Product filter')}</span>
                <DrawerCloseButton
                    aria-label={t('Close filter panel', { ns: 'accessibility' })}
                    title={t('Close filter panel')}
                    onClick={closeFilterPanel}
                />
            </div>

            <div className="vl:static h-full vl:overflow-visible overflow-y-scroll px-5 vl:px-0">
                {shouldDisplayInStockFilter && <FilterGroupInStock inStockCount={filterOptions.inStock} />}

                <div className="divide-y divide-border-less">
                    {isPriceVisible(filterOptions.minimalPrice) && (
                        <FilterGroupPrice
                            ariaLabel={t('Filter by price', { ns: 'accessibility' })}
                            initialMaxPrice={filterOptions.maximalPrice}
                            initialMinPrice={filterOptions.minimalPrice}
                            isActive={activePriceFilter}
                            title={t('Price')}
                        />
                    )}

                    {!!filterOptions.flags?.length && (
                        <FilterGroupGeneric
                            ariaLabel={t('Filter by flags', { ns: 'accessibility' })}
                            defaultNumberOfShownItems={DEFAULT_NUMBER_OF_SHOWN_FLAGS}
                            filterField="flags"
                            isActive={activeFlagFilter}
                            options={filterOptions.flags.map(({ flag, ...rest }) => ({ ...flag, ...rest }))}
                            title={t('Flags')}
                        />
                    )}

                    {!!filterOptions.brands?.length && (
                        <FilterGroupGeneric
                            ariaLabel={t('Filter by brands', { ns: 'accessibility' })}
                            defaultNumberOfShownItems={DEFAULT_NUMBER_OF_SHOWN_BRANDS}
                            filterField="brands"
                            isActive={activeBrandFilter}
                            options={filterOptions.brands.map(({ brand, ...rest }) => ({ ...brand, ...rest }))}
                            title={t('Brands')}
                        />
                    )}

                    {filterOptions.parameters?.map((parameter, index) => {
                        const activeParamFilter = !!currentFilter?.parameters?.find(
                            (currentParameter) => currentParameter.parameter === parameter.uuid,
                        );

                        return (
                            <FilterGroupParameters
                                key={parameter.uuid}
                                defaultNumberOfShownParameters={DEFAULT_NUMBER_OF_SHOWN_PARAMETERS}
                                isActive={activeParamFilter}
                                parameter={parameter as ParametersType}
                                parameterIndex={index}
                                title={parameter.name}
                            />
                        );
                    })}
                </div>
            </div>

            <div className="flex vl:hidden w-full flex-wrap items-center justify-between gap-x-5 gap-y-2 bg-background-more px-5 py-4">
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
        </section>
    );
};
