import { ControllerRenderProps } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { FC } from 'react';
import { Props } from 'react-select';
import { SelectStyled } from './Select.style';

type NativeProps = ExtractNativePropsFromDefault<
    Props,
    'options' | 'onChange',
    'defaultValue' | 'value' | 'isDisabled'
>;

type SelectProps = NativeProps & {
    hasError: boolean;
    fieldRef?: ControllerRenderProps;
};

const customStyles = {
    indicatorSeparator: () => ({}),
};

const Select: FC<SelectProps> = (props) => {
    return (
        <SelectStyled
            {...props}
            {...props.fieldRef}
            classNamePrefix="select"
            styles={customStyles}
            inputStateError={props.hasError}
        />
    );
};

/* @component */
export default Select;
