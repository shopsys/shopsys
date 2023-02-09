import { ImageSizesFragmentApi } from 'graphql/generated';

export const getFirstImageOrNull = (images: ImageSizesFragmentApi[]): ImageSizesFragmentApi | null =>
    images.at(0) ?? null;
