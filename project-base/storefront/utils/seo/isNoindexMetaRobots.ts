export const isNoindexMetaRobots = (metaRobots: string | null | undefined): boolean =>
    !!metaRobots && metaRobots.split(',').some((directive) => directive.trim().toLowerCase() === 'noindex');
