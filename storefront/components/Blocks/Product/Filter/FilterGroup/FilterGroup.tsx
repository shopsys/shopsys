import { Controller, SubmitHandler, useFieldArray, useFormContext } from 'react-hook-form';
import { FC, Fragment, useState } from 'react';
import { FilterFormType, ParameterItemsType } from 'components/Blocks/Product/Filter/types';
import {
    FilterGroupArrowStyled,
    FilterGroupColorStyled,
    FilterGroupContentItemStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from './FilterGroup.style';
import Checkbox from 'components/Forms/Checkbox';
import CheckboxColor from 'components/Forms/CheckboxColor';
import { clearArrayFromEmptyValue } from 'utils/Filter/ClearArrayFromEmptyValue';
import RangeSlider from 'components/Basic/RangeSlider';

type FilterGroupProps = {
    /**
     * Changes filter items to its elements
     */
    type: 'checkbox' | 'colorPicker' | 'price' | 'checkboxInStock' | string;
    /**
     * Group title with arrow
     */
    title: string;
    /**
     * Sets if group is default open
     */
    isOpen?: boolean;
    /**
     * Special type of parameters for separation
     */
    filterField?: 'flags' | 'parameters' | 'brands';
    /**
     * Index of parent currently specially for parameters
     */
    parentIndex?: number;
    /**
     * Uuid of parameters
     */
    uuid?: string;
    /**
     * Parameters data of product filter
     */
    data?: ParameterItemsType[];
    /**
     * Count of inStock parameter
     */
    inStockCount?: number;
    /**
     * Maximal price of price slider
     */
    maximalPrice?: number;
    /**
     * Minimal price of price slider
     */
    minimalPrice?: number;
    /**
     * Function for submit form
     */
    onSubmit: (data: FilterFormType) => SubmitHandler<FilterFormType>;
};

const FilterGroup: FC<FilterGroupProps> = (props) => {
    const [isGroupOpen, setIsGroupOpen] = useState(props.isOpen);
    const formProviderMethods = useFormContext();
    const control = formProviderMethods.control;
    const { append, remove } = useFieldArray({ control, name: 'parameters' });

    const checkIfParameterIsActive = (itemUuid, parentUuid) => {
        const parametersValues = formProviderMethods.getValues().parameters;
        const findedExistingParent = parametersValues.find((item) => item.parameter === parentUuid);
        const findedExistingValue = parametersValues.find((item) =>
            item.values.find((itemChild) => itemChild === itemUuid),
        );

        return findedExistingValue !== undefined && findedExistingParent !== undefined && true;
    };

    const onChangeInStockCheckbox = (field) => {
        formProviderMethods.setValue(field?.name, !field?.value);
        formProviderMethods.handleSubmit(props.onSubmit(formProviderMethods.getValues()));
    };

    const onChangeCheckbox = (value, filterField) => {
        const formValues = formProviderMethods.getValues()[filterField];
        const values = [...formValues];
        const valueIndex = values.indexOf(value);

        if (values.includes(value)) {
            values.splice(valueIndex, 1);
            formProviderMethods.setValue(filterField, [...values]);
        } else {
            formProviderMethods.setValue(filterField, [...values, ...value.split()]);
        }

        formProviderMethods.handleSubmit(props.onSubmit(formProviderMethods.getValues()));
    };

    const onChangeParametersCheckbox = (value, parameterParentUuid) => {
        const parametersValues = formProviderMethods.getValues().parameters;
        const findedExistingValue = parametersValues.find((item) => item.parameter === parameterParentUuid);

        if (findedExistingValue !== undefined) {
            const indexOfParameter = parametersValues.findIndex((item) => item.parameter === parameterParentUuid);

            if (findedExistingValue.values.includes(value)) {
                const arrayOfValues = [...findedExistingValue.values];
                const indexOfRemovedValue = arrayOfValues.indexOf(value);
                arrayOfValues.splice(indexOfRemovedValue, 1);
                remove(indexOfParameter);
                append({ parameter: parameterParentUuid, values: [...arrayOfValues] });
                clearArrayFromEmptyValue(formProviderMethods);
            } else {
                const arrayOfValues = [...findedExistingValue.values, ...value.split()];
                formProviderMethods.setValue(`parameters[${indexOfParameter}].values`, [...arrayOfValues]);
            }
        } else {
            append({ parameter: parameterParentUuid, values: [...value.split()] });
            clearArrayFromEmptyValue(formProviderMethods);
        }

        const updatedFormParametersValues = formProviderMethods.getValues().parameters;
        const parameterWithoutValue = updatedFormParametersValues.find((item) => item.values === undefined);
        if (parameterWithoutValue !== undefined) {
            const indexOfParameterWithoutValue = updatedFormParametersValues.findIndex(
                (item) => item.parameter === parameterWithoutValue.parameter,
            );
            remove(indexOfParameterWithoutValue);
        }

        formProviderMethods.handleSubmit(props.onSubmit(formProviderMethods.getValues()));
    };

    const handleGroupClick = () => {
        setIsGroupOpen(!isGroupOpen);
    };

    return (
        <>
            <FilterGroupStyled>
                <FilterGroupTitleStyled onClick={handleGroupClick}>
                    {props.title}
                    <FilterGroupArrowStyled icon="Arrow" isOpen={isGroupOpen} />
                </FilterGroupTitleStyled>
                <FilterGroupContentStyled isOpen={isGroupOpen}>
                    {renderItems(
                        props.type,
                        props.onSubmit,
                        props.uuid,
                        props.data,
                        props.filterField,
                        props.minimalPrice,
                        props.maximalPrice,
                        props.inStockCount,
                        onChangeCheckbox,
                        formProviderMethods,
                        onChangeInStockCheckbox,
                        checkIfParameterIsActive,
                        onChangeParametersCheckbox,
                    )}
                </FilterGroupContentStyled>
            </FilterGroupStyled>
        </>
    );
};

/**
 * TODO PRG: join real data from API - Product filter items
 */
const renderItems = (
    type: 'checkbox' | 'colorPicker' | 'price' | 'checkboxInStock' | string,
    onSubmit: (data: FilterFormType) => SubmitHandler<FilterFormType>,
    parameterParentUuid,
    data?: ParameterItemsType[],
    filterField?: 'flags' | 'parameters' | 'brands',
    minimalPrice?: number,
    maximalPrice?: number,
    inStockCount?: number,
    onChangeCheckbox,
    formProviderMethods,
    onChangeInStockCheckbox,
    checkIfParameterIsActive,
    onChangeParametersCheckbox,
) => {
    switch (type) {
        case 'checkbox':
            return (
                <>
                    {filterField === 'parameters'
                        ? data !== undefined &&
                          data.map((dataItem: ParameterItemsType) => (
                              <FilterGroupContentItemStyled
                                  key={dataItem.item.uuid}
                                  isDisabled={dataItem.count === 0}
                                  isActive={checkIfParameterIsActive(dataItem.item.uuid, parameterParentUuid)}
                              >
                                  <Checkbox
                                      name={dataItem.item.uuid}
                                      id={dataItem.item.uuid + parameterParentUuid}
                                      label={dataItem.item.name}
                                      uuid={dataItem.item.uuid}
                                      count={dataItem.count}
                                      onSubmit={onSubmit}
                                      checked={checkIfParameterIsActive(dataItem.item.uuid, parameterParentUuid)}
                                      onChange={() =>
                                          onChangeParametersCheckbox(dataItem.item.uuid, parameterParentUuid)
                                      }
                                  />
                              </FilterGroupContentItemStyled>
                          ))
                        : data !== undefined &&
                          data.map((dataItem: ParameterItemsType) => (
                              <FilterGroupContentItemStyled
                                  key={dataItem.item.uuid}
                                  isDisabled={dataItem.count === 0}
                                  isActive={formProviderMethods.getValues()[filterField].includes(dataItem.item.uuid)}
                              >
                                  <Checkbox
                                      name={dataItem.item.uuid}
                                      id={dataItem.item.uuid}
                                      label={dataItem.item.name}
                                      uuid={dataItem.item.uuid}
                                      onSubmit={onSubmit}
                                      count={dataItem.count}
                                      checked={formProviderMethods
                                          .getValues()
                                          [filterField].includes(dataItem.item.uuid)}
                                      onChange={() => onChangeCheckbox(dataItem.item.uuid, filterField)}
                                  />
                              </FilterGroupContentItemStyled>
                          ))}
                </>
            );
        case 'checkboxInStock':
            return (
                <Controller
                    name="onlyInStock"
                    render={({ field }) => (
                        <Checkbox
                            name="onlyInStock"
                            id="onlyInStock"
                            label="Skladem"
                            fieldRef={field}
                            count={inStockCount}
                            onClick={() => onChangeInStockCheckbox(field)}
                        />
                    )}
                />
            );
        case 'colorPicker':
            return (
                <FilterGroupColorStyled>
                    {data !== undefined &&
                        data.map((dataItem: ParameterItemsType) => (
                            <Fragment key={dataItem.item.uuid}>
                                <CheckboxColor
                                    name={dataItem.item.uuid}
                                    id={dataItem.item.uuid + parameterParentUuid}
                                    label={dataItem.item.name}
                                    uuid={dataItem.item.uuid}
                                    count={dataItem.count}
                                    isDisabled={dataItem.count === 0}
                                    isActive={checkIfParameterIsActive(dataItem.item.uuid, parameterParentUuid)}
                                    bgColor={dataItem.rgbHex}
                                    onSubmit={onSubmit}
                                    checked={checkIfParameterIsActive(dataItem.item.uuid, parameterParentUuid)}
                                    onChange={() => onChangeParametersCheckbox(dataItem.item.uuid, parameterParentUuid)}
                                />
                            </Fragment>
                        ))}
                </FilterGroupColorStyled>
            );
        case 'price':
            return (
                <>
                    <RangeSlider
                        min={minimalPrice !== undefined ? minimalPrice : 0}
                        max={maximalPrice !== undefined ? maximalPrice : 0}
                        delay={300}
                        onSubmit={onSubmit}
                    />
                </>
            );
    }
    throw new Error('Wrong type provided for Product filter group.' + type);
};

/* @component */
export default FilterGroup;
