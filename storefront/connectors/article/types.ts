import { BreadcrumbItemType } from 'connectors/breadcrumb/Breadcrumb';

export type ArticleDetailType = {
    __typename: string;
    slug: string;
    uuid: string;
    placement: string;
    articleName: string;
    text: string | null;
    breadcrumb: BreadcrumbItemType[];
};
