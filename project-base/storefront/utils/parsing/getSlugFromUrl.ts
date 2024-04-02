import { getUrlWithoutGetParameters } from './getUrlWithoutGetParameters';
import { getStringWithoutLeadingSlash } from 'utils/parsing/stringWIthoutSlash';

export const getSlugFromUrl = (originalUrl: string): string => {
    return getStringWithoutLeadingSlash(getUrlWithoutGetParameters(originalUrl));
};
