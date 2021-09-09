import { IconName, IconsSvgMap } from './IconsSvgMap';
import { FC } from 'react';
import { IconSvgStyled } from './IconSvg.style';

export type IconSvgProps = {
    icon: IconName;
};

export const IconSvg: FC<IconSvgProps> = (props) => (
    <IconSvgStyled {...props}>{IconsSvgMap[props.icon] ?? <i {...props} />}</IconSvgStyled>
);
