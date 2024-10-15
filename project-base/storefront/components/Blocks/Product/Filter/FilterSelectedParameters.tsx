import { SelectedParametersList, SelectedParametersListItem, SelectedParametersName } from './FilterElements';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { ProductFlag } from 'components/Blocks/Product/ProductFlag';
import { TypeProductFilterOptionsFragment } from 'graphql/requests/productFilterOptions/fragments/ProductFilterOptionsFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { DefaultProductFiltersMapType } from 'store/slices/createSeoCategorySlice';
import { useSessionStore } from 'store/useSessionStore';
import { FilterOptionsParameterUrlQueryType } from 'types/productFilter';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useUpdateFilterQuery } from 'utils/queryParams/useUpdateFilterQuery';

type FilterSelectedParametersProps = {
    filterOptions: TypeProductFilterOptionsFragment;
};

export const FilterSelectedParameters: FC<FilterSelectedParametersProps> = ({ filterOptions }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const defaultProductFiltersMap = useSessionStore((s) => s.defaultProductFiltersMap);

    const currentFilter = useCurrentFilterQuery();
    const {
        updateFilterInStockQuery,
        updateFilterPricesQuery,
        updateFilterBrandsQuery,
        updateFilterFlagsQuery,
        updateFilterParametersQuery,
        resetAllFilterQueries,
    } = useUpdateFilterQuery();

    if (!currentFilter && !getHasDefaultFilters(defaultProductFiltersMap)) {
        return null;
    }

    const checkedBrands = currentFilter?.brands?.map((checkedBrandUuid) =>
        filterOptions.brands?.find((brandOption) => brandOption.brand.uuid === checkedBrandUuid),
    );
    const checkedFlags = getCheckedFlags(defaultProductFiltersMap, filterOptions.flags, currentFilter?.flags);

    return (
        <div className="mt-5 vl:mb-5 vl:mt-0">
            <h6 className="mb-5 vl:mb-2">{t('Selected filters')}</h6>

            <div className="flex flex-wrap items-center gap-y-2">
                {!!currentFilter?.onlyInStock && (
                    <SelectedParametersList>
                        <SelectedParametersName>{t('Availability')}</SelectedParametersName>
                        <SelectedParametersListItem onClick={() => updateFilterInStockQuery(false)}>
                            {t('Only goods in stock')}
                            <SelectedParametersIcon />
                        </SelectedParametersListItem>
                    </SelectedParametersList>
                )}

                {(currentFilter?.minimalPrice !== undefined || currentFilter?.maximalPrice !== undefined) && (
                    <SelectedParametersList>
                        <SelectedParametersName>{t('Price')}</SelectedParametersName>
                        <SelectedParametersListItem
                            onClick={() => {
                                updateFilterPricesQuery({ maximalPrice: undefined, minimalPrice: undefined });
                            }}
                        >
                            {currentFilter.minimalPrice !== undefined && (
                                <>
                                    <span>{t('from')}&nbsp;</span>
                                    {formatPrice(currentFilter.minimalPrice)}
                                    {currentFilter.maximalPrice !== undefined && <>&nbsp;</>}
                                </>
                            )}
                            {currentFilter.maximalPrice !== undefined && (
                                <>
                                    <span>{t('to')}&nbsp;</span>
                                    {formatPrice(currentFilter.maximalPrice)}
                                </>
                            )}
                            <SelectedParametersIcon />
                        </SelectedParametersListItem>
                    </SelectedParametersList>
                )}

                {!!checkedBrands?.length && (
                    <SelectedParametersList>
                        <SelectedParametersName>{t('Brands')}</SelectedParametersName>
                        {checkedBrands.map(
                            (checkedBrand) =>
                                !!checkedBrand && (
                                    <SelectedParametersListItem
                                        key={checkedBrand.brand.uuid}
                                        onClick={() => updateFilterBrandsQuery(checkedBrand.brand.uuid)}
                                    >
                                        {checkedBrand.brand.name}
                                        <SelectedParametersIcon />
                                    </SelectedParametersListItem>
                                ),
                        )}
                    </SelectedParametersList>
                )}

                {!!checkedFlags.length && (
                    <SelectedParametersList>
                        <SelectedParametersName>{t('Flags')}</SelectedParametersName>
                        {checkedFlags.map((checkedFlag) => (
                            <SelectedParametersListItem
                                key={checkedFlag.flag.uuid}
                                onClick={() => updateFilterFlagsQuery(checkedFlag.flag.uuid)}
                            >
                                <ProductFlag name={checkedFlag.flag.name} rgbColor={checkedFlag.flag.rgbColor} />
                                <SelectedParametersIcon />
                            </SelectedParametersListItem>
                        ))}
                    </SelectedParametersList>
                )}

                {getSelectedParameters(defaultProductFiltersMap, currentFilter?.parameters).map((selectedParameter) => {
                    const selectedParameterOptions = filterOptions.parameters?.find(
                        (parameterOption) => parameterOption.uuid === selectedParameter.parameter,
                    );

                    const isSliderParameter = selectedParameterOptions?.__typename === 'ParameterSliderFilterOption';
                    const isColorParameter = selectedParameterOptions?.__typename === 'ParameterColorFilterOption';
                    const isCheckBoxParameter =
                        selectedParameterOptions?.__typename === 'ParameterCheckboxFilterOption';

                    const selectedParameterValues =
                        // hack typescript because it is confused about filtering shared types
                        isCheckBoxParameter || isColorParameter
                            ? (
                                  selectedParameterOptions.values as {
                                      uuid: string;
                                      text: string;
                                      isSelected: boolean;
                                      rgbHex: string;
                                  }[]
                              ).filter((selectedParameterValue) => {
                                  return (
                                      selectedParameter.values?.includes(selectedParameterValue.uuid) ||
                                      defaultProductFiltersMap.parameters
                                          .get(selectedParameter.parameter)
                                          ?.has(selectedParameterValue.uuid)
                                  );
                              })
                            : undefined;

                    if (!selectedParameterOptions) {
                        return null;
                    }

                    return (
                        <SelectedParametersList key={selectedParameterOptions.uuid}>
                            <SelectedParametersName>{selectedParameterOptions.name}</SelectedParametersName>
                            {isSliderParameter && (
                                <SelectedParametersListItem
                                    key={selectedParameterOptions.uuid}
                                    onClick={() =>
                                        updateFilterParametersQuery(selectedParameterOptions.uuid, undefined)
                                    }
                                >
                                    <span>{t('from')}&nbsp;</span>
                                    {selectedParameter.minimalValue || selectedParameterOptions.minimalValue}
                                    {!!selectedParameterOptions.unit?.name &&
                                        `\xa0${selectedParameterOptions.unit.name}`}
                                    <span>&nbsp;{t('to')}&nbsp;</span>
                                    {selectedParameter.maximalValue || selectedParameterOptions.maximalValue}
                                    {selectedParameterOptions.unit?.name && `\xa0${selectedParameterOptions.unit.name}`}
                                    <SelectedParametersIcon />
                                </SelectedParametersListItem>
                            )}
                            {selectedParameterValues &&
                                selectedParameterValues.map((selectedValue) => (
                                    <SelectedParametersListItem
                                        key={selectedValue.uuid}
                                        onClick={() =>
                                            updateFilterParametersQuery(selectedParameter.parameter, selectedValue.uuid)
                                        }
                                    >
                                        {selectedValue.rgbHex && selectedValue.rgbHex !== '' && (
                                            <div
                                                className="mr-2 h-4 w-4 rounded border border-text"
                                                style={{ backgroundColor: selectedValue.rgbHex }}
                                            />
                                        )}
                                        {selectedValue.text}
                                        <SelectedParametersIcon />
                                    </SelectedParametersListItem>
                                ))}
                        </SelectedParametersList>
                    );
                })}

                <div
                    className="cursor-pointer font-secondary text-sm font-semibold text-link underline hover:text-linkHovered"
                    onClick={resetAllFilterQueries}
                >
                    {t('Clear all')}
                </div>
            </div>
        </div>
    );
};

const SelectedParametersIcon: FC = () => <RemoveIcon className="ml-3 w-3 cursor-pointer group-hover:text-textError" />;

const getCheckedFlags = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    flagFilterOptions: TypeProductFilterOptionsFragment['flags'],
    flagsCheckedByUser: string[] = [],
) => {
    const checkedFlagsSet = new Set([...flagsCheckedByUser, ...Array.from(defaultProductFiltersMap.flags)]);

    return (flagFilterOptions ?? []).filter((flagOption) => checkedFlagsSet.has(flagOption.flag.uuid));
};

const getSelectedParameters = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    parameters: FilterOptionsParameterUrlQueryType[] | undefined = [],
) => {
    const parametersMap = new Map(parameters.map((parameter) => [parameter.parameter, parameter]));
    const defaultProductFiltersArray = Array.from(defaultProductFiltersMap.parameters);

    for (const [defaultParameterUuid, defaultParameterSelectedValues] of defaultProductFiltersArray) {
        parametersMap.set(defaultParameterUuid, {
            parameter: defaultParameterUuid,
            values: Array.from(defaultParameterSelectedValues),
        });
    }

    return Array.from(parametersMap.values());
};

const getHasDefaultFilters = (defaultProductFiltersMap: DefaultProductFiltersMapType) =>
    defaultProductFiltersMap.flags.size > 0 || defaultProductFiltersMap.parameters.size > 0;
