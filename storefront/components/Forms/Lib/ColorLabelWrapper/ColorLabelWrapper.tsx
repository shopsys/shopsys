import { ColorLabelWrapperStyled } from './ColorLabelWrapper.style';
import { Tooltip } from 'components/Basic/Tooltip/Tooltip';
import { FC, LabelHTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<LabelHTMLAttributes<HTMLLabelElement>, never, 'htmlFor'>;

type ColorLabelWrapperProps = NativeProps & {
    label?: string;
    isLightColor: boolean;
    bgColor: string;
    isDisabled?: boolean;
    isActive: boolean;
};

export const ColorLabelWrapper: FC<ColorLabelWrapperProps> = (props) => {
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
