import { forwardRef, InputHTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';

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
        <div className="relative">
            <input
                className="peer z-above h-6 w-11 rounded opacity-0 "
                id={id}
                type="checkbox"
                checked={value}
                name={name}
                onChange={onChange}
                ref={toggleSwitchForwardedRef}
                onBlur={onBlur}
            />
            <label
                className="absolute top-0 left-0 h-6 w-11 cursor-pointer rounded bg-greyLight after:my-[2px] after:ml-[2px] after:block after:h-5 after:w-5 after:rounded after:bg-white after:shadow-sm after:transition-all after:content-[''] peer-checked:bg-primary peer-checked:after:ml-[22px] peer-checked:after:block"
                htmlFor={id}
            />
        </div>
    ),
);

ToggleSwitch.displayName = 'ToggleSwitch';
