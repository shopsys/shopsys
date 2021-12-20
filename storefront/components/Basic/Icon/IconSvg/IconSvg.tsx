import { IconName, IconsSvgMap } from './IconsSvgMap';
import { FC } from 'react';
import { IconSvgStyled } from './IconSvg.style';

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
