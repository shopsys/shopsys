import { FC } from 'react';
import { SelectStyled } from './Select.style';

type SelectProps = {
    options: {
        value: string;
        label: string;
    }[];
    defaultValue: {
        value: string;
        label: string;
    };
    onChange: any;
};

const customStyles = {
    indicatorSeparator: () => ({}),
};

const Select: FC<SelectProps> = (props) => {
    return <SelectStyled classNamePrefix="select" styles={customStyles} {...props} />;
};

/* @component */
export default Select;
