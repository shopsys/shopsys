import { ImageFragmentApi } from 'graphql/generated';

export type ListedItemPropTypeTypename = 'ArticleSite' | 'BlogArticle' | 'Category' | 'Brand' | 'Link';

export type ListedItemPropType = (
    | {
          slug: string;
          mainImage: ImageFragmentApi;
          name: string;
          totalCount?: number;
      }
    | {
          slug: string;
          mainImage: ImageFragmentApi;
          name: string;
      }
    | {
          slug: string;
          name: string;
      }
) & {
    __typename?: ListedItemPropTypeTypename;
};
