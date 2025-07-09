import { SelectedParametersList, SelectedParametersListItem, SelectedParametersName } from './FilterElements';
import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { Flag } from 'components/Basic/Flag/Flag';
import { RemoveBoldIcon } from 'components/Basic/Icon/RemoveBoldIcon';
import { AnimatePresence } from 'framer-motion';
import { TypeProductFilterOptionsFragment } from 'graphql/requests/productFilterOptions/fragments/ProductFilterOptionsFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';
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

    const checkedBrands = useMemo(
        () =>
            currentFilter?.brands?.map((checkedBrandUuid) =>
                filterOptions.brands?.find((brandOption) => brandOption.brand.uuid === checkedBrandUuid),
            ),
        [currentFilter?.brands, filterOptions.brands],
    );

    const checkedFlags = useMemo(
        () => getCheckedFlags(defaultProductFiltersMap, filterOptions.flags, currentFilter?.flags),
        [defaultProductFiltersMap, filterOptions.flags, currentFilter?.flags],
    );

    const selectedParameters = useMemo(
        () => getSelectedParameters(defaultProductFiltersMap, currentFilter?.parameters),
        [defaultProductFiltersMap, currentFilter?.parameters],
    );

    return (
        <AnimatePresence initial={false}>
            {!currentFilter && !getHasDefaultFilters(defaultProductFiltersMap) ? null : (
                <AnimateCollapseDiv className="vl:mb-5 vl:mt-0 mt-5 !block" keyName="selected-parameters">
                    <p className="h6 vl:mb-2 mb-5">{t('Selected filters')}</p>

                    <div className="flex flex-wrap items-center gap-y-2">
                        {!!currentFilter?.onlyInStock && (
                            <SelectedParametersList keyName="filter-only-in-stock">
                                <SelectedParametersName>{t('Availability')}</SelectedParametersName>
                                <SelectedParametersListItem
                                    ariaLabel={t('Remove filter Availability only goods in stock')}
                                    onClick={() => updateFilterInStockQuery(false)}
                                >
                                    {t('Only goods in stock')}
                                    <SelectedParametersIcon />
                                </SelectedParametersListItem>
                            </SelectedParametersList>
                        )}

                        {(currentFilter?.minimalPrice !== undefined || currentFilter?.maximalPrice !== undefined) && (
                            <SelectedParametersList keyName="filter-minmax-price">
                                <SelectedParametersName>{t('Price')}</SelectedParametersName>
                                <SelectedParametersListItem
                                    ariaLabel={t('Remove filter Price from {{ minimalPrice }} to {{ maximalPrice }}', {
                                        minimalPrice: formatPrice(currentFilter.minimalPrice ?? 0),
                                        maximalPrice: formatPrice(currentFilter.maximalPrice ?? 0),
                                    })}
                                    onClick={() => {
                                        updateFilterPricesQuery({
                                            maximalPrice: undefined,
                                            minimalPrice: undefined,
                                        });
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
                            <SelectedParametersList keyName="selected-brands">
                                <SelectedParametersName>{t('Brands')}</SelectedParametersName>
                                {checkedBrands.map(
                                    (checkedBrand) =>
                                        !!checkedBrand && (
                                            <SelectedParametersListItem
                                                key={checkedBrand.brand.uuid}
                                                ariaLabel={t('Remove filter Brand {{ filterName }}', {
                                                    filterName: checkedBrand.brand.name,
                                                })}
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
                            <SelectedParametersList keyName="selected-flags">
                                <SelectedParametersName>{t('Flags')}</SelectedParametersName>
                                {checkedFlags.map((checkedFlag) => (
                                    <SelectedParametersListItem
                                        key={checkedFlag.flag.uuid}
                                        ariaLabel={t('Remove filter Flag {{ filterName }}', {
                                            filterName: checkedFlag.flag.name,
                                        })}
                                        onClick={() => updateFilterFlagsQuery(checkedFlag.flag.uuid)}
                                    >
                                        <Flag className="py-0.5" rgbBgColor={checkedFlag.flag.rgbColor}>
                                            {checkedFlag.flag.name}
                                        </Flag>
                                        <SelectedParametersIcon />
                                    </SelectedParametersListItem>
                                ))}
                            </SelectedParametersList>
                        )}

                        {selectedParameters.map((selectedParameter) => {
                            const selectedParameterOptions = filterOptions.parameters?.find(
                                (parameterOption) => parameterOption.uuid === selectedParameter.parameter,
                            );

                            const isSliderParameter =
                                selectedParameterOptions?.__typename === 'ParameterSliderFilterOption';
                            const isColorParameter =
                                selectedParameterOptions?.__typename === 'ParameterColorFilterOption';
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
                                <SelectedParametersList
                                    key={selectedParameterOptions.uuid}
                                    keyName={selectedParameterOptions.uuid}
                                >
                                    <SelectedParametersName>{selectedParameterOptions.name}</SelectedParametersName>
                                    {isSliderParameter && (
                                        <SelectedParametersListItem
                                            key={selectedParameterOptions.uuid}
                                            ariaLabel={t(
                                                'Remove parameter range from {{ minValue }} to {{ maxValue }} from group {{ groupName }}',
                                                {
                                                    minValue:
                                                        selectedParameter.minimalValue ||
                                                        selectedParameterOptions.minimalValue,
                                                    maxValue:
                                                        selectedParameter.maximalValue ||
                                                        selectedParameterOptions.maximalValue,
                                                    groupName: selectedParameterOptions.name,
                                                },
                                            )}
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
                                            {selectedParameterOptions.unit?.name &&
                                                `\xa0${selectedParameterOptions.unit.name}`}
                                            <SelectedParametersIcon />
                                        </SelectedParametersListItem>
                                    )}
                                    {selectedParameterValues &&
                                        selectedParameterValues.map((selectedValue) => (
                                            <SelectedParametersListItem
                                                key={selectedValue.uuid}
                                                ariaLabel={t(
                                                    'Remove parameter {{ value }} from group {{ groupName }}',
                                                    {
                                                        value: selectedValue.text,
                                                        groupName: selectedParameterOptions.name,
                                                    },
                                                )}
                                                onClick={() =>
                                                    updateFilterParametersQuery(
                                                        selectedParameter.parameter,
                                                        selectedValue.uuid,
                                                    )
                                                }
                                            >
                                                {selectedValue.rgbHex && selectedValue.rgbHex !== '' && (
                                                    <div
                                                        className="border-icon-default mr-2 size-4 rounded-sm border"
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

                        <button
                            aria-label={t('Clear all active filters')}
                            className="font-secondary text-link-default hover:text-link-hovered cursor-pointer rounded-sm text-sm font-semibold underline"
                            tabIndex={0}
                            type="button"
                            onClick={resetAllFilterQueries}
                        >
                            {t('Clear all')}
                        </button>
                    </div>
                </AnimateCollapseDiv>
            )}
        </AnimatePresence>
    );
};

const SelectedParametersIcon: FC = () => (
    <RemoveBoldIcon className="group-hover:text-icon-inverted text-icon-less ml-2 w-3 cursor-pointer" />
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
