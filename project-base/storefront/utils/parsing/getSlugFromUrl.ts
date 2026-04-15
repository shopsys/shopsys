import { getStringWithoutLeadingSlash } from 'utils/parsing/stringWIthoutSlash';
import { getUrlWithoutGetParameters } from './getUrlWithoutGetParameters';

export const getSlugFromUrl = (originalUrl: string): string => {
    return getStringWithoutLeadingSlash(getUrlWithoutGetParameters(originalUrl));
};
