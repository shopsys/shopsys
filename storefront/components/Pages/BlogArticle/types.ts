import { BreadcrumbItemType } from 'connectors/breadcrumb/Breadcrumb';
import { ImageType } from 'components/Basic/Image/types';
import { SliderProductItemType } from 'components/Blocks/Product/types';

export type BlogArticleDetailType = {
    __typename: string | undefined;
    breadcrumb: BreadcrumbItemType[];
    uuid: string;
    name: string;
    text: string | null;
    publishDate: string;
    slug: string;
    link: string;
    image: ImageType | null;
    blogArticleProducts: SliderProductItemType[];
};
