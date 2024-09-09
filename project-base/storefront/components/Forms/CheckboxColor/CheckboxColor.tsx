import { ColorLabelWrapper } from 'components/Forms/Lib/ColorLabelWrapper';
import { InputHTMLAttributes } from 'react';
import tinycolor from 'tinycolor2';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'id' | 'onChange',
    'name' | 'required' | 'disabled'
>;

type CheckboxColorProps = NativeProps & {
    value: any;
    label?: string;
    bgColor?: string;
    count?: number;
};

export const CheckboxColor: FC<CheckboxColorProps> = ({
    bgColor = '#d4d4d4',
    label,
    id,
    name,
    count,
    disabled,
    required,
    value,
    onChange,
}) => (
    <ColorLabelWrapper
        bgColor={bgColor}
        checked={value}
        count={count}
        disabled={disabled}
        htmlFor={id}
        isLightColor={tinycolor(bgColor).isLight()}
        label={label}
    >
        <input
            aria-label={label}
            checked={value}
            className="peer sr-only"
            disabled={disabled}
            id={id}
            name={name}
            required={required}
            type="checkbox"
            value={value}
            onChange={onChange}
        />
    </ColorLabelWrapper>
);
