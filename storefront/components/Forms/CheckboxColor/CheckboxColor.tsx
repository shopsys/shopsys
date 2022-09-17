import { CheckboxColorStyled } from './CheckboxColor.style';
import { ColorLabelWrapper } from 'components/Forms/Lib/ColorLabelWrapper/ColorLabelWrapper';
import { FC, InputHTMLAttributes } from 'react';
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
    testIdentifier?: string;
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
    testIdentifier,
}) => {
    return (
        <ColorLabelWrapper
            label={label}
            htmlFor={id}
            bgColor={bgColor}
            isLightColor={tinycolor(bgColor).isLight()}
            isDisabled={disabled}
            isActive={value}
        >
            <CheckboxColorStyled
                disabled={disabled}
                required={required}
                id={id}
                name={name}
                checked={value}
                value={value}
                onChange={onChange}
                type="checkbox"
                data-testid={testIdentifier}
            />
        </ColorLabelWrapper>
    );
};
