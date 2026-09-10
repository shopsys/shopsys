import { MetaRobotsContent } from 'types/seo';

/**
 * Value set in the administration is the source of truth: SEO page (override for the URL) first, then the entity
 * (product, category, ...). Only when the administrator leaves robots unset ("Default (not set)") the storefront
 * applies its own rules passed as the default (e.g. noindex for the cart or for a filtered product listing).
 */
export const resolveMetaRobots = (
    seoPageMetaRobots: string | null | undefined,
    entityMetaRobots: string | null | undefined,
    defaultMetaRobots: MetaRobotsContent | undefined,
): string | null => seoPageMetaRobots || entityMetaRobots || defaultMetaRobots || null;
