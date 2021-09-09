import { FC } from 'react';
import { IconName } from './IconSvg/IconsSvgMap';
import { IconSvg } from './IconSvg';

/**
 *  Basic icon component unifies displaying icons
 */
type IconProps = {
    /**
     * Define which type of icon will be generated
     */
    iconType?: 'img';
    /**
     * Define name of icon which is rendered as a svg element
     */
    icon: IconName;
    /**
     * Define name of icon which is rendered as a img element
     */
    imageName?: string;
    /**
     * Icon height for img element
     */
    iconHeight?: number;
    /**
     * Icon width for img element
     */
    iconWidth?: number;
    /**
     * String for title attribute for generating tooltip text
     */
    iconTitle?: string;
};

const Icon: FC<IconProps> = (props) => {
    if (props.iconType === 'img') {
        return (
            <img
                src={`/icons/${props.imageName}.png`}
                height={props.iconHeight !== undefined ? props.iconHeight : '24'}
                width={props.iconWidth !== undefined ? props.iconWidth : '24'}
                title={props.iconTitle}
            />
        );
    }

    return <IconSvg {...props} icon={props.icon} />;
};

/* @component */
export default Icon;
