import { ImageType } from 'types/image';

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
