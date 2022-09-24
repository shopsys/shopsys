import { IconName, IconsSvgMap } from './IconsSvgMap';
import { IconSvgStyled } from './IconSvg.style';
import { CSSProperties, FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLElement>, never, 'onClick' | 'className'>;

type IconSvgProps = NativeProps & {
    icon: IconName;
    width?: number;
    height?: number;
    color?: CSSProperties['color'];
};

const getTestIdentifier = (icon: IconName) => 'basic-icon-iconsvg-' + icon;

export const IconSvg: FC<IconSvgProps> = ({ icon, onClick, className, width = 14, height = 14, color }) => (
    <IconSvgStyled
        className={className}
        data-testid={getTestIdentifier(icon)}
        onClick={onClick}
        $width={width}
        $height={height}
        $color={color}
    >
        {IconsSvgMap[icon]}
    </IconSvgStyled>
);
