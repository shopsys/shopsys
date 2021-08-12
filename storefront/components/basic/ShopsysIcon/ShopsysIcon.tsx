import PropTypes, { InferProps } from 'prop-types';
import { ReactElement } from 'react';

/**
 *  Basic icon component unifies displaying icons
 *  className .icon is used for eliminating img reset (max-width: 100%, height: auto)
 */
function ShopsysIcon(props: InferProps<typeof ShopsysIcon.propTypes>): ReactElement {
    let iconSrc = '';

    if (props.iconType === 'svg') {
        iconSrc = `/svg/${props.icon}.svg`;
    }

    if (props.iconType === 'png') {
        iconSrc = `/icons/${props.icon}.png`;
    }

    return <img className="icon" src={iconSrc} height={props.iconHeight} title={props.iconTitle} />;
}

ShopsysIcon.defaultProps = {
    iconType: 'svg',
    iconHeight: 24,
    iconTitle: '',
};

ShopsysIcon.propTypes = {
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
export default ShopsysIcon;
