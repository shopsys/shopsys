import PropTypes, { InferProps } from 'prop-types';
import { ReactElement } from 'react';
import * as TEST from '../../../public/svg';

/**
 *  Basic icon component unifies displaying icons
 *  className .icon is used for eliminating img reset (max-width: 100%, height: auto)
 */
function Icon(props: InferProps<typeof Icon.propTypes>): ReactElement {
    let iconSrc = '';
    let IconElement = '';

    if (props.iconType === 'svg') {
        iconSrc = `/svg/${props.icon}.svg`;
        IconElement = 'svg';
    }

    if (props.iconType === 'png') {
        iconSrc = `/icons/${props.icon}.png`;
        IconElement = 'img';
    }

    return <IconElement className="icon" src={iconSrc} height={props.iconHeight} title={props.iconTitle} />;
}

Icon.defaultProps = {
    iconType: 'svg',
    iconHeight: 24,
    iconTitle: '',
};

Icon.propTypes = {
    /**
     * String for title attribute for generating tooltip text
     */
    iconTitle: PropTypes.string.isRequired,
    /**
     * Define which type of icon will be generated
     *
     * @param {string} svg will generate image from public/svg/[icon].svg file<br>
     * @param {string} png will generate image from public/icons/[icon].png file
     */
    iconType: PropTypes.oneOf<'svg' | 'png'>(['svg', 'png']).isRequired,
    /**
     * Define name of icon file without extension
     */
    icon: PropTypes.string.isRequired,
    /**
     * Icon height in px, width is auto
     */
    iconHeight: PropTypes.number.isRequired,
};

/* @component */
export default Icon;
