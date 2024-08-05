import { SelectedParametersList, SelectedParametersListItem, SelectedParametersName } from './FilterElements';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { RemoveThinIcon } from 'components/Basic/Icon/RemoveThinIcon';
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
        <div className="z-aboveOverlay rounded py-4 vl:z-[0]">
            <div className="h4 mb-3 uppercase">{t('Selected filters')}</div>

            <div className="mb-4 flex flex-col gap-3">
                {!!checkedBrands?.length && (
                    <SelectedParametersList>
                        <SelectedParametersName>{t('Brands')}:</SelectedParametersName>
                        {checkedBrands.map(
                            (checkedBrand) =>
                                !!checkedBrand && (
                                    <SelectedParametersListItem key={checkedBrand.brand.uuid}>
                                        {checkedBrand.brand.name}
                                        <SelectedParametersIcon
                                            onClick={() => updateFilterBrandsQuery(checkedBrand.brand.uuid)}
                                        />
                                    </SelectedParametersListItem>
                                ),
                        )}
                    </SelectedParametersList>
                )}

                {!!checkedFlags.length && (
                    <SelectedParametersList>
                        <SelectedParametersName>{t('Flags')}:</SelectedParametersName>
                        {checkedFlags.map((checkedFlag) => (
                            <SelectedParametersListItem key={checkedFlag.flag.uuid}>
                                {checkedFlag.flag.name}
                                <SelectedParametersIcon onClick={() => updateFilterFlagsQuery(checkedFlag.flag.uuid)} />
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
                            <SelectedParametersName>{selectedParameterOptions.name}:</SelectedParametersName>
                            {isSliderParameter && (
                                <SelectedParametersListItem key={selectedParameterOptions.uuid}>
                                    <span>{t('from')}&nbsp;</span>
                                    {selectedParameter.minimalValue || selectedParameterOptions.minimalValue}
                                    {!!selectedParameterOptions.unit?.name &&
                                        `\xa0${selectedParameterOptions.unit.name}`}
                                    <span>&nbsp;{t('to')}&nbsp;</span>
                                    {selectedParameter.maximalValue || selectedParameterOptions.maximalValue}
                                    {selectedParameterOptions.unit?.name && `\xa0${selectedParameterOptions.unit.name}`}
                                    <SelectedParametersIcon
                                        onClick={() =>
                                            updateFilterParametersQuery(selectedParameterOptions.uuid, undefined)
                                        }
                                    />
                                </SelectedParametersListItem>
                            )}
                            {selectedParameterValues &&
                                selectedParameterValues.map((selectedValue) => (
                                    <SelectedParametersListItem key={selectedValue.uuid}>
                                        {selectedValue.text}
                                        <SelectedParametersIcon
                                            onClick={() =>
                                                updateFilterParametersQuery(
                                                    selectedParameter.parameter,
                                                    selectedValue.uuid,
                                                )
                                            }
                                        />
                                    </SelectedParametersListItem>
                                ))}
                        </SelectedParametersList>
                    );
                })}

                {!!currentFilter?.onlyInStock && (
                    <SelectedParametersList>
                        <SelectedParametersName>{t('Availability')}:</SelectedParametersName>
                        <SelectedParametersListItem>
                            {t('Only goods in stock')}
                            <SelectedParametersIcon onClick={() => updateFilterInStockQuery(false)} />
                        </SelectedParametersListItem>
                    </SelectedParametersList>
                )}

                {(currentFilter?.minimalPrice !== undefined || currentFilter?.maximalPrice !== undefined) && (
                    <SelectedParametersList>
                        <SelectedParametersName>{t('Price')}:</SelectedParametersName>
                        <SelectedParametersListItem>
                            {currentFilter.minimalPrice !== undefined && (
                                <>
                                    <span>{t('from')}&nbsp;</span>
                                    {formatPrice(currentFilter.minimalPrice)}
                                    {currentFilter.maximalPrice !== undefined ? ' ' : ''}
                                </>
                            )}
                            {currentFilter.maximalPrice !== undefined && (
                                <>
                                    <span>{t('to')}&nbsp;</span>
                                    {formatPrice(currentFilter.maximalPrice)}
                                </>
                            )}
                            <SelectedParametersIcon
                                onClick={() => {
                                    updateFilterPricesQuery({ maximalPrice: undefined, minimalPrice: undefined });
                                }}
                            />
                        </SelectedParametersListItem>
                    </SelectedParametersList>
                )}
            </div>
            <div className="flex cursor-pointer items-center text-sm text-graySlate" onClick={resetAllFilterQueries}>
                <div className="font-bold uppercase">{t('Clear all')}</div>
                <RemoveIcon className="ml-2 cursor-pointer text-secondary" />
            </div>
        </div>
    );
};

const SelectedParametersIcon: FC<{ onClick: () => void }> = ({ onClick }) => (
    <RemoveThinIcon className="ml-3 w-3 cursor-pointer" onClick={onClick} />
);

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
