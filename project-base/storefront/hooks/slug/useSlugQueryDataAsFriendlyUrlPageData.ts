import { SlugQueryApi } from 'graphql/requests/slugs/queries/slugQuery.generated';
import { FriendlyUrlPageType } from 'types/friendlyUrl';

export const useSlugQueryDataAsFriendlyUrlPageData = (
    slugQueryData: SlugQueryApi['slug'] | undefined,
): FriendlyUrlPageType | null | undefined => (slugQueryData?.__typename === 'Variant' ? null : slugQueryData);
