import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper';
import { ReactNode } from 'react';
import SelectReact from 'react-select';
import { components, Props } from 'react-select';
import { twJoin } from 'tailwind-merge';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    Props,
    'options' | 'onChange',
    'defaultValue' | 'value' | 'isDisabled' | 'id' | 'required'
>;

type SelectProps = NativeProps & {
    hasError: boolean;
    label: ReactNode;
};

const DropdownIndicator = (props: any) => {
    return (
        <components.DropdownIndicator {...props}>
            <ArrowIcon className={twJoin('text-inputText', props.isDisabled && 'text-inputTextDisabled')} />
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
                className="selectbox peer font-bold"
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
                control: (styles) => {
                    if (isDisabled) {
                        return {
                            ...styles,
                            backgroundColor: '#E3E3E3 !important',
                            borderColor: '#AFBBCF !important',
                            color: '#727588 !important',
                        };
                    }
                    if (hasError) {
                        return {
                            ...styles,
                            boxShadow: 'none',
                            backgroundColor: 'white',
                            borderColor: '#ec5353',
                        };
                    }

                    return styles;
                },
                singleValue: (styles) => {
                    if (isDisabled) {
                        return {
                            ...styles,
                            color: '#727588 !important',
                        };
                    }

                    return styles;
                },
            }}
            onChange={onChange}
            {...props}
        />
    );
};
