import { FilterFormType, FilterFormValuesType, FilterOptionsType, ItemsType } from './types';
import { FC } from 'react';
import FilterGroup from './FilterGroup';
import { FilterStyled } from './Filter.style';
import { FormProvider } from 'react-hook-form';
import { userActions } from 'redux/slices/user';
import { useShopsysDispatch } from 'redux/main';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type FilterProps = {
    productFilterOptions: FilterOptionsType;
};

const Filter: FC<FilterProps> = (props) => {
    const t = useTypedTranslationFunction();
    const dispatch = useShopsysDispatch();

    const mapFilterItems = (values: ItemsType[]) => {
        const filterItems: FilterFormValuesType = {};

        values.map((filterItem) => (filterItems[filterItem.item.uuid] = false));

        return [{ values: filterItems }];
    };

    const mapFilterParameters = props.productFilterOptions.parameters.map((parameterItem) => {
        const parameterValues: FilterFormValuesType = {};
        const parameterUuid = parameterItem.uuid;

        parameterItem.items.map((parameterItemsValue) => (parameterValues[parameterItemsValue.item.uuid] = false));

        return { parameter: parameterUuid, values: parameterValues };
    });

    const formProviderMethods = useShopsysForm(undefined, {
        minimalPrice: props.productFilterOptions.minimalPrice,
        maximalPrice: props.productFilterOptions.maximalPrice,
        onlyInStock: false,
        brands: mapFilterItems(props.productFilterOptions.brands),
        flags: mapFilterItems(props.productFilterOptions.flags),
        parameters: mapFilterParameters,
    });

    const onSubmit = (data: FilterFormType) => {
        const getCheckedUuid = (itemValues: FilterFormValuesType) => {
            const objectToArray = Object.entries(itemValues);
            const onlyTrueArrays = objectToArray.filter((item) => item[1] === true);
            const arraysOfUuid = onlyTrueArrays.map((item) => item[0]);
            return arraysOfUuid;
        };

        const checkedParametersUuid = data.parameters
            .map((item) => {
                const parameterItem = { values: {}, parameter: item.parameter };
                const checkedUuid = getCheckedUuid(item.values);

                if (checkedUuid.length > 0) {
                    parameterItem.values = checkedUuid;

                    return parameterItem;
                }

                return undefined;
            })
            .filter((item) => item !== undefined);

        const filterParameters = {
            brands: getCheckedUuid(data.brands[0].values),
            flags: getCheckedUuid(data.flags[0].values),
            minimalPrice: data.minimalPrice,
            maximalPrice: data.maximalPrice,
            onlyInStock: data.onlyInStock,
            parameters: checkedParametersUuid,
        };

        dispatch(userActions.setParametersFilter({ ...filterParameters }));
    };

    return (
        <FilterStyled>
            <FormProvider {...formProviderMethods}>
                <form>
                    <FilterGroup
                        title={t('Price')}
                        type="price"
                        minimalPrice={props.productFilterOptions.minimalPrice}
                        maximalPrice={props.productFilterOptions.maximalPrice}
                        isOpen={true}
                        onSubmit={onSubmit}
                    />

                    <FilterGroup
                        title={t('Availability')}
                        type="checkboxInStock"
                        inStockCount={props.productFilterOptions.inStock}
                        isOpen={true}
                        onSubmit={onSubmit}
                    />

                    <FilterGroup
                        title={t('Flags')}
                        filterField="flags"
                        type="checkbox"
                        data={props.productFilterOptions.flags}
                        isOpen={true}
                        onSubmit={onSubmit}
                    />

                    <FilterGroup
                        title={t('Brands')}
                        filterField="brands"
                        type="checkbox"
                        data={props.productFilterOptions.brands}
                        isOpen={true}
                        onSubmit={onSubmit}
                    />

                    {props.productFilterOptions.parameters.map((parametersItem, index) => (
                        <FilterGroup
                            key={index}
                            filterField="parameters"
                            parentIndex={index}
                            uuid={parametersItem.uuid}
                            title={parametersItem.name}
                            type={parametersItem.type}
                            data={parametersItem.items}
                            isOpen={true}
                            onSubmit={onSubmit}
                        />
                    ))}
                </form>
            </FormProvider>
        </FilterStyled>
    );
};

/* @component */
export default Filter;
