import { ColorLabelWrapper } from 'components/Forms/Lib/ColorLabelWrapper';
import { InputHTMLAttributes } from 'react';
import tinycolor from 'tinycolor2';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'id' | 'onChange',
    'name' | 'required' | 'disabled'
>;

type CheckboxColorProps = NativeProps & {
    value: any;
    label?: string;
    bgColor?: string;
};

export const CheckboxColor: FC<CheckboxColorProps> = ({
    bgColor = '#d4d4d4',
    label,
    id,
    name,
    disabled,
    required,
    value,
    onChange,
    dataTestId,
}) => (
    <ColorLabelWrapper
        label={label}
        htmlFor={id}
        bgColor={bgColor}
        isLightColor={tinycolor(bgColor).isLight()}
        isDisabled={disabled}
        isActive={value}
    >
        <input
            className="peer sr-only"
            aria-label={label}
            disabled={disabled}
            required={required}
            id={id}
            name={name}
            checked={value}
            value={value}
            onChange={onChange}
            type="checkbox"
            data-testid={dataTestId}
        />
    </ColorLabelWrapper>
);
