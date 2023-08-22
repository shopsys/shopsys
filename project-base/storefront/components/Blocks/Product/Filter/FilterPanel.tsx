import { FilterGroupGeneric } from './FilterGroupGeneric';
import { FilterGroupInStock } from './FilterGroupInStock';
import { FilterGroupParameters } from './FilterGroupParameters';
import { FilterGroupPrice } from './FilterGroupPrice';
import { SelectedParameters } from './SelectedParameters';
import { Icon } from 'components/Basic/Icon/Icon';
import { Remove } from 'components/Basic/Icon/IconsSvg';
import { Button } from 'components/Forms/Button/Button';
import { ProductFilterOptionsFragmentApi, ProductOrderingModeEnumApi } from 'graphql/generated';
import useTranslation from 'next-translate/useTranslation';
import { memo } from 'react';
import { ParametersType } from 'types/productFilter';

type FilterPanelProps = {
    productFilterOptions: ProductFilterOptionsFragmentApi;
    defaultOrderingMode?: ProductOrderingModeEnumApi | null;
    orderingMode: ProductOrderingModeEnumApi | null;
    originalSlug: string | null;
    slug: string;
    panelCloseHandler?: () => void;
    totalCount: number;
};

const TEST_IDENTIFIER = 'blocks-product-filter';
const DEFAULT_NUMBER_OF_SHOWN_FLAGS = 5;
const DEFAULT_NUMBER_OF_SHOWN_BRANDS = 5;
const DEFAULT_NUMBER_OF_SHOWN_PARAMETERS = 5;

export const FilterPanel = memo<FilterPanelProps>(
    ({ productFilterOptions: filterOptions, panelCloseHandler, totalCount }) => {
        const { t } = useTranslation();

        return (
            <div
                className="z-aboveOverlay flex h-full flex-col bg-blueLight pb-1 vl:z-above vl:h-auto vl:rounded"
                data-testid={TEST_IDENTIFIER}
            >
                <div className="border-b-2 border-greyLight px-5 vl:border-none">
                    <div className="flex items-center justify-between bg-blueLight py-5 text-2xl vl:hidden">
                        {t('Product filter')}
                        <span
                            className="relative inline-flex h-7 w-7 cursor-pointer items-center justify-center rounded-full text-primary"
                            onClick={panelCloseHandler}
                        >
                            <Icon icon={<Remove />} className="w-6 text-primary" />
                        </span>
                    </div>

                    <SelectedParameters filterOptions={filterOptions} />
                </div>
                <div className="h-full overflow-y-scroll px-5 vl:static vl:overflow-visible">
                    <FilterGroupPrice
                        title={t('Price')}
                        initialMinPrice={filterOptions.minimalPrice}
                        initialMaxPrice={filterOptions.maximalPrice}
                    />

                    <FilterGroupInStock title={t('Availability')} inStockCount={filterOptions.inStock} />

                    {!!filterOptions.flags?.length && (
                        <FilterGroupGeneric
                            title={t('Flags')}
                            filterField="flags"
                            defaultNumberOfShownItems={DEFAULT_NUMBER_OF_SHOWN_FLAGS}
                            options={filterOptions.flags.map(({ flag, ...rest }) => ({ ...flag, ...rest }))}
                        />
                    )}

                    {!!filterOptions.brands?.length && (
                        <FilterGroupGeneric
                            title={t('Brands')}
                            filterField="brands"
                            defaultNumberOfShownItems={DEFAULT_NUMBER_OF_SHOWN_BRANDS}
                            options={filterOptions.brands.map(({ brand, ...rest }) => ({ ...brand, ...rest }))}
                        />
                    )}

                    {filterOptions.parameters?.map((parameter, index) => (
                        <FilterGroupParameters
                            key={parameter.uuid}
                            parameterIndex={index}
                            title={parameter.name}
                            parameter={parameter as ParametersType}
                            defaultNumberOfShownParameters={DEFAULT_NUMBER_OF_SHOWN_PARAMETERS}
                        />
                    ))}
                </div>
                <div className="flex items-center justify-end border-t-2 border-greyLight p-5 vl:hidden">
                    <Button
                        size="small"
                        onClick={panelCloseHandler}
                        className="inline-block lowercase first-letter:uppercase"
                    >
                        {t('Show')}
                        {` ${totalCount} `}
                        {t('Products count', {
                            count: totalCount,
                        })}
                    </Button>
                </div>
            </div>
        );
    },
);

FilterPanel.displayName = 'FilterPanel';
