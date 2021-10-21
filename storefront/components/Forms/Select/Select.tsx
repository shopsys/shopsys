import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { FC } from 'react';
import { Props } from 'react-select';
import { SelectStyled } from './Select.style';

type SelectProps = ExtractNativePropsFromDefault<Props, 'options' | 'onChange', 'defaultValue' | 'value'>;

export type SelectOptionType = {
    value: string;
    label: string;
};

const customStyles = {
    indicatorSeparator: () => ({}),
};

const Select: FC<SelectProps> = (props) => {
    return <SelectStyled classNamePrefix="select" styles={customStyles} {...props} />;
};

/* @component */
export default Select;
