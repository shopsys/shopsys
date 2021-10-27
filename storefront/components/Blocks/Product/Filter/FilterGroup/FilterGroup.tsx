import { Controller, SubmitHandler } from 'react-hook-form';
import { FC, Fragment, useState } from 'react';
import { FilterFormType, ParameterItemsType } from 'components/Blocks/Product/Filter/types';
import {
    FilterGroupArrowStyled,
    FilterGroupColorStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from './FilterGroup.style';
import FilterCheckbox from 'components/Blocks/Product/Filter/FilterCheckbox';
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
    const parentIndex = props.parentIndex !== undefined ? props.parentIndex : 0;

    const handleGroupClick = () => {
        setIsGroupOpen(!isGroupOpen);
    };

    return (
        <FilterGroupStyled>
            <FilterGroupTitleStyled onClick={handleGroupClick}>
                {props.title}
                <FilterGroupArrowStyled icon="Arrow" isOpen={isGroupOpen} />
            </FilterGroupTitleStyled>
            <FilterGroupContentStyled isOpen={isGroupOpen}>
                {renderItems(
                    props.type,
                    parentIndex,
                    props.title,
                    props.onSubmit,
                    props.data,
                    props.filterField,
                    props.minimalPrice,
                    props.maximalPrice,
                )}
            </FilterGroupContentStyled>
        </FilterGroupStyled>
    );
};

/**
 * TODO PRG: join real data from API - Product filter items
 */
const renderItems = (
    type: 'checkbox' | 'colorPicker' | 'price' | 'checkboxInStock' | string,
    parentIndex: number,
    filterGroupTitle: string,
    onSubmit: (data: FilterFormType) => SubmitHandler<FilterFormType>,
    data?: ParameterItemsType[],
    filterField?: 'flags' | 'parameters' | 'brands',
    minimalPrice?: number,
    maximalPrice?: number,
    inStockCount?: number,
) => {
    switch (type) {
        case 'checkbox':
            return (
                <>
                    {data !== undefined &&
                        data.map((dataItem: ParameterItemsType, index: number) => (
                            <Fragment key={index}>
                                <Controller
                                    name={`${filterField}[${parentIndex}].values.${dataItem.item.uuid}`}
                                    render={({ field }) => (
                                        <FilterCheckbox
                                            isDisabled={dataItem.count === 0}
                                            isActive={field.value}
                                            id={dataItem.item.name + filterGroupTitle}
                                            label={dataItem.item.name}
                                            field={field}
                                            count={dataItem.count}
                                            onSubmit={onSubmit}
                                        />
                                    )}
                                />
                            </Fragment>
                        ))}
                </>
            );
        case 'checkboxInStock':
            return (
                <Controller
                    name="onlyInStock"
                    render={({ field }) => (
                        <FilterCheckbox
                            isDisabled={inStockCount === 0}
                            isActive={field.value}
                            id="onlyInStock"
                            label="Skladem"
                            field={field}
                            count={inStockCount}
                            onSubmit={onSubmit}
                        />
                    )}
                />
            );
        case 'colorPicker':
            return (
                <FilterGroupColorStyled>
                    {data !== undefined &&
                        data.map((dataItem: ParameterItemsType, index: number) => (
                            <Fragment key={index}>
                                <Controller
                                    name={`${filterField}[${parentIndex}].values.${dataItem.item.uuid}`}
                                    render={({ field }) => (
                                        <FilterCheckbox
                                            colorPicker={true}
                                            isDisabled={dataItem.count === 0}
                                            isActive={field.value}
                                            id={dataItem.item.name + filterGroupTitle + type}
                                            bgColor={dataItem.rgbHex}
                                            label={dataItem.item.name}
                                            field={field}
                                            onSubmit={onSubmit}
                                        />
                                    )}
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
