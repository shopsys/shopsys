import { SelectedParametersList, SelectedParametersListItem, SelectedParametersName } from './FilterElements';
import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { ProductFilterOptionsFragmentApi } from 'graphql/generated';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useQueryParams } from 'hooks/useQueryParams';

const TEST_IDENTIFIER = 'blocks-product-filter-selectedparameters';

type SelectedParametersProps = {
    filterOptions: ProductFilterOptionsFragmentApi;
};

export const SelectedParameters: FC<SelectedParametersProps> = ({ filterOptions }) => {
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();

    const {
        filter,
        updateFilterInStock,
        updateFilterPrices,
        updateFilterBrands,
        updateFilterFlags,
        updateFilterParameters,
        resetAllFilters,
    } = useQueryParams();

    if (!filter) {
        return null;
    }

    const { onlyInStock, minimalPrice, maximalPrice, brands, flags, parameters } = filter;

    const isOnlyInStock = !!onlyInStock;

    const checkedBrands = brands?.map((checkedBrandUuid) =>
        filterOptions.brands?.find((brandOption) => brandOption.brand.uuid === checkedBrandUuid),
    );

    const checkedFlags = flags?.map((checkedFlagUuid) =>
        filterOptions.flags?.find((brandOption) => brandOption.flag.uuid === checkedFlagUuid),
    );

    const isWithMinimalPrice = minimalPrice !== undefined;
    const isWithMaximalPrice = maximalPrice !== undefined;

    return (
        <div className="z-aboveOverlay rounded-xl py-4 vl:z-[0]" data-testid={TEST_IDENTIFIER}>
            <Heading type="h4" className="uppercase">
                {t('Selected filters')}
            </Heading>
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
                                            onClick={() => updateFilterBrands(checkedBrand.brand.uuid)}
                                        />
                                    </SelectedParametersListItem>
                                ),
                        )}
                    </SelectedParametersList>
                )}

                {!!checkedFlags?.length && (
                    <SelectedParametersList>
                        <SelectedParametersName>{t('Flags')}:</SelectedParametersName>
                        {checkedFlags.map(
                            (checkedFlag) =>
                                !!checkedFlag && (
                                    <SelectedParametersListItem key={checkedFlag.flag.uuid}>
                                        {checkedFlag.flag.name}
                                        <SelectedParametersIcon
                                            onClick={() => updateFilterFlags(checkedFlag.flag.uuid)}
                                        />
                                    </SelectedParametersListItem>
                                ),
                        )}
                    </SelectedParametersList>
                )}

                {parameters?.map((filterSelectedParameter) => {
                    const selectedParameter = filterOptions.parameters?.find(
                        (parameter) => parameter.uuid === filterSelectedParameter.parameter,
                    );

                    const isSliderParameter = selectedParameter?.__typename === 'ParameterSliderFilterOption';
                    const isColorParameter = selectedParameter?.__typename === 'ParameterColorFilterOption';
                    const isCheckBoxParameter = selectedParameter?.__typename === 'ParameterCheckboxFilterOption';

                    const selectedColorParameterOptions =
                        // hack typescript because it is confused about filtering shared types
                        isColorParameter
                            ? (selectedParameter.values as { uuid: string; text: string }[]).filter((option) =>
                                  filterSelectedParameter.values?.includes(option.uuid),
                              )
                            : undefined;

                    const selectedCheckBoxParametersOptions =
                        // hack typescript because it is confused about filtering shared types
                        isCheckBoxParameter
                            ? selectedParameter.values.filter((option) =>
                                  filterSelectedParameter.values?.includes(option.uuid),
                              )
                            : undefined;

                    return (
                        <SelectedParametersList key={selectedParameter?.uuid}>
                            <SelectedParametersName>{selectedParameter?.name}:</SelectedParametersName>
                            {isSliderParameter ? (
                                <SelectedParametersListItem key={selectedParameter.uuid}>
                                    {filterSelectedParameter.minimalValue && (
                                        <>
                                            <span>{t('from')}&nbsp;</span>
                                            {selectedParameter.minimalValue}
                                            {selectedParameter.unit?.name !== undefined
                                                ? `\xa0${selectedParameter.unit.name}`
                                                : ''}
                                            {filterSelectedParameter.maximalValue && ' '}
                                        </>
                                    )}
                                    {filterSelectedParameter.maximalValue && (
                                        <>
                                            <span>{t('to')}&nbsp;</span>
                                            {selectedParameter.maximalValue}
                                            {selectedParameter.unit?.name !== undefined
                                                ? `\xa0${selectedParameter.unit.name}`
                                                : ''}
                                        </>
                                    )}
                                    <SelectedParametersIcon
                                        onClick={() => updateFilterParameters(selectedParameter.uuid, undefined)}
                                    />
                                </SelectedParametersListItem>
                            ) : (
                                (selectedColorParameterOptions || selectedCheckBoxParametersOptions)?.map(
                                    (selectedOption, index) => (
                                        <SelectedParametersListItem
                                            key={selectedOption.uuid}
                                            dataTestId={TEST_IDENTIFIER + index}
                                        >
                                            {selectedOption.text}
                                            <SelectedParametersIcon
                                                onClick={() =>
                                                    updateFilterParameters(selectedParameter?.uuid, selectedOption.uuid)
                                                }
                                                dataTestId={TEST_IDENTIFIER + 'remove-' + index}
                                            />
                                        </SelectedParametersListItem>
                                    ),
                                )
                            )}
                        </SelectedParametersList>
                    );
                })}

                {isOnlyInStock && (
                    <SelectedParametersList>
                        <SelectedParametersName>{t('Availability')}:</SelectedParametersName>
                        <SelectedParametersListItem>
                            {t('Only goods in stock')}
                            <SelectedParametersIcon onClick={() => updateFilterInStock(false)} />
                        </SelectedParametersListItem>
                    </SelectedParametersList>
                )}

                {(isWithMinimalPrice || isWithMaximalPrice) && (
                    <SelectedParametersList>
                        <SelectedParametersName>{t('Price')}:</SelectedParametersName>
                        <SelectedParametersListItem>
                            {isWithMinimalPrice && (
                                <>
                                    <span>{t('from')}&nbsp;</span>
                                    {formatPrice(minimalPrice)}
                                    {isWithMaximalPrice ? ' ' : ''}
                                </>
                            )}
                            {isWithMaximalPrice && (
                                <>
                                    <span>{t('to')}&nbsp;</span>
                                    {formatPrice(maximalPrice)}
                                </>
                            )}
                            <SelectedParametersIcon
                                onClick={() => {
                                    updateFilterPrices({ maximalPrice: undefined, minimalPrice: undefined });
                                }}
                            />
                        </SelectedParametersListItem>
                    </SelectedParametersList>
                )}
            </div>
            <div className="flex cursor-pointer items-center text-sm text-greyLight" onClick={resetAllFilters}>
                <div className="font-bold uppercase">{t('Clear all')}</div>
                <Icon iconType="icon" icon="Remove" className="ml-2 cursor-pointer text-greenLight" />
            </div>
        </div>
    );
};

const SelectedParametersIcon: FC<{ onClick: () => void }> = ({ onClick, dataTestId }) => (
    <Icon
        iconType="icon"
        icon="RemoveThin"
        onClick={onClick}
        className="ml-3 w-3 cursor-pointer"
        data-testid={dataTestId}
    />
);
