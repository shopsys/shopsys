import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { ColorPreview } from 'components/Basic/ColorPreview/ColorPreview';
import { Flag } from 'components/Basic/Flag/Flag';
import { RemoveBoldIcon } from 'components/Basic/Icon/RemoveBoldIcon';
import { TIDs } from 'cypress/tids';
import { AnimatePresence } from 'framer-motion';
import { TypeProductFilterOptionsFragment } from 'graphql/requests/productFilterOptions/fragments/ProductFilterOptionsFragment.generated';
import { DefaultProductFiltersMapType } from 'store/slices/createSeoCategorySlice';
import { useSessionStore } from 'store/useSessionStore';
import { FilterOptionsParameterUrlQueryType } from 'types/productFilter';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useUpdateFilterQuery } from 'utils/queryParams/useUpdateFilterQuery';
import { SelectedParametersList, SelectedParametersListItem, SelectedParametersName } from './FilterElements';

export type FilterSelectedParametersProps = {
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

    const checkedBrands = currentFilter?.brands?.map((checkedBrandUuid) =>
        filterOptions.brands?.find((brandOption) => brandOption.brand.uuid === checkedBrandUuid),
    );

    const checkedFlags = getCheckedFlags(defaultProductFiltersMap, filterOptions.flags, currentFilter?.flags);

    const selectedParameters = getSelectedParameters(defaultProductFiltersMap, currentFilter?.parameters);

    return (
        <AnimatePresence initial={false}>
            {!currentFilter && !getHasDefaultFilters(defaultProductFiltersMap) ? null : (
                <AnimateCollapseDiv className="block!" keyName="selected-parameters">
                    <div className="mt-5 vl:mt-0 vl:mb-5" data-tid={TIDs.selected_filters}>
                        <p className="h6 mb-5 vl:mb-2">{t('Selected filters')}</p>

                        <div className="flex flex-wrap items-center gap-y-2">
                            {!!currentFilter?.onlyInStock && (
                                <SelectedParametersList keyName="filter-only-in-stock">
                                    <SelectedParametersName>{t('Availability')}</SelectedParametersName>
                                    <SelectedParametersListItem
                                        ariaLabel={t('Remove filter Availability only goods in stock', {
                                            ns: 'accessibility',
                                        })}
                                        onClick={() => updateFilterInStockQuery(false)}
                                    >
                                        {t('Only goods in stock')}
                                        <SelectedParametersIcon />
                                    </SelectedParametersListItem>
                                </SelectedParametersList>
                            )}

                            {(currentFilter?.minimalPrice !== undefined ||
                                currentFilter?.maximalPrice !== undefined) && (
                                <SelectedParametersList keyName="filter-minmax-price">
                                    <SelectedParametersName>{t('Price')}</SelectedParametersName>
                                    <SelectedParametersListItem
                                        ariaLabel={t(
                                            'Remove filter Price from {{ minimalPrice }} to {{ maximalPrice }}',
                                            {
                                                ns: 'accessibility',
                                                minimalPrice: formatPrice(currentFilter.minimalPrice ?? 0),
                                                maximalPrice: formatPrice(currentFilter.maximalPrice ?? 0),
                                            },
                                        )}
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
                                                        ns: 'accessibility',
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
                                                ns: 'accessibility',
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
                                                  colorIcon?: {
                                                      url: string;
                                                      anchorText: string;
                                                  };
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
                                                        ns: 'accessibility',
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
                                                    updateFilterParametersQuery(
                                                        selectedParameterOptions.uuid,
                                                        undefined,
                                                    )
                                                }
                                            >
                                                <span>{t('from')}&nbsp;</span>
                                                {selectedParameter.minimalValue ||
                                                    selectedParameterOptions.minimalValue}
                                                {!!selectedParameterOptions.unit?.name &&
                                                    `\xa0${selectedParameterOptions.unit.name}`}
                                                <span>&nbsp;{t('to')}&nbsp;</span>
                                                {selectedParameter.maximalValue ||
                                                    selectedParameterOptions.maximalValue}
                                                {selectedParameterOptions.unit?.name &&
                                                    `\xa0${selectedParameterOptions.unit.name}`}
                                                <SelectedParametersIcon />
                                            </SelectedParametersListItem>
                                        )}
                                        {selectedParameterValues?.map((selectedValue) => (
                                            <SelectedParametersListItem
                                                key={selectedValue.uuid}
                                                ariaLabel={t(
                                                    'Remove parameter {{ value }} from group {{ groupName }}',
                                                    {
                                                        ns: 'accessibility',
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
                                                <ColorPreview
                                                    className="mr-2"
                                                    colorIcon={selectedValue.colorIcon}
                                                    rgbHex={selectedValue.rgbHex}
                                                />
                                                {selectedValue.text}
                                                <SelectedParametersIcon />
                                            </SelectedParametersListItem>
                                        ))}
                                    </SelectedParametersList>
                                );
                            })}

                            <button
                                aria-label={t('Clear all active filters', { ns: 'accessibility' })}
                                className="cursor-pointer rounded-sm font-secondary font-semibold text-link-default text-sm underline hover:text-link-hovered"
                                data-tid={TIDs.clear_all_filters_button}
                                tabIndex={0}
                                type="button"
                                onClick={resetAllFilterQueries}
                            >
                                {t('Clear all')}
                            </button>
                        </div>
                    </div>
                </AnimateCollapseDiv>
            )}
        </AnimatePresence>
    );
};

const SelectedParametersIcon: FC = () => (
    <RemoveBoldIcon className="ml-2 w-3 cursor-pointer text-icon-less group-hover:text-icon-inverted" />
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
