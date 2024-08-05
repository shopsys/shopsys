import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';

export type ListedItemPropTypeTypename = 'ArticleSite' | 'BlogArticle' | 'Category' | 'Brand' | 'Link';

export type ListedItemPropType = (
    | {
          slug: string;
          mainImage: TypeImageFragment;
          name: string;
          totalCount?: number;
      }
    | {
          slug: string;
          mainImage: TypeImageFragment;
          name: string;
      }
    | {
          slug: string;
          name: string;
      }
    | {
          slug: string;
          name: string;
          icon: JSX.Element;
      }
) & {
    __typename?: ListedItemPropTypeTypename;
};
