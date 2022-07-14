import { CheckboxColorStyled } from './CheckboxColor.style';
import ColorLabelWrapper from 'components/Forms/Lib/ColorLabelWrapper';
import { FC, InputHTMLAttributes } from 'react';
import { ControllerRenderProps } from 'react-hook-form';
import tinycolor from 'tinycolor2';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'name' | 'disabled',
    'id' | 'required'
>;

type CheckboxColorProps = NativeProps & {
    /**
     * Display Label of the HTML checkbox element
     */
    label?: string;
    /**
     * Background color of color chooser
     */
    bgColor?: string;
    /**
     * a ref of the controlled field element used for hooking onto the field events/changes
     */
    fieldRef?: ControllerRenderProps<any, any>;
};

/**
 * CheckboxColor - circle color with invisible checkbox, selected color will display tick
 */
const CheckboxColor: FC<CheckboxColorProps> = ({
    bgColor = '#d4d4d4',
    label,
    fieldRef,
    id,
    name,
    disabled,
    required,
}) => {
    return (
        <ColorLabelWrapper
            htmlFor={id === undefined ? name + 'checkbox_color-id' : id}
            label={label}
            bgColor={bgColor}
            isLightColor={tinycolor(bgColor).isLight()}
            isDisabled={disabled}
            isActive={fieldRef?.value ?? false}
        >
            <CheckboxColorStyled
                {...fieldRef}
                disabled={disabled}
                required={required}
                id={id === undefined ? name + 'checkbox_color-id' : id}
                type="checkbox"
            />
        </ColorLabelWrapper>
    );
};

/* @component */
export default CheckboxColor;
