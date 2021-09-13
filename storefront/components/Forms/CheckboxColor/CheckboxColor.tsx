import { FC, InputHTMLAttributes } from 'react';
import { CheckboxColorStyled } from './CheckboxColor.style';
import ColorLabelWrapper from 'components/Forms/Lib/ColorLabelWrapper';
import { ControllerRenderProps } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'id' | 'name' | 'disabled' | 'required'
>;

type CheckboxColorProps = NativeProps & {
    /**
     * Display Label of the HTML checkbox element
     */
    label?: string;
    /**
     * When bg color is light - we have to show dark tick
     */
    isLightColor: boolean;
    /**
     * Background color of color chooser
     */
    bgColor: string;
    /**
     * a ref of the controlled field element used for hooking onto the field events/changes
     */
    fieldRef?: ControllerRenderProps;
};

/**
 * CheckboxColor - circle color with invisible checkbox, selected color will display tick
 */
const CheckboxColor: FC<CheckboxColorProps> = (props) => {
    return (
        <ColorLabelWrapper
            htmlFor={props.id}
            label={props.label}
            bgColor={props.bgColor}
            isLightColor={props.isLightColor}
        >
            <CheckboxColorStyled {...props} {...props.fieldRef} type="checkbox" />
        </ColorLabelWrapper>
    );
};

/* @component */
export default CheckboxColor;
