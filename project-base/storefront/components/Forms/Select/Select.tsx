import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper';
import { ReactNode } from 'react';
import SelectReact from 'react-select';
import { components, Props } from 'react-select';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    Props,
    'options' | 'onChange',
    'defaultValue' | 'value' | 'isDisabled' | 'id'
>;

type SelectProps = NativeProps & {
    hasError: boolean;
    label: ReactNode;
};

const DropdownIndicator = (props: any) => {
    return (
        <components.DropdownIndicator {...props}>
            <ArrowIcon className="text-inputText" />
        </components.DropdownIndicator>
    );
};

const Control = (props: any) => {
    return (
        <LabelWrapper
            {...props.children}
            htmlFor={props.id}
            inputType="selectbox"
            label={props.selectProps.label}
            required={props.selectProps.required}
            selectBoxLabelIsFloated={props.menuIsOpen === true || props.hasValue === true}
        >
            <components.Control
                // class "peer" is used for styling in LabelWrapper
                className="selectbox peer"
                {...props}
            />
        </LabelWrapper>
    );
};

export const Select: FC<SelectProps> = ({ hasError, onChange, options, defaultValue, isDisabled, value, ...props }) => {
    return (
        <SelectReact
            classNamePrefix="select"
            components={{ Control, DropdownIndicator }}
            defaultValue={defaultValue}
            inputId={props.id}
            isDisabled={isDisabled}
            isSearchable={false}
            options={options}
            placeholder={props.label}
            value={value}
            styles={{
                indicatorSeparator: () => ({}),
                control: (styles) =>
                    hasError
                        ? { ...styles, boxShadow: 'none', backgroundColor: 'white', borderColor: '#ec5353' }
                        : styles,
            }}
            onChange={onChange}
            {...props}
        />
    );
};
