import { useFilterState } from '../FilterContext/useFilterState';
import {
    FilterGroupContent,
    FilterGroupContentItem,
    FilterGroupTitle,
    FilterGroupWrapper,
    ShowAllButton,
} from '../FilterElements';
import { FilterGroupIcon } from './FilterGroupIcon';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useMemo, useState } from 'react';

type FilterFieldType = 'flags' | 'brands';

type FilterGroupProps = {
    title: string;
    isOpen: boolean;
    filterField: FilterFieldType;
    defaultNumberOfShownFlagsOrBrands: number;
    areByDefaultAllFlagsOrBrandsShown?: boolean;
};

const getTestIdentifier = (filterField: FilterFieldType) => 'blocks-product-filter-filtergroup-' + filterField;

export const FilterGroup: FC<FilterGroupProps> = ({
    title,
    isOpen,
    filterField,
    defaultNumberOfShownFlagsOrBrands,
    areByDefaultAllFlagsOrBrandsShown,
}) => {
    const t = useTypedTranslationFunction();
    const [isGroupOpen, setIsGroupOpen] = useState(isOpen);
    const [areAllFlagsOrBrandsShown, setAreAllFlagsOrBrandsShown] = useState(!!areByDefaultAllFlagsOrBrandsShown);
    const [state, dispatch] = useFilterState();

    const selected = useMemo(() => state.selected[filterField], [filterField, state.selected]);
    const options = useMemo(() => state.options[filterField], [filterField, state.options]);

    return (
        <FilterGroupWrapper dataTestId={getTestIdentifier(filterField)}>
            <FilterGroupTitle onClick={() => setIsGroupOpen((currentGroupVisibility) => !currentGroupVisibility)}>
                {title}
                <FilterGroupIcon isOpen={isGroupOpen} />
            </FilterGroupTitle>
            <FilterGroupContent isOpen={isGroupOpen}>
                {selected
                    .slice(0, areAllFlagsOrBrandsShown ? undefined : defaultNumberOfShownFlagsOrBrands)
                    .map((dataItem, index) => {
                        const count = typeof options[index] !== 'undefined' ? options[index].count : 0;

                        return (
                            <FilterGroupContentItem
                                key={dataItem.uuid}
                                isDisabled={count === 0 && !dataItem.checked}
                                dataTestId={getTestIdentifier(filterField) + '-' + index}
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
                            </FilterGroupContentItem>
                        );
                    })}
                {selected.length > defaultNumberOfShownFlagsOrBrands && (
                    <ShowAllButton onClick={() => setAreAllFlagsOrBrandsShown((prev) => !prev)}>
                        {areAllFlagsOrBrandsShown ? t('show less') : t('show more')}
                    </ShowAllButton>
                )}
            </FilterGroupContent>
        </FilterGroupWrapper>
    );
};
