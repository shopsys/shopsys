import { ImageSizesFragmentApi } from 'graphql/generated';

export type ListedItemPropType = (
    | {
          slug: string;
          mainImage: ImageSizesFragmentApi;
          name: string;
          totalCount?: number;
      }
    | {
          slug: string;
          mainImage: ImageSizesFragmentApi;
          name: string;
      }
    | {
          slug: string;
          name: string;
      }
) & {
    __typename?: 'ArticleSite' | 'BlogArticle' | 'Category' | 'Brand' | 'Link';
};
