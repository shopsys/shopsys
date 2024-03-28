import { StyleguideSection } from './StyleguideElements';
import { Select } from 'components/Forms/Select/Select';
import React, { useState } from 'react';

export const StyleguideSelects: FC = () => {
    const [selectedBasicOption, setSelectedBasicOption] = useState<ColourOption>();
    const [selectedGroupOption, setSelectedGroupOption] = useState<ColourOption>();

    return (
        <StyleguideSection className="flex flex-col gap-3" title="Selects">
            <Select
                hasError={false}
                label="Basic select"
                options={colourOptions}
                value={selectedBasicOption}
                onChange={(value) => setSelectedBasicOption(value as ColourOption)}
            />

            <Select
                hasError={false}
                label="Group select"
                options={groupedOptions}
                value={selectedGroupOption}
                onChange={(value) => setSelectedGroupOption(value as ColourOption)}
            />

            <Select
                isDisabled
                hasError={false}
                label="Disabled select"
                options={[]}
                value={null}
                onChange={() => null}
            />
        </StyleguideSection>
    );
};

export interface ColourOption {
    readonly value: string;
    readonly label: string;
    readonly color: string;
    readonly isFixed?: boolean;
    readonly isDisabled?: boolean;
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

export const colourOptions: readonly ColourOption[] = [
    { value: 'ocean', label: 'Ocean - fixed', color: '#00B8D9', isFixed: true },
    { value: 'blue', label: 'Blue - disabled', color: '#0052CC', isDisabled: true },
    { value: 'purple', label: 'Purple', color: '#5243AA' },
    { value: 'red', label: 'Red', color: '#FF5630' },
    { value: 'orange', label: 'Orange', color: '#FF8B00' },
    { value: 'yellow', label: 'Yellow', color: '#FFC400' },
    { value: 'green', label: 'Green', color: '#36B37E' },
    { value: 'forest', label: 'Forest', color: '#00875A' },
    { value: 'slate', label: 'Slate', color: '#253858' },
    { value: 'silver', label: 'Silver', color: '#666666' },
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

export const groupedOptions: readonly GroupedOption[] = [
    {
        label: 'Colours',
        options: colourOptions,
    },
    {
        label: 'Flavours',
        options: flavourOptions,
    },
];
