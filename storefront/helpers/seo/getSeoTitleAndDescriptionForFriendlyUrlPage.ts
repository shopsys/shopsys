import { FriendlyUrlPageType } from 'types/friendlyUrl';

type ReturnType = { title: string | null; description: string | null };

export const getSeoTitleAndDescriptionForFriendlyUrlPage = (data: FriendlyUrlPageType): ReturnType => {
    let title: string | null;
    let description: string | null = null;

    switch (data.__typename) {
        case 'Store':
            title = data.storeName;
            break;
        case 'ArticleSite':
            title = data.articleName;
            break;
        default:
            title = data.name;
    }

    if ('seoTitle' in data && data.seoTitle !== null) {
        title = data.seoTitle;
    }

    if ('seoMetaDescription' in data && data.seoMetaDescription !== null) {
        description = data.seoMetaDescription;
    }

    return {
        title,
        description,
    };
};
