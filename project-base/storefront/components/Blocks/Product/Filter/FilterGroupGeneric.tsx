import {
    FilterGroupContent,
    FilterGroupContentItem,
    FilterGroupTitle,
    FilterGroupWrapper,
    ShowAllButton,
} from './FilterElements';
import { useFilterShowLess } from './utils/useFilterShowLess';
import { Flag } from 'components/Basic/Flag/Flag';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { AnimatePresence } from 'framer-motion';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { createAriaParameter } from 'utils/accessibility/createAriaParameter';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useUpdateFilterQuery } from 'utils/queryParams/useUpdateFilterQuery';

type FilterFieldType = 'flags' | 'brands';

export type MappedFilterOption = { name: string; uuid: string; count?: number; rgbColor?: string };

type FilterGroupGenericProps = {
    title: string;
    filterField: FilterFieldType;
    options: MappedFilterOption[];
    defaultNumberOfShownItems: number;
    isActive: boolean;
    ariaLabel: string;
};

export const FilterGroupGeneric: FC<FilterGroupGenericProps> = ({
    title,
    options,
    defaultNumberOfShownItems,
    filterField,
    isActive,
    ariaLabel,
}) => {
    const { t } = useTranslation();
    const [isGroupOpen, setIsGroupOpen] = useState(true);
    const currentFilter = useCurrentFilterQuery();
    const { updateFilterFlagsQuery, updateFilterBrandsQuery } = useUpdateFilterQuery();
    const defaultSelectedFlags = useSessionStore((s) => s.defaultProductFiltersMap.flags);

    const selectedItems = currentFilter && currentFilter[filterField];

    const { defaultOptions, isShowLessMoreShown, isWithAllItemsShown, setAreAllItemsShown } = useFilterShowLess(
        options,
        defaultNumberOfShownItems,
        selectedItems,
    );

    const handleCheck = (uuid: string) => {
        switch (filterField) {
            case 'brands':
                updateFilterBrandsQuery(uuid);
                break;
            case 'flags':
                updateFilterFlagsQuery(uuid);
                break;
        }
    };

    return (
        <FilterGroupWrapper>
            <FilterGroupTitle
                ariaLabel={ariaLabel}
                isActive={isActive}
                isOpen={isGroupOpen}
                title={title}
                onClick={() => setIsGroupOpen(!isGroupOpen)}
            />
            <AnimatePresence initial={false}>
                {isGroupOpen && (
                    <FilterGroupContent id={createAriaParameter('filter-group', title)}>
                        {defaultOptions && (
                            <AnimatePresence initial={false}>
                                {defaultOptions.map((option, index) => {
                                    const isFlagAndSelectedByDefault =
                                        filterField === 'flags' && defaultSelectedFlags.has(option.uuid);
                                    const isChecked =
                                        !!selectedItems?.includes(option.uuid) || isFlagAndSelectedByDefault;
                                    const isDisabled = option.count === 0 && !isChecked;

                                    const optionLabel =
                                        filterField === 'flags' ? (
                                            <Flag className="flex w-fit py-0.5" rgbBgColor={option.rgbColor}>
                                                {option.name}
                                            </Flag>
                                        ) : (
                                            option.name
                                        );

                                    return (
                                        <FilterGroupContentItem
                                            key={option.uuid}
                                            isDisabled={isDisabled}
                                            keyName={option.uuid}
                                        >
                                            <Checkbox
                                                count={option.count}
                                                disabled={isDisabled}
                                                id={`${filterField}.${index}.checked`}
                                                label={optionLabel}
                                                name={`${filterField}.${index}.checked`}
                                                value={isChecked}
                                                onChange={() => handleCheck(option.uuid)}
                                            />
                                        </FilterGroupContentItem>
                                    );
                                })}

                                {isShowLessMoreShown && (
                                    <ShowAllButton onClick={() => setAreAllItemsShown((prev) => !prev)}>
                                        {isWithAllItemsShown ? t('Show less') : t('Show more')}
                                    </ShowAllButton>
                                )}
                            </AnimatePresence>
                        )}
                    </FilterGroupContent>
                )}
            </AnimatePresence>
        </FilterGroupWrapper>
    );
};
