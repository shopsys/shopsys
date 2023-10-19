import {
    FilterGroupContent,
    FilterGroupContentItem,
    FilterGroupTitle,
    FilterGroupWrapper,
    ShowAllButton,
} from './FilterElements';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { useFilterShowLess } from 'hooks/filter/useFilterShowLess';
import { useQueryParams } from 'hooks/useQueryParams';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';

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
    const { t } = useTranslation();
    const [isGroupOpen, setIsGroupOpen] = useState(true);
    const { filter, updateFilterFlags, updateFilterBrands } = useQueryParams();
    const defaultSelectedFlags = useSessionStore((s) => s.defaultProductFiltersMap.flags);

    const selectedItems = filter && filter[filterField];

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
            <FilterGroupTitle isOpen={isGroupOpen} title={title} onClick={() => setIsGroupOpen(!isGroupOpen)} />
            {isGroupOpen && (
                <FilterGroupContent>
                    {defaultOptions && (
                        <>
                            {defaultOptions.map((option, index) => {
                                const isFlagAndSelectedByDefault =
                                    filterField === 'flags' && defaultSelectedFlags.has(option.uuid);
                                const isChecked = !!selectedItems?.includes(option.uuid) || isFlagAndSelectedByDefault;
                                const isDisabled = option.count === 0 && !isChecked;

                                return (
                                    <FilterGroupContentItem
                                        key={option.uuid}
                                        dataTestId={getDataTestId(filterField) + '-' + index}
                                        isDisabled={isDisabled}
                                    >
                                        <Checkbox
                                            count={option.count}
                                            disabled={isDisabled}
                                            id={`${filterField}.${index}.checked`}
                                            label={option.name}
                                            name={`${filterField}.${index}.checked`}
                                            value={isChecked}
                                            onChange={() => handleCheck(option.uuid)}
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
            )}
        </FilterGroupWrapper>
    );
};
