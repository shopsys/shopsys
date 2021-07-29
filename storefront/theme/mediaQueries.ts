const sizes = {
    xs: 320,
    sm: 480,
    md: 600,
    lg: 769,
    vl: 1024,
    xl: 1240,
} as const;

const mobileFirst = {
    queryXs: `(min-width: ${sizes.xs}px)`,
    querySm: `(min-width: ${sizes.sm}px)`,
    queryMd: `(min-width: ${sizes.md}px)`,
    queryLg: `(min-width: ${sizes.lg}px)`,
    queryVl: `(min-width: ${sizes.vl}px)`,
    queryXl: `(min-width: ${sizes.xl}px)`,
} as const;

const desktopFirst = {
    queryMobileXs: `(max-width: ${sizes.sm - 1}px)`,
    queryMobile: `(max-width: ${sizes.md - 1}px)`,
    queryTablet: `(max-width: ${sizes.lg - 1}px)`,
    queryNotLargeDesktop: `(max-width: ${sizes.vl - 1}px)`,
} as const;

const mediaQueries = {
    ...mobileFirst,
    ...desktopFirst,
};

export default mediaQueries;
