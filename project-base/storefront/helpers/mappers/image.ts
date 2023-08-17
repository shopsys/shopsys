import { ImageSizesFragmentApi } from 'graphql/requests/images/fragments/ImageSizesFragment.generated';

export const getFirstImageOrNull = (images: ImageSizesFragmentApi[]): ImageSizesFragmentApi | null => images[0] ?? null;
