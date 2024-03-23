import { ImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';

export type ListedItemPropTypeTypename = 'ArticleSite' | 'BlogArticle' | 'Category' | 'Brand' | 'Link';

export type ListedItemPropType = (
    | {
          slug: string;
          mainImage: ImageFragment;
          name: string;
          totalCount?: number;
      }
    | {
          slug: string;
          mainImage: ImageFragment;
          name: string;
      }
    | {
          slug: string;
          name: string;
      }
) & {
    __typename?: ListedItemPropTypeTypename;
};
