import { FilterGroupGeneric } from './FilterGroupGeneric';
import { FilterGroupInStock } from './FilterGroupInStock';
import { FilterGroupParameters } from './FilterGroupParameters';
import { FilterGroupPrice } from './FilterGroupPrice';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { Button } from 'components/Forms/Button/Button';
import { TypeProductFilterOptionsFragment } from 'graphql/requests/productFilterOptions/fragments/ProductFilterOptionsFragment.generated';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useSessionStore } from 'store/useSessionStore';
import { ParametersType } from 'types/productFilter';
import { isPriceVisible } from 'utils/mappers/price';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useUpdateFilterQuery } from 'utils/queryParams/useUpdateFilterQuery';

export type FilterPanelProps = {
    productFilterOptions: TypeProductFilterOptionsFragment;
    defaultOrderingMode?: TypeProductOrderingModeEnum | null;
    orderingMode: TypeProductOrderingModeEnum | null;
    originalSlug: string | null;
    slug: string;
    totalCount: number;
};

const DEFAULT_NUMBER_OF_SHOWN_FLAGS = 5;
const DEFAULT_NUMBER_OF_SHOWN_BRANDS = 5;
const DEFAULT_NUMBER_OF_SHOWN_PARAMETERS = 5;

export const FilterPanel: FC<FilterPanelProps> = ({ productFilterOptions: filterOptions, totalCount }) => {
    const { t } = useTranslation();
    const { resetAllFilterQueries } = useUpdateFilterQuery();
    const currentFilter = useCurrentFilterQuery();
    const activePriceFilter = currentFilter?.minimalPrice !== undefined || currentFilter?.maximalPrice !== undefined;
    const activeFlagFilter = !!currentFilter?.flags?.length;
    const activeBrandFilter = !!currentFilter?.brands?.length;
    const setIsFilterPanelOpen = useSessionStore((s) => s.setIsFilterPanelOpen);

    return (
        <div className="z-aboveOverlay flex h-full flex-col bg-background pb-1 vl:z-above">
            <div className="flex items-center justify-between p-5 vl:hidden">
                <h5>{t('Product filter')}</h5>
                <span className="inline-flex size-4 cursor-pointer" onClick={() => setIsFilterPanelOpen(false)}>
                    <RemoveIcon className="w-6 text-inputPlaceholder hover:text-inputPlaceholderHovered" />
                </span>
            </div>

            <div className="h-full overflow-y-scroll px-5 vl:static vl:overflow-visible vl:px-0">
                {!!filterOptions.inStock && <FilterGroupInStock inStockCount={filterOptions.inStock} />}

                <div className="divide-y divide-borderAccent">
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

            <div className="flex flex-wrap items-center justify-between gap-x-5 gap-y-2 bg-backgroundMore p-5 vl:hidden">
                <Button className="ml:auto" size="small" onClick={() => setIsFilterPanelOpen(false)}>
                    {t('Show')} {totalCount} {t('products count', { count: totalCount })}
                </Button>
                {currentFilter !== null && (
                    <Button size="small" variant="inverted" onClick={resetAllFilterQueries}>
                        {t('Clear all')}
                    </Button>
                )}
            </div>
        </div>
    );
};
