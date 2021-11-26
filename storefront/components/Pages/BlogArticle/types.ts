import { BreadcrumbType } from 'connectors/breadcrumb/Breadcrumb';
import { ImageType } from 'components/Basic/Image/types';
import { SliderProductItemType } from 'components/Blocks/Product/types';
import { SlugType } from 'connectors/slug/Slug';

export interface BlogArticleDetailType extends SlugType, BreadcrumbType {
    __typename: string | undefined;
    uuid: string;
    name: string;
    text: string | null;
    publishDate: string;
    slug: string;
    link: string;
    image: ImageType | null;
    blogArticleProducts: SliderProductItemType[];
}
