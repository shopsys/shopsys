import { ImageSizesFragmentApi } from 'graphql/generated';

export type ListedItemPropType =
    | {
          slug: string;
          images: ImageSizesFragmentApi[];
          name: string;
          totalCount?: number;
      }
    | {
          slug: string;
          images: ImageSizesFragmentApi[];
          name: string;
      }
    | {
          slug: string;
          name: string;
      };
