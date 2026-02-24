import { executeGraphqlQuery } from './graphql';

export const normalizeSlug = (slug: string): string => {
    return slug.startsWith('/') ? slug : `/${slug}`;
};

export const fetchEntityData = (
    entityType: string,
    uuid: string,
    fields: string,
): Cypress.Chainable<Record<string, any>> => {
    const capitalizedType = entityType.charAt(0).toUpperCase() + entityType.slice(1);
    const query = `
        query Get${capitalizedType}Data($uuid: Uuid!) {
            ${entityType}(uuid: $uuid) {
                ${fields}
            }
        }
    `;

    return executeGraphqlQuery(query, { uuid }).then((body) => {
        if (body.errors) {
            throw new Error(
                `GraphQL errors fetching ${entityType} (UUID "${uuid}"):\n${JSON.stringify(body.errors, null, 2)}`,
            );
        }

        const data = body.data?.[entityType];

        if (!data) {
            throw new Error(`No data returned for ${entityType} with UUID "${uuid}"`);
        }

        return data;
    });
};

export const fetchEntitySlug = (entityType: string, uuid: string): Cypress.Chainable<string> => {
    const capitalizedType = entityType.charAt(0).toUpperCase() + entityType.slice(1);
    const query = `
        query Get${capitalizedType}Url($uuid: Uuid!) {
            ${entityType}(uuid: $uuid) {
                slug
            }
        }
    `;

    return executeGraphqlQuery(query, { uuid }).then((body) => {
        if (body.errors) {
            throw new Error(
                `GraphQL errors fetching ${entityType} slug (UUID "${uuid}"):\n${JSON.stringify(body.errors, null, 2)}`,
            );
        }

        const slug = body.data?.[entityType]?.slug;

        if (!slug) {
            throw new Error(`No slug returned for ${entityType} with UUID "${uuid}"`);
        }

        return normalizeSlug(slug);
    });
};

export const fetchArticleSlug = (uuid: string): Cypress.Chainable<string> => {
    const query = `
        query GetArticleUrl($uuid: Uuid!) {
            article(uuid: $uuid) {
                ... on ArticleSite {
                    slug
                }
            }
        }
    `;

    return executeGraphqlQuery(query, { uuid }).then((body) => {
        if (body.errors) {
            throw new Error(
                `GraphQL errors fetching article slug (UUID "${uuid}"):\n${JSON.stringify(body.errors, null, 2)}`,
            );
        }

        const slug = body.data?.article?.slug;

        if (!slug) {
            throw new Error(`No slug returned for article with UUID "${uuid}"`);
        }

        return normalizeSlug(slug);
    });
};

const BLOG_CATEGORIES_QUERY = `
    query BlogCategories {
        blogCategories {
            uuid
            link
            children {
                uuid
                link
                children {
                    uuid
                    link
                    children {
                        uuid
                        link
                        children {
                            uuid
                            link
                        }
                    }
                }
            }
        }
    }
`;

export const fetchBlogCategoryLink = (uuid: string): Cypress.Chainable<string> => {
    return executeGraphqlQuery(BLOG_CATEGORIES_QUERY).then((body) => {
        if (body.errors) {
            throw new Error(`GraphQL errors fetching blog categories:\n${JSON.stringify(body.errors, null, 2)}`);
        }

        const allCategories = flattenBlogCategories(body.data?.blogCategories ?? []);
        const category = allCategories.find((cat) => cat.uuid === uuid);

        if (!category?.link) {
            throw new Error(`Blog category with UUID "${uuid}" not found`);
        }

        return category.link;
    });
};

export const flattenBlogCategories = (categories: any[]): any[] => {
    return categories.flatMap((category) => [
        category,
        ...(category.children ? flattenBlogCategories(category.children) : []),
    ]);
};
