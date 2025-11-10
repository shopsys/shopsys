// Fetches entity by UUID and visits its page - handles different entity types with their specific GraphQL requirements
export const visitEntityByUuid = (entityType: string, uuid: string): Cypress.Chainable<void> => {
    // Different entity types require different query approaches
    if (entityType === 'blogCategory') {
        return visitBlogCategoryByUuid(uuid);
    }

    if (entityType === 'article') {
        return visitArticleByUuid(uuid);
    }

    if (entityType === 'blogArticle') {
        return visitBlogArticleByUuid(uuid);
    }

    // Default: category, product, brand, flag, store
    return visitDefaultEntityByUuid(entityType, uuid);
};

// Blog categories use a different query structure - fetch all and find by UUID
const visitBlogCategoryByUuid = (uuid: string): Cypress.Chainable<void> => {
    const query = `
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

    return cy
        .request({
            method: 'POST',
            url: 'graphql/',
            headers: { 'Content-Type': 'application/json' },
            body: { query },
            failOnStatusCode: false,
        })
        .then((response) => {
            const body = parseResponse(response);
            const allCategories = flattenBlogCategories(body.data?.blogCategories ?? []);
            const category = allCategories.find((cat) => cat.uuid === uuid);

            if (!category?.link) {
                throw new Error(`Blog category with UUID "${uuid}" not found`);
            }

            cy.visitAndWaitForStableAndInteractiveDOM(category.link);
        }) as unknown as Cypress.Chainable<void>;
};

// Articles require inline fragment to access ArticleSite fields
const visitArticleByUuid = (uuid: string): Cypress.Chainable<void> => {
    const query = `
        query GetArticleUrl($uuid: Uuid!) {
            article(uuid: $uuid) {
                ... on ArticleSite {
                    slug
                }
            }
        }
    `;

    return visitEntityWithQuery('article', query, uuid);
};

// Blog articles have both slug and link fields
const visitBlogArticleByUuid = (uuid: string): Cypress.Chainable<void> => {
    const query = `
        query GetBlogArticleUrl($uuid: Uuid!) {
            blogArticle(uuid: $uuid) {
                slug
                link
            }
        }
    `;

    return visitEntityWithQuery('blogArticle', query, uuid);
};

// Default entities (category, product, brand, flag, store) only have slug field
const visitDefaultEntityByUuid = (entityType: string, uuid: string): Cypress.Chainable<void> => {
    const capitalizedType = entityType.charAt(0).toUpperCase() + entityType.slice(1);
    const query = `
        query Get${capitalizedType}Url($uuid: Uuid!) {
            ${entityType}(uuid: $uuid) {
                slug
            }
        }
    `;

    return visitEntityWithQuery(entityType, query, uuid);
};

// Generic function to execute GraphQL query and visit the resulting URL
const visitEntityWithQuery = (entityType: string, query: string, uuid: string): Cypress.Chainable<void> => {
    return cy
        .request({
            method: 'POST',
            url: 'graphql/',
            headers: { 'Content-Type': 'application/json' },
            body: {
                query,
                variables: { uuid },
            },
            failOnStatusCode: false,
        })
        .then((response) => {
            const body = parseResponse(response);

            if (body.errors || !body.data?.[entityType]) {
                const errorMsg = body.errors ? JSON.stringify(body.errors, null, 2) : 'No data returned';
                throw new Error(`Failed to fetch ${entityType} with UUID "${uuid}"\nError: ${errorMsg}`);
            }

            const entityData = body.data[entityType];
            const url = buildUrlFromEntityData(entityData);

            if (!url) {
                throw new Error(`No URL found for ${entityType} with UUID "${uuid}"`);
            }

            cy.visitAndWaitForStableAndInteractiveDOM(url);
        }) as unknown as Cypress.Chainable<void>;
};

// Builds URL from entity data - uses link if available, otherwise slug
const buildUrlFromEntityData = (entityData: any): string | null => {
    if (entityData.link) {
        return entityData.link;
    }

    if (entityData.slug) {
        // Backend returns slugs with leading slash, remove it before building URL
        const cleanSlug = entityData.slug.startsWith('/') ? entityData.slug.slice(1) : entityData.slug;
        return `/${cleanSlug}`;
    }

    return null;
};

// Flattens nested blog category tree into a flat array
const flattenBlogCategories = (categories: any[]): any[] => {
    return categories.flatMap((category) => [
        category,
        ...(category.children ? flattenBlogCategories(category.children) : []),
    ]);
};

// Parses GraphQL response (handles both string and object responses)
const parseResponse = (response: any): any => {
    return typeof response.body === 'string' ? JSON.parse(response.body) : response.body;
};
