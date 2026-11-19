import { twJoin } from 'tailwind-merge';

type LinkStyleOptions = {
    variant?: 'content' | 'navigation';
    isInverted?: boolean;
    isColored?: boolean;
};

const LINK_COLOR_CLASS_NAME = 'text-link-default hover:text-link-hovered active:text-link-hovered';
const INVERTED_LINK_COLOR_CLASS_NAME =
    'text-link-inverted-default hover:text-link-inverted-hovered active:text-link-inverted-hovered';
const PLAIN_TEXT_COLOR_CLASS_NAME = 'text-text-default hover:text-text-default active:text-text-default';

const getLinkColorClassName = (isInverted: boolean, isColored: boolean) => {
    if (isInverted) {
        return INVERTED_LINK_COLOR_CLASS_NAME;
    }

    return isColored ? LINK_COLOR_CLASS_NAME : PLAIN_TEXT_COLOR_CLASS_NAME;
};

/**
 * Placement decides the default underline, hover is the same everywhere:
 * - `content` (a link inside text or a text action) is always underlined and uses the link color,
 * - `navigation` (a standalone link in a menu, footer or list) gets the underline on hover and keeps the text color,
 *   unless `isColored` asks for the link color (e.g. breadcrumbs or "All categories").
 * On hover every link is underlined; colored links also shift their color.
 */
export const getLinkClassName = ({
    variant = 'content',
    isInverted = false,
    isColored = variant === 'content',
}: LinkStyleOptions = {}) =>
    twJoin(
        'cursor-pointer rounded-sm',
        variant === 'content' ? 'underline hover:underline' : 'no-underline hover:underline',
        getLinkColorClassName(isInverted, isColored),
    );
