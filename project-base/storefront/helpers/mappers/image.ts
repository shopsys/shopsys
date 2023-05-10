import { ImageSizesFragmentApi } from 'graphql/generated';

export const getFirstImageOrNull = (images: ImageSizesFragmentApi[]): ImageSizesFragmentApi | null => images[0] ?? null;
