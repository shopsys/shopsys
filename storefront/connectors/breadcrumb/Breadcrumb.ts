type BreadcrumbItemType = {
    __typename: string;
    name: string;
    slug: string;
};

export type BreadcrumbType = {
    breadcrumb: BreadcrumbItemType[];
};
