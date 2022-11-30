import { ToggleSwitchLabel, ToggleSwitchStyled, ToggleSwitchWrapper } from './ToggleSwitch.style';
import { forwardRef, InputHTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'id' | 'onChange' | 'name',
    'onBlur'
>;

type ToggleSwitchProps = NativeProps & {
    value: any;
};

export const ToggleSwitch = forwardRef<HTMLInputElement, ToggleSwitchProps>(
    ({ id, name, onChange, value, onBlur }, toggleSwitchForwardedRef) => (
        <ToggleSwitchWrapper>
            <ToggleSwitchStyled
                id={id}
                type="checkbox"
                checked={value}
                name={name}
                onChange={onChange}
                ref={toggleSwitchForwardedRef}
                onBlur={onBlur}
            />
            <ToggleSwitchLabel htmlFor={id} />
        </ToggleSwitchWrapper>
    ),
);

ToggleSwitch.displayName = 'ToggleSwitch';
