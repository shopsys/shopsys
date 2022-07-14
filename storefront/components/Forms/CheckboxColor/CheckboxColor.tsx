import { CheckboxColorStyled } from './CheckboxColor.style';
import ColorLabelWrapper from 'components/Forms/Lib/ColorLabelWrapper';
import { FC, InputHTMLAttributes } from 'react';
import { ControllerRenderProps } from 'react-hook-form';
import tinycolor from 'tinycolor2';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'name',
    'id' | 'disabled' | 'required'
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
    /**
     * Prop to check if checkbox is disabled
     */
    isDisabled: boolean;
    /**
     * Prop to check if checkbox is active
     */
    isActive: boolean;
};

/**
 * CheckboxColor - circle color with invisible checkbox, selected color will display tick
 */
const CheckboxColor: FC<CheckboxColorProps> = ({ bgColor = '#d4d4d4', ...props }) => {
    return (
        <ColorLabelWrapper
            htmlFor={props.id === undefined ? props.name + 'checkbox_color-id' : props.id}
            label={props.label}
            bgColor={bgColor}
            isLightColor={tinycolor(bgColor).isLight()}
            isDisabled={props.isDisabled}
            isActive={props.isActive}
        >
            <CheckboxColorStyled
                {...props}
                {...props.fieldRef}
                id={props.id === undefined ? props.name + 'checkbox_color-id' : props.id}
                checked={props.fieldRef?.value}
                type="checkbox"
            />
        </ColorLabelWrapper>
    );
};

/* @component */
export default CheckboxColor;
