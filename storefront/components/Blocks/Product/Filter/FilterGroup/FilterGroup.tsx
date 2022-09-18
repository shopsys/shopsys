import { useFilterState } from '../FilterContext/useFilterState';
import {
    FilterGroupArrowStyled,
    FilterGroupContentItemStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from './FilterGroup.style';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { FC, useMemo, useState } from 'react';

type FilterFieldType = 'flags' | 'brands';

type FilterGroupProps = {
    title: string;
    isOpen: boolean;
    filterField: FilterFieldType;
};

const getTestIdentifier = (filterField: FilterFieldType) => 'blocks-product-filter-filtergroup-' + filterField;

export const FilterGroup: FC<FilterGroupProps> = ({ title, isOpen, filterField }) => {
    const [isGroupOpen, setIsGroupOpen] = useState(isOpen);
    const [state, dispatch] = useFilterState();
    const selected = useMemo(() => state.selected[filterField], [filterField, state.selected]);
    const options = useMemo(() => state.options[filterField], [filterField, state.options]);

    return (
        <FilterGroupStyled data-testid={getTestIdentifier(filterField)}>
            <FilterGroupTitleStyled onClick={() => setIsGroupOpen((currentGroupVisibility) => !currentGroupVisibility)}>
                {title}
                <FilterGroupArrowStyled alt="" iconType="icon" icon="Arrow" isOpen={isGroupOpen} />
            </FilterGroupTitleStyled>
            <FilterGroupContentStyled isOpen={isGroupOpen}>
                {selected.map((dataItem, index) => {
                    const count = typeof options[index] !== 'undefined' ? options[index].count : 0;

                    return (
                        <FilterGroupContentItemStyled
                            key={dataItem.uuid}
                            isDisabled={count === 0}
                            isActive={dataItem.checked}
                            data-testid={getTestIdentifier(filterField) + '-' + index}
                        >
                            <Checkbox
                                id={`${filterField}.${index}.checked`}
                                name={`${filterField}.${index}.checked`}
                                label={dataItem.name}
                                onChange={() =>
                                    dispatch({
                                        type: filterField === 'flags' ? 'setFlags' : 'setBrands',
                                        payload: {
                                            value: { ...dataItem, checked: !dataItem.checked },
                                            index,
                                        },
                                    })
                                }
                                value={dataItem.checked}
                                count={count}
                            />
                        </FilterGroupContentItemStyled>
                    );
                })}
            </FilterGroupContentStyled>
        </FilterGroupStyled>
    );
};
