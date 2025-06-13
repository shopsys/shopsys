import { StyleguideSection } from './StyleguideElements';
import { Select } from 'components/Forms/Select/Select';
import useTranslation from 'next-translate/useTranslation';
import React, { useState } from 'react';

export const COMBO_BOX_PAGE_SIZE = 5;

export const StyleguideSelects: FC = () => {
    const { t } = useTranslation();
    const [selectedBasicOption, setSelectedBasicOption] = useState<ColourOption>();

    const [searchValue, setSearchValue] = useState('');
    const [selectedComboBoxOption, setSelectedComboBoxOption] = useState<ColourOption | null>(null);

    const [selectedInfiniteOption, setSelectedInfiniteOption] = useState<ColourOption>();
    const [page, setPage] = useState(1);

    const generateInfiniteOptions = (pageNum: number): ColourOption[] => {
        const itemsPerPage = 5;
        const start = (pageNum - 1) * itemsPerPage;

        return Array.from({ length: itemsPerPage }, (_, i) => ({
            value: `item-${start + i}`,
            label: `Infinite Item ${start + i}`,
            color: `#${Math.floor(Math.random() * 16777215).toString(16)}`,
            count: Math.floor(Math.random() * 100),
        }));
    };
    const [infiniteOptions, setInfiniteOptions] = useState<ColourOption[]>(generateInfiniteOptions(1));

    return (
        <StyleguideSection className="flex max-w-96 flex-col gap-3" title="Selects">
            <Select
                activeOption={selectedBasicOption}
                ariaLabel={t('Select basic')}
                label="Basic select"
                options={colourOptions}
                tid="styleguide-selects-basic-select"
                onSelectOption={(value) => setSelectedBasicOption(value as ColourOption)}
            />

            <Select
                isLoading
                activeOption={selectedBasicOption}
                ariaLabel={t('Select loading')}
                label="Loading select"
                options={colourOptions}
                tid="styleguide-selects-basic-select"
                onSelectOption={(value) => setSelectedBasicOption(value as ColourOption)}
            />

            <Select
                isDisabled
                activeOption={null}
                ariaLabel={t('Select disabled')}
                label="Disabled select"
                options={[]}
                tid="styleguide-selects-disabled-select"
                onSelectOption={() => null}
            />

            <Select
                activeOption={selectedComboBoxOption}
                ariaLabel={t('Select combobox')}
                label="ComboBox select"
                options={colourOptions}
                placeholder={selectedComboBoxOption?.label || t('Search')}
                tid="styleguide-selects-combobox-select"
                comboBoxConfig={{
                    searchValue,
                    setSearchValue: (value) => {
                        setSearchValue(value);
                    },
                }}
                onResetSelect={() => setSelectedComboBoxOption(null)}
                onSelectOption={(value) => {
                    setSearchValue('');
                    setSelectedComboBoxOption(value as ColourOption);
                }}
            />

            <Select
                activeOption={selectedInfiniteOption}
                ariaLabel={t('Select infinite')}
                label="Infinite scroll select"
                options={infiniteOptions}
                tid="styleguide-selects-infinite-select"
                infinityScrollConfig={{
                    pageSize: COMBO_BOX_PAGE_SIZE,
                    hasMore: page < 5,
                    dataLength: infiniteOptions.length,
                    next: () => {
                        const newPage = page + 1;
                        setPage(newPage);
                        setInfiniteOptions((prev) => [...prev, ...generateInfiniteOptions(newPage)]);
                    },
                }}
                onSelectOption={(value) => setSelectedInfiniteOption(value as ColourOption)}
            />
        </StyleguideSection>
    );
};

export interface ColourOption {
    readonly value: string;
    readonly label: string;
    readonly color?: string;
    readonly isDisabled?: boolean;
    readonly count?: number;
}

export const dogOptions = [
    { id: 1, label: 'Chihuahua' },
    { id: 2, label: 'Bulldog' },
    { id: 3, label: 'Dachshund' },
    { id: 4, label: 'Akita' },
];

export interface GroupedOption {
    readonly label: string;
    readonly options: readonly ColourOption[] | readonly FlavourOption[];
}

export const colourOptions: ColourOption[] = [
    { value: 'ocean', label: 'Ocean', color: '#00B8D9', count: 22 },
    { value: 'blue', label: 'Blue - disabled', color: '#0052CC', isDisabled: true, count: 17 },
    { value: 'purple', label: 'Purple', color: '#5243AA', count: 5 },
    { value: 'red', label: 'Red', color: '#FF5630', count: 9 },
    { value: 'orange', label: 'Orange', color: '#FF8B00', count: 15 },
    { value: 'yellow', label: 'Yellow', color: '#FFC400', count: 7 },
    { value: 'green', label: 'Green', color: '#36B37E', count: 4 },
    { value: 'forest', label: 'Forest', color: '#00875A', count: 25 },
    { value: 'slate', label: 'Slate', color: '#253858', count: 13 },
    { value: 'silver', label: 'Silver', color: '#666666', count: 17 },
];

export interface FlavourOption {
    readonly value: string;
    readonly label: string;
    readonly rating: string;
}

export const flavourOptions: readonly FlavourOption[] = [
    { value: 'vanilla', label: 'Vanilla', rating: 'safe' },
    { value: 'chocolate', label: 'Chocolate', rating: 'good' },
    { value: 'strawberry', label: 'Strawberry', rating: 'wild' },
    { value: 'salted-caramel', label: 'Salted Caramel', rating: 'crazy' },
];
