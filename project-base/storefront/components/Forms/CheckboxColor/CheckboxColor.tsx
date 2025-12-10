import { ColorLabelWrapper } from 'components/Forms/Lib/ColorLabelWrapper';
import { InputHTMLAttributes } from 'react';
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
    imageUrl?: string;
    imageName?: string;
    count?: number;
};

export const CheckboxColor: FC<CheckboxColorProps> = ({
    bgColor = '#d4d4d4',
    imageUrl,
    imageName,
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
        imageName={imageName}
        imageUrl={imageUrl}
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
            tabIndex={0}
            type="checkbox"
            value={value}
            onChange={onChange}
        />
    </ColorLabelWrapper>
);
