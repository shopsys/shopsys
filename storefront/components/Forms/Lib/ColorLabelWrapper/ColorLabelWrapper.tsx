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

export const ColorLabelWrapper: FC<ColorLabelWrapperProps> = ({
    label,
    isLightColor,
    bgColor,
    isDisabled,
    isActive,
    htmlFor,
    children,
}) => {
    return (
        <ColorLabelWrapperStyled
            isLightColor={isLightColor}
            bgColor={bgColor}
            isDisabled={isDisabled}
            isActive={isActive}
        >
            {children}
            <Tooltip label={label}>
                <label htmlFor={htmlFor} />
            </Tooltip>
        </ColorLabelWrapperStyled>
    );
};
