import { DEFAULT_SKELETON_TYPE } from 'config/constants';
import { FriendlyPagesDestinations, FriendlyPagesTypesKey } from 'types/friendlyUrl';

export function getSkeletonTypeFromLink(link: string): FriendlyPagesTypesKey {
    const matchingDestination = Object.entries(FriendlyPagesDestinations).find(([, destination]) => {
        const cleanDestination = destination.replace(/\[.*?\]/g, '');

        return link.startsWith(cleanDestination);
    });

    if (matchingDestination) {
        const [pageTypeKey] = matchingDestination;

        return pageTypeKey as FriendlyPagesTypesKey;
    }

    return DEFAULT_SKELETON_TYPE;
}
