import { ImageApiType, ImageType } from 'components/Basic/Image/types';
import { useFetchQuery } from 'hooks/graphQl/UseFetchQuery';

export const blogPreviewQuery = `
query blogArticles {
    blogArticles(first: 6, onlyHomepageArticles: true) {
        edges {
            node {
                name
                link
                perex
                image(sizes: "list") {
                    sizes {
                        size
                        url
                        width
                        height
                    }
                }
                blogCategories {
                    name
                    link
                    parent {
                        name
                    }
                }
            }
        }
    }
} ` as const;

type BlogPreviewCategory = {
    name: string;
    link: string;
    parent: {
        name: string;
    };
};

export type BlogPreviewType = {
    name: string;
    link: string;
    perex: string;
    image: ImageType | null;
    blogCategories: BlogPreviewCategory[];
};

type BlogPreviewApiData = {
    node: {
        name: string;
        link: string;
        perex: string;
        image: ImageApiType;
        blogCategories: BlogPreviewCategory[];
    };
};

const mapBlogPreview = (blogArticles: BlogPreviewApiData[]): BlogPreviewType[] => {
    const mappedBlogPreviewArticles = [];
    for (const blogArticle of blogArticles) {
        mappedBlogPreviewArticles.push({
            ...blogArticle.node,
            image: blogArticle.node.image !== null ? blogArticle.node.image.sizes[0] : null,
        });
    }

    return mappedBlogPreviewArticles;
};

export const getBlogPreviewArticles = (): BlogPreviewType[] => {
    const result = useFetchQuery({ query: blogPreviewQuery });
    const blogPreviewApiData = result?.data?.blogArticles.edges;

    if (blogPreviewApiData === undefined) {
        return [];
    }

    return mapBlogPreview(blogPreviewApiData);
};
