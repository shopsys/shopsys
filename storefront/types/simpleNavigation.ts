import { ImageSizeType } from 'types/image';

export type ListedItemPropType =
    | {
          slug: string;
          image: ImageSizeType | null;
          name: string;
          totalCount?: number;
      }
    | {
          slug: string;
          image: ImageSizeType | null;
          name: string;
      }
    | {
          slug: string;
          name: string;
      };
