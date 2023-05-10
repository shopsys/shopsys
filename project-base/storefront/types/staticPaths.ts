export type StaticRewriteDomainPathsType = {
    [slug in string]: string;
};

export type StaticRewritePathsType = {
    [domain: string]: StaticRewriteDomainPathsType;
};
