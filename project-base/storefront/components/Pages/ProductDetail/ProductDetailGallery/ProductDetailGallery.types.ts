import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeVideoTokenFragment } from 'graphql/requests/products/fragments/VideoTokenFragment.generated';

export type ProductDetailGalleryItem = TypeImageFragment | TypeVideoTokenFragment;
