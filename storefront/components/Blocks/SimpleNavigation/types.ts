import { ImageType } from 'components/Basic/Image/types';

export type ListedItemPropType =
    | {
          slug: string;
          image: ImageType | null;
          name: string;
          totalCount?: number;
      }
    | {
          slug: string;
          image: ImageType | null;
          name: string;
      };
