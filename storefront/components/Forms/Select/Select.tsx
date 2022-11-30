import { SelectStyled } from './Select.style';
import { Icon } from 'components/Basic/Icon/Icon';
import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper/LabelWrapper';
import { FC, ReactNode } from 'react';
import { components, Props } from 'react-select';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    Props,
    'options' | 'onChange',
    'defaultValue' | 'value' | 'isDisabled'
>;

type SelectProps = NativeProps & {
    hasError: boolean;
    label: ReactNode;
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

export const Select: FC<SelectProps> = ({ hasError, label, onChange, options, defaultValue, isDisabled, value }) => {
    return (
        <SelectStyled
            label={label}
            onChange={onChange}
            options={options}
            defaultValue={defaultValue}
            isDisabled={isDisabled}
            value={value}
            classNamePrefix="select"
            styles={customStyles}
            inputStateError={hasError}
            placeholder={label}
            components={{ Control, DropdownIndicator }}
            isSearchable={false}
        />
    );
};
