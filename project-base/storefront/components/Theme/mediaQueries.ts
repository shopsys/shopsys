export const mobileFirstSizes = {
    xs: 320,
    sm: 480,
    md: 600,
    lg: 769,
    vl: 1024,
    xl: 1240,
} as const;

export const desktopFirstSizes = {
    mobileXs: 479,
    mobile: 599,
    tablet: 768,
    notLargeDesktop: 1023,
} as const;

const mobileFirst = {
    queryXs: `(min-width: ${mobileFirstSizes.xs}px)`,
    querySm: `(min-width: ${mobileFirstSizes.sm}px)`,
    queryMd: `(min-width: ${mobileFirstSizes.md}px)`,
    queryLg: `(min-width: ${mobileFirstSizes.lg}px)`,
    queryVl: `(min-width: ${mobileFirstSizes.vl}px)`,
    queryXl: `(min-width: ${mobileFirstSizes.xl}px)`,
} as const;

const desktopFirst = {
    queryMobileXs: `(max-width: ${desktopFirstSizes.mobileXs}px)`,
    queryMobile: `(max-width: ${desktopFirstSizes.mobile}px)`,
    queryTablet: `(max-width: ${desktopFirstSizes.tablet}px)`,
    queryNotLargeDesktop: `(max-width: ${desktopFirstSizes.notLargeDesktop}px)`,
} as const;

export const mediaQueries = {
    ...mobileFirst,
    ...desktopFirst,
};
