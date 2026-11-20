import { twJoin } from 'tailwind-merge';

export type LinkPlacement = 'inline' | 'standalone' | 'list';

export type LinkTone = 'default' | 'accent' | 'subtle' | 'inverted';

export type LinkStyleOptions = {
    placement?: LinkPlacement;
    tone?: LinkTone;
};

const LINK_TONE_CLASS_NAMES: Record<LinkTone, string> = {
    default: 'text-text-default hover:text-text-default active:text-text-default',
    accent: 'text-link-default hover:text-link-hovered active:text-link-hovered',
    subtle: 'text-text-less hover:text-link-hovered active:text-link-hovered',
    inverted: 'text-link-inverted-default hover:text-link-inverted-hovered active:text-link-inverted-hovered',
};

const LINK_PLACEMENT_CLASS_NAMES: Record<LinkPlacement, string> = {
    // keeps the font of the sentence it is part of
    inline: '',
    // one typography for every text action outside a sentence; a trailing 16px icon sits 4px after the text
    standalone: 'inline-flex items-center gap-1 font-secondary font-semibold text-sm',
    // typography comes from the menu or list the link belongs to
    list: '',
};

/**
 * Style recipe of every text link and text action (`Link`, `TextButton`); prefer those components over calling it.
 * Pick the placement by asking where the link is:
 * - `inline`: inside a sentence. It inherits the font of the text, is always underlined. Default tone `accent`.
 * - `standalone`: a text action on its own (show more, open description, review count, policy under a button).
 *   Fixed typography, always underlined. Default tone `accent`, `subtle` for secondary actions.
 * - `list`: among similar links (menu, footer column, breadcrumbs). Underlined on hover. Default tone `default`.
 * Every underlined link thickens its underline on hover. A `subtle` link is always underlined, because muted text
 * without an underline reads as plain text. `inverted` is for dark backgrounds.
 */
export const getLinkClassName = ({ placement = 'inline', tone }: LinkStyleOptions = {}) => {
    const isAlwaysUnderlined = placement !== 'list' || tone === 'subtle';

    return twJoin(
        'cursor-pointer rounded-sm',
        LINK_PLACEMENT_CLASS_NAMES[placement],
        isAlwaysUnderlined
            ? 'underline decoration-1 hover:underline hover:decoration-2'
            : 'no-underline hover:underline',
        LINK_TONE_CLASS_NAMES[tone ?? (placement === 'list' ? 'default' : 'accent')],
    );
};
