import { SelectStyled } from './Select.style';
import Icon from 'components/Basic/Icon';
import LabelWrapper from 'components/Forms/Lib/LabelWrapper';
import { FC } from 'react';
import { ControllerRenderProps } from 'react-hook-form';
import { components, Props } from 'react-select';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    Props,
    'options' | 'onChange',
    'defaultValue' | 'value' | 'isDisabled'
>;

type SelectProps = NativeProps & {
    hasError: boolean;
    fieldRef?: ControllerRenderProps<any, any>;
    label: string | JSX.Element;
    required?: boolean;
};

const customStyles = {
    indicatorSeparator: () => ({}),
};

const DropdownIndicator = (props: any) => {
    return (
        <components.DropdownIndicator {...props}>
            <Icon iconType="icon" icon="Arrow" />
        </components.DropdownIndicator>
    );
};

const Control = (props: any) => {
    return (
        <LabelWrapper
            {...props.children}
            label={props.selectProps.label}
            required={props.selectProps.required}
            selectBoxLabelIsFloated={props.menuIsOpen === true || props.hasValue === true}
            htmlFor={props.id}
            inputType="selectbox"
        >
            <components.Control className="selectbox" {...props} />
        </LabelWrapper>
    );
};

const Select: FC<SelectProps> = (props) => {
    return (
        <SelectStyled
            {...props}
            {...props.fieldRef}
            classNamePrefix="select"
            styles={customStyles}
            inputStateError={props.hasError}
            placeholder={props.label}
            components={{ Control, DropdownIndicator }}
            isSearchable={false}
        />
    );
};

/* @component */
export default Select;
