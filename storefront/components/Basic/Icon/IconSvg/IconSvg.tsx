import { IconName, IconsSvgMap } from './IconsSvgMap';
import { IconSvgStyled } from './IconSvg.style';
import { FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLElement>, never, 'onClick' | 'className'>;

type IconSvgProps = NativeProps & {
    icon: IconName;
};

const getTestIdentifier = (icon: IconName) => 'basic-icon-iconsvg-' + icon;

export const IconSvg: FC<IconSvgProps> = ({ icon, onClick, className }) => (
    <IconSvgStyled className={className} data-testid={getTestIdentifier(icon)} onClick={onClick}>
        {IconsSvgMap[icon]}
    </IconSvgStyled>
);
