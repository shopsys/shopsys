'use client';

import { FilterGroupGeneric } from './FilterGroupGeneric';
import { FilterGroupInStock } from './FilterGroupInStock';
import { FilterGroupParameters } from './FilterGroupParameters';
import { FilterGroupPrice } from './FilterGroupPrice';
import { useCurrentFilterQuery } from 'app/_utils/queryParams/useCurrentFilterQuery';
import { useUpdateFilterQuery } from 'app/_utils/queryParams/useUpdateFilterQuery';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { Button } from 'components/Forms/Button/Button';
import { useTranslation } from 'components/providers/TranslationProvider';
import { TypeProductFilterOptionsFragment } from 'graphql/requests/productFilterOptions/fragments/ProductFilterOptionsFragment.ssr';
import { TypeCategoryAutomatedFilterEnum } from 'graphql/types';
import { useSessionStore } from 'store/useSessionStore';
import { ParametersType } from 'types/productFilter';
import { isPriceVisible } from 'utils/mappers/price';

export type FilterPanelProps = {
    productFilterOptions: TypeProductFilterOptionsFragment;
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

    return (
        <div className="z-aboveOverlay bg-background-default vl:z-above flex h-full flex-col pb-1">
            <div className="vl:hidden flex items-center justify-between p-5">
                <h5>{t('Product filter')}</h5>
                <button
                    aria-label={t('Close filter panel')}
                    className="inline-flex size-4 cursor-pointer"
                    type="button"
                    onClick={() => setIsFilterPanelOpen(false)}
                >
                    <RemoveIcon className="text-input-placeholder-default hover:text-input-placeholder-hovered w-6" />
                </button>
            </div>

            <div className="vl:static vl:overflow-visible vl:px-0 h-full overflow-y-scroll px-5">
                {shouldDisplayInStockFilter && <FilterGroupInStock inStockCount={filterOptions.inStock} />}

                <div className="divide-borderAccentLess divide-y">
                    {isPriceVisible(filterOptions.minimalPrice) && (
                        <FilterGroupPrice
                            initialMaxPrice={filterOptions.maximalPrice}
                            initialMinPrice={filterOptions.minimalPrice}
                            isActive={activePriceFilter}
                            title={t('Price')}
                        />
                    )}

                    {!!filterOptions.flags?.length && (
                        <FilterGroupGeneric
                            defaultNumberOfShownItems={DEFAULT_NUMBER_OF_SHOWN_FLAGS}
                            filterField="flags"
                            isActive={activeFlagFilter}
                            options={filterOptions.flags.map(({ flag, ...rest }) => ({ ...flag, ...rest }))}
                            title={t('Flags')}
                        />
                    )}

                    {!!filterOptions.brands?.length && (
                        <FilterGroupGeneric
                            defaultNumberOfShownItems={DEFAULT_NUMBER_OF_SHOWN_BRANDS}
                            filterField="brands"
                            isActive={activeBrandFilter}
                            options={filterOptions.brands.map(({ brand, ...rest }) => ({ ...brand, ...rest }))}
                            title={t('Brands')}
                        />
                    )}

                    {filterOptions.parameters?.map((parameter, index) => {
                        const activeParamFilter = !!currentFilter?.parameters?.find(
                            (currentParameter: { parameter: string }) => currentParameter.parameter === parameter.uuid,
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

            <div className="bg-background-more vl:hidden flex flex-wrap items-center justify-between gap-x-5 gap-y-2 px-5 py-4">
                <Button className="ml:auto" size="large" onClick={() => setIsFilterPanelOpen(false)}>
                    {t('Show')} {totalCount} {t('products count', { count: totalCount })}
                </Button>

                <Button size="large" variant="inverted" onClick={resetAllFilterQueries}>
                    {t('Clear all')}
                </Button>
            </div>
        </div>
    );
};
