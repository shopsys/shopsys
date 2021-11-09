import { FC, useEffect, useState } from 'react';
import { FilterFormType, FilterOptionsType } from './types';
import { FormProvider, useFieldArray } from 'react-hook-form';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import FilterGroup from './FilterGroup';
import { FilterStyled } from './Filter.style';
import SelectedParameters from './SelectedParameters';
import { userActions } from 'redux/slices/user';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type FilterProps = {
    productFilterOptions: FilterOptionsType;
};

const Filter: FC<FilterProps> = (props) => {
    const t = useTypedTranslationFunction();
    const dispatch = useShopsysDispatch();
    const parametersFilterState = useShopsysSelector((state) => state.user.parametersFilter);
    const formProviderMethods = useShopsysForm(undefined, {
        brands: [],
        flags: [],
        parameters: [],
        onlyInStock: false,
        minimalPrice: props.productFilterOptions.minimalPrice,
        maximalPrice: props.productFilterOptions.maximalPrice,
    });
    const control = formProviderMethods.control;
    const { append } = useFieldArray({ control, name: 'parameters' });
    const [isNotFirstRender, setIsNotFirstRender] = useState(false);

    const updateUrlWithCurrentFilter = (object) => {
        const queryParams = new URLSearchParams(window.location.search);

        if (
            object.brands.length === 0 &&
            object.flags.length === 0 &&
            object.parameters.length === 0 &&
            object.onlyInStock === false &&
            object.minimalPrice === props.productFilterOptions.minimalPrice &&
            object.maximalPrice === props.productFilterOptions.maximalPrice
        ) {
            queryParams.delete('filter');
        } else {
            queryParams.set('filter', JSON.stringify(object));
        }

        let newState = document.location.pathname;
        if (queryParams.toString().length > 0) {
            newState = '?' + queryParams.toString();
        }

        history.replaceState(history.state, document.title, newState);
    };

    const onSubmit = (data: FilterFormType) => {
        const filterParameters = {
            brands: data.brands,
            flags: data.flags,
            parameters: data.parameters,
            onlyInStock: data.onlyInStock,
            minimalPrice: data.minimalPrice,
            maximalPrice: data.maximalPrice,
        };

        updateUrlWithCurrentFilter(data);
        dispatch(userActions.setParametersFilter({ ...filterParameters }));
    };

    useEffect(() => {
        const parametersToAppend = [];

        if (parametersFilterState.brands.length > 0) {
            formProviderMethods.setValue('brands', [...parametersFilterState.brands]);
        }
        if (parametersFilterState.flags.length > 0) {
            formProviderMethods.setValue('flags', [...parametersFilterState.flags]);
        }
        if (parametersFilterState.parameters.length > 0) {
            parametersFilterState.parameters.map((item) => {
                parametersToAppend.push({ parameter: item.parameter, values: [...item.values] });
            });
            append(parametersToAppend);
        }
        formProviderMethods.setValue('onlyInStock', parametersFilterState.onlyInStock);
        parametersFilterState.minimalPrice !== null &&
            formProviderMethods.setValue('minimalPrice', parametersFilterState.minimalPrice);
        parametersFilterState.maximalPrice !== null &&
            formProviderMethods.setValue('maximalPrice', parametersFilterState.maximalPrice);
    }, []);

    useEffect(() => {
        if (isNotFirstRender) {
            formProviderMethods.reset();
            formProviderMethods.setValue('minimalPrice', props.productFilterOptions.minimalPrice);
            formProviderMethods.setValue('maximalPrice', props.productFilterOptions.maximalPrice);
            formProviderMethods.handleSubmit(onSubmit(formProviderMethods.getValues()));
        } else {
            setIsNotFirstRender(true);
        }
    }, [props.slug]);

    return (
        <>
            <FormProvider {...formProviderMethods}>
                <SelectedParameters productFilterOptions={props.productFilterOptions} onSubmit={onSubmit} />
                <FilterStyled>
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

                        {props.productFilterOptions.flags !== null && (
                            <FilterGroup
                                title={t('Flags')}
                                filterField="flags"
                                type="checkbox"
                                data={props.productFilterOptions.flags}
                                isOpen={true}
                                onSubmit={onSubmit}
                            />
                        )}

                        {props.productFilterOptions.brands !== null && (
                            <FilterGroup
                                title={t('Brands')}
                                filterField="brands"
                                type="checkbox"
                                data={props.productFilterOptions.brands}
                                isOpen={true}
                                onSubmit={onSubmit}
                            />
                        )}

                        {props.productFilterOptions.parameters.map((parametersItem, index) => (
                            <FilterGroup
                                key={index}
                                filterField="parameters"
                                parameterParentUuid={parametersItem.uuid}
                                title={parametersItem.name}
                                type={parametersItem.type}
                                data={parametersItem.items}
                                isOpen={true}
                                onSubmit={onSubmit}
                            />
                        ))}
                    </form>
                </FilterStyled>
            </FormProvider>
        </>
    );
};

/* @component */
export default Filter;
