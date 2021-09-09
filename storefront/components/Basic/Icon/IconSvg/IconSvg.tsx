import { IconName, IconsSvgMap } from './IconsSvgMap';
import { FC } from 'react';
import { IconSvgStyled } from './IconSvg.style';

export type IconSvgProps = {
    icon: IconName;
};

export const IconSvg: FC<IconSvgProps> = ({ icon, ...props }) => (
    <IconSvgStyled {...props}>{IconsSvgMap[icon] ?? <i {...props} />}</IconSvgStyled>
);

IconSvg.displayName = `IconSvg`;
