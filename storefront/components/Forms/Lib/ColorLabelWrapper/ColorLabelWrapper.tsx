import { ColorLabelWrapperStyled } from './ColorLabelWrapper.style';
import { Tooltip } from 'components/Basic/Tooltip/Tooltip';
import { FC, LabelHTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<LabelHTMLAttributes<HTMLLabelElement>, never, 'htmlFor'>;

type ColorLabelWrapperProps = NativeProps & {
    /**
     * Display Label of the given HTML element
     */
    label?: string;
    /**
     * Determine if selected color is light then show dark tick icon
     */
    isLightColor: boolean;
    /**
     * Set background color for checkbox color
     */
    bgColor: string;
    isDisabled?: boolean;
    isActive: boolean;
};

const ColorLabelWrapper: FC<ColorLabelWrapperProps> = (props) => {
    return (
        <ColorLabelWrapperStyled
            isLightColor={props.isLightColor}
            bgColor={props.bgColor}
            isDisabled={props.isDisabled}
            isActive={props.isActive}
        >
            {props.children}
            <Tooltip label={props.label}>
                <label htmlFor={props.htmlFor} />
            </Tooltip>
        </ColorLabelWrapperStyled>
    );
};

/* @component */
export default ColorLabelWrapper;
