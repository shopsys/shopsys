import { twJoin } from 'tailwind-merge';

export type LinkVariant = 'text' | 'action' | 'navigation';

export type LinkTone = 'default' | 'accent' | 'subtle' | 'inverted';

export type LinkStyleOptions = {
    variant?: LinkVariant;
    tone?: LinkTone;
};

const LINK_TONE_CLASS_NAMES: Record<LinkTone, string> = {
    default: 'text-text-default hover:text-text-default active:text-text-default',
    accent: 'text-link-default hover:text-link-hovered active:text-link-active',
    subtle: 'text-text-less hover:text-link-hovered active:text-link-active',
    inverted: 'text-link-inverted-default hover:text-link-inverted-hovered active:text-link-inverted-active',
};

const LINK_VARIANT_CLASS_NAMES: Record<LinkVariant, string> = {
    // keeps the font and size of the text it is part of
    text: '',
    // one typography for every text action; a trailing 16px icon sits 4px after the text
    action: 'inline-flex items-center gap-1 font-semibold text-sm',
    // typography comes from the menu or list the link belongs to
    navigation: '',
};

/**
 * Style recipe of every text link and text action (`Link`, `TextButton`); prefer those components over calling it.
 * Pick the variant by asking what the link does:
 * - `text`: a link in the content of the page, inside a sentence or a single link next to text (a policy under a
 *   button, the consent link in the footer). It keeps the font of the text, is always underlined. Default tone `accent`.
 * - `action`: a text action (show more, open description, edit, clear filters, review count). Inter 600 14px,
 *   always underlined. Default tone `accent`, `subtle` for secondary actions.
 * - `navigation`: a link among similar links (menu, footer column, breadcrumbs). Underlined on hover.
 *   Default tone `default`.
 * An underlined link responds to hover by its color, so `default` (no hover color) belongs to navigation only.
 * A `subtle` link is always underlined, because muted text without an underline reads as plain text. `inverted` is
 * for dark backgrounds. Cards are not text links: they react to hover by their surface, never by an underline.
 */
export const getLinkClassName = ({ variant = 'text', tone }: LinkStyleOptions = {}) => {
    const isAlwaysUnderlined = variant !== 'navigation' || tone === 'subtle';
    const resolvedTone = tone ?? (variant === 'navigation' ? 'default' : 'accent');

    return twJoin(
        'cursor-pointer rounded-sm',
        LINK_VARIANT_CLASS_NAMES[variant],
        isAlwaysUnderlined ? 'underline hover:underline' : 'no-underline hover:underline',
        LINK_TONE_CLASS_NAMES[resolvedTone],
    );
};
