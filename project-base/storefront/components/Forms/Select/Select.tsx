import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper';
import { m } from 'framer-motion';
import { ReactNode, useState } from 'react';
import SelectReact, { components, Props } from 'react-select';
import { twJoin } from 'tailwind-merge';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { isClient } from 'utils/isClient';

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
            <m.div
                animate={{ rotate: props.selectProps.menuIsOpen ? 180 : 0 }}
                transition={{ type: 'tween', duration: 0.2 }}
            >
                <ArrowIcon className={twJoin('text-inputText', props.isDisabled && 'text-inputTextDisabled')} />
            </m.div>
        </components.DropdownIndicator>
    );
};

const Control = (props: any) => {
    return (
        <LabelWrapper
            {...props.children}
            disabled={props.isDisabled}
            htmlFor={props.inputId}
            inputType="selectbox"
            label={props.selectProps.label}
            required={props.selectProps.required}
            selectBoxLabelIsFloated={props.menuIsOpen === true || props.hasValue === true}
        >
            <components.Control
                // class "peer" is used for styling in LabelWrapper
                className="selectbox peer font-semibold"
                {...props}
            />
        </LabelWrapper>
    );
};

export const Select: FC<SelectProps> = ({ hasError, onChange, options, defaultValue, isDisabled, value, ...props }) => {
    const [isMenuOpen, setIsMenuOpen] = useState(false);

    const openMenuHandler = () => setIsMenuOpen(true);
    const closeMenuHandler = () => {
        const menu = document.querySelector(`.select__menu`);
        menu?.classList.add('animate-fadeOut');
        setIsMenuOpen(false);
    };

    return (
        <SelectReact
            classNamePrefix="select"
            components={{ Control, DropdownIndicator }}
            defaultValue={defaultValue}
            inputId={props.id}
            isDisabled={isDisabled}
            isSearchable={false}
            menuIsOpen={isMenuOpen}
            menuPortalTarget={isClient ? document.body : undefined}
            options={options}
            placeholder={props.label}
            value={value}
            styles={{
                menuPortal: (base) => ({ ...base, zIndex: 10001 }),
                indicatorSeparator: () => ({}),
                control: (styles) => {
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
            }}
            onChange={onChange}
            onMenuClose={closeMenuHandler}
            onMenuOpen={openMenuHandler}
            {...props}
        />
    );
};
