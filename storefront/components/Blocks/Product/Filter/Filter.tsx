import { FC, useEffect, useState } from 'react';
import { FilterFormParametersType, FilterFormType, FilterOptionsType } from './types';
import { FormProvider, useFieldArray } from 'react-hook-form';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import FilterGroup from './FilterGroup';
import FilterGroupInStock from './FilterGroupInStock';
import FilterGroupParameters from './FilterGroupParameters';
import FilterGroupPrice from './FilterGroupPrice';
import { FilterStyled } from './Filter.style';
import SelectedParameters from './SelectedParameters';
import { userActions } from 'redux/slices/user';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type FilterProps = {
    productFilterOptions: FilterOptionsType;
    slug: string;
};

const Filter: FC<FilterProps> = (props) => {
    const t = useTypedTranslationFunction();
    const dispatch = useShopsysDispatch();
    const parametersFilterState = useShopsysSelector((state) => state.user.parametersFilter);
    const formProviderMethods = useShopsysForm<FilterFormType>(undefined, {
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

    const updateUrlWithCurrentFilter = (object: FilterFormType) => {
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
        updateUrlWithCurrentFilter(data);
        dispatch(userActions.setParametersFilter({ ...data }));
    };

    // this useEffect triggered only on the first render
    useEffect(() => {
        const parametersToAppend: FilterFormParametersType[] = [];

        if (parametersFilterState.brands.length > 0) {
            formProviderMethods.setValue('brands', [...parametersFilterState.brands]);
        }
        if (parametersFilterState.flags.length > 0) {
            formProviderMethods.setValue('flags', [...parametersFilterState.flags]);
        }
        if (parametersFilterState.parameters.length > 0) {
            parametersFilterState.parameters.map((item) => {
                return parametersToAppend.push({ parameter: item.parameter, values: [...item.values] });
            });
            append(parametersToAppend);
        }
        formProviderMethods.setValue('onlyInStock', parametersFilterState.onlyInStock);
        parametersFilterState.minimalPrice !== null &&
            formProviderMethods.setValue('minimalPrice', parametersFilterState.minimalPrice);
        parametersFilterState.maximalPrice !== null &&
            formProviderMethods.setValue('maximalPrice', parametersFilterState.maximalPrice);
    }, []);

    // this useEffect triggered only when is prop slug changed but never on the first render
    useEffect(() => {
        if (isNotFirstRender) {
            formProviderMethods.reset();
            formProviderMethods.setValue('minimalPrice', props.productFilterOptions.minimalPrice);
            formProviderMethods.setValue('maximalPrice', props.productFilterOptions.maximalPrice);
            onSubmit(formProviderMethods.getValues());
        } else {
            setIsNotFirstRender(true);
        }
    }, [props.slug]);

    return (
        <FormProvider {...formProviderMethods}>
            <SelectedParameters productFilterOptions={props.productFilterOptions} onSubmit={onSubmit} />
            <FilterStyled>
                <form>
                    <FilterGroupPrice
                        title={t('Price')}
                        minimalPrice={props.productFilterOptions.minimalPrice}
                        maximalPrice={props.productFilterOptions.maximalPrice}
                        isOpen={true}
                        onSubmit={onSubmit}
                    />

                    <FilterGroupInStock
                        title={t('Availability')}
                        inStockCount={props.productFilterOptions.inStock}
                        isOpen={true}
                        onSubmit={onSubmit}
                    />

                    {props.productFilterOptions.flags !== null && (
                        <FilterGroup
                            title={t('Flags')}
                            filterField="flags"
                            data={props.productFilterOptions.flags}
                            isOpen={true}
                            onSubmit={onSubmit}
                        />
                    )}

                    {props.productFilterOptions.brands !== null && (
                        <FilterGroup
                            title={t('Brands')}
                            filterField="brands"
                            data={props.productFilterOptions.brands}
                            isOpen={true}
                            onSubmit={onSubmit}
                        />
                    )}

                    {props.productFilterOptions.parameters.map((parametersItem, index) => (
                        <FilterGroupParameters
                            key={index}
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
    );
};

/* @component */
export default Filter;
