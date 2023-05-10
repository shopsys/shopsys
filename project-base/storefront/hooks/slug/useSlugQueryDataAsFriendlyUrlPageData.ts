import { SlugQueryApi } from 'graphql/generated';
import { FriendlyUrlPageType } from 'types/friendlyUrl';

export const useSlugQueryDataAsFriendlyUrlPageData = (
    slugQueryData: SlugQueryApi['slug'] | undefined,
): FriendlyUrlPageType | null | undefined => (slugQueryData?.__typename === 'Variant' ? null : slugQueryData);
