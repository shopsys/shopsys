import { FriendlyPagesTypes, FriendlyPagesTypesKey, FriendlyPageTypesValue } from 'types/friendlyUrl';

export const getPageTypeKey = (friendlyPageType: FriendlyPageTypesValue | null): FriendlyPagesTypesKey | undefined => {
    return (Object.keys(FriendlyPagesTypes) as FriendlyPagesTypesKey[]).find(
        (key) => FriendlyPagesTypes[key].toLowerCase() === friendlyPageType?.toLowerCase(),
    );
};
