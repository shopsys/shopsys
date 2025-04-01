import { forwardRef, InputHTMLAttributes } from 'react';
import { twJoin } from 'tailwind-merge';
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
                checked={value}
                className="z-above peer h-6 w-11 rounded"
                id={id}
                name={name}
                ref={toggleSwitchForwardedRef}
                type="checkbox"
                onBlur={onBlur}
                onChange={onChange}
            />
            <label
                htmlFor={id}
                className={twJoin(
                    "absolute top-0 left-0 h-6 w-11 cursor-pointer rounded-sm after:my-[2px] after:ml-[2px] after:block after:h-5 after:w-5 after:rounded-sm after:shadow-xs after:transition-all after:content-[''] peer-checked:after:ml-5 peer-checked:after:block",
                    'bg-input-border-default after:bg-input-text-inverted',
                    'peer-checked:bg-input-border-active',
                )}
            />
        </div>
    ),
);

ToggleSwitch.displayName = 'ToggleSwitch';
