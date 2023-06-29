import {
    FilterGroupContent,
    FilterGroupContentItem,
    FilterGroupTitle,
    FilterGroupWrapper,
    ShowAllButton,
} from './FilterElements';
import { FilterGroupIcon } from './FilterGroupIcon';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { useFilterShowLess } from 'hooks/filter/useFilterShowLess';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useQueryParams } from 'hooks/useQueryParams';
import { useState } from 'react';
import { useSessionStore } from 'store/zustand/useSessionStore';

type FilterFieldType = 'flags' | 'brands';

export type MappedFilterOption = { name: string; uuid: string; count?: number };

type FilterGroupGenericProps = {
    title: string;
    filterField: FilterFieldType;
    options: MappedFilterOption[];
    defaultNumberOfShownItems: number;
};

const getDataTestId = (filterField: FilterFieldType) => 'blocks-product-filter-filtergroup-' + filterField;

export const FilterGroupGeneric: FC<FilterGroupGenericProps> = ({
    title,
    options,
    defaultNumberOfShownItems,
    filterField,
}) => {
    const t = useTypedTranslationFunction();
    const [isGroupOpen, setIsGroupOpen] = useState(true);
    const { filter, updateFilterFlags, updateFilterBrands } = useQueryParams();
    const defaultSelectedFlags = useSessionStore((s) => s.defaultProductFiltersMap.flags);

    const selectedItems = filter[filterField];

    const { defaultOptions, isShowLessMoreShown, isWithAllItemsShown, setAreAllItemsShown } = useFilterShowLess(
        options,
        defaultNumberOfShownItems,
        selectedItems,
    );

    const handleCheck = (uuid: string) => {
        switch (filterField) {
            case 'brands':
                updateFilterBrands(uuid);
                break;
            case 'flags':
                updateFilterFlags(uuid);
                break;
        }
    };

    return (
        <FilterGroupWrapper dataTestId={getDataTestId(filterField)}>
            <FilterGroupTitle onClick={() => setIsGroupOpen((currentGroupVisibility) => !currentGroupVisibility)}>
                {title}
                <FilterGroupIcon isOpen={isGroupOpen} />
            </FilterGroupTitle>
            <FilterGroupContent isOpen={isGroupOpen}>
                {defaultOptions && (
                    <>
                        {defaultOptions.map((option, index) => {
                            const isFlagAndSelectedByDefault =
                                filterField === 'flags' && defaultSelectedFlags.has(option.uuid);
                            const isChecked = !!selectedItems?.includes(option.uuid) || isFlagAndSelectedByDefault;

                            return (
                                <FilterGroupContentItem
                                    key={option.uuid}
                                    isDisabled={option.count === 0 && !isChecked}
                                    dataTestId={getDataTestId(filterField) + '-' + index}
                                >
                                    <Checkbox
                                        id={`${filterField}.${index}.checked`}
                                        name={`${filterField}.${index}.checked`}
                                        label={option.name}
                                        onChange={() => handleCheck(option.uuid)}
                                        value={isChecked}
                                        count={option.count}
                                    />
                                </FilterGroupContentItem>
                            );
                        })}

                        {isShowLessMoreShown && (
                            <ShowAllButton onClick={() => setAreAllItemsShown((prev) => !prev)}>
                                {isWithAllItemsShown ? t('show less') : t('show more')}
                            </ShowAllButton>
                        )}
                    </>
                )}
            </FilterGroupContent>
        </FilterGroupWrapper>
    );
};
