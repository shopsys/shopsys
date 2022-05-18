import { IconName, IconsSvgMap } from './IconsSvgMap';
import { IconSvgStyled } from './IconSvg.style';
import { FC } from 'react';

type IconSvgProps = {
    icon: IconName;
};

export const IconSvg: FC<IconSvgProps> = (props) => {
    const testIdentifier = 'basic-icon-iconsvg-' + props.icon;

    return (
        <IconSvgStyled {...props} data-testid={testIdentifier}>
            {IconsSvgMap[props.icon]}
        </IconSvgStyled>
    );
};
