import { ArrowIcon } from 'components/Basic/Icon/IconsSvg';
import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper';
import { ReactNode } from 'react';
import SelectReact from 'react-select';
import { components, Props } from 'react-select';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    Props,
    'options' | 'onChange',
    'defaultValue' | 'value' | 'isDisabled'
>;

type SelectProps = NativeProps & {
    hasError: boolean;
    label: ReactNode;
};

const DropdownIndicator = (props: any) => {
    return (
        <components.DropdownIndicator {...props}>
            <ArrowIcon className="text-greyDark" />
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
            onChange={onChange}
            options={options}
            defaultValue={defaultValue}
            isDisabled={isDisabled}
            value={value}
            classNamePrefix="select"
            styles={{
                indicatorSeparator: () => ({}),
                control: (styles) =>
                    hasError
                        ? { ...styles, boxShadow: 'none', backgroundColor: 'white', borderColor: '#ec5353' }
                        : styles,
            }}
            placeholder={props.label}
            components={{ Control, DropdownIndicator }}
            isSearchable={false}
            {...props}
        />
    );
};
