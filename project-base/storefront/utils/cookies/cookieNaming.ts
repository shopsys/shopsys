/**
 * Gets the domain-specific cookie name
 * All domains use name-domainId pattern for consistency
 *
 * @param baseName Base cookie name (e.g., 'accessToken', 'refreshToken', 'cookiesStore')
 * @param domainId Domain identifier from domain configuration
 * @returns Domain-specific cookie name
 */
export const getCookieName = (baseName: string, domainId: number): string => {
    return `${baseName}-${domainId}`;
};
