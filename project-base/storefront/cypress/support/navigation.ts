import { fetchBlogCategoryLink, normalizeSlug } from './entityData';
import { executeGraphqlQuery } from './graphql';

export const visitEntityByUuid = (entityType: string, uuid: string): Cypress.Chainable<void> => {
    if (entityType === 'blogCategory') {
        return visitBlogCategoryByUuid(uuid);
    }

    if (entityType === 'article') {
        return visitArticleByUuid(uuid);
    }

    if (entityType === 'blogArticle') {
        return visitBlogArticleByUuid(uuid);
    }

    return visitDefaultEntityByUuid(entityType, uuid);
};

const visitBlogCategoryByUuid = (uuid: string): Cypress.Chainable<void> => {
    return fetchBlogCategoryLink(uuid).then((link) => {
        cy.visitAndWaitForStableAndInteractiveDOM(link);
    }) as unknown as Cypress.Chainable<void>;
};

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

const visitEntityWithQuery = (entityType: string, query: string, uuid: string): Cypress.Chainable<void> => {
    return executeGraphqlQuery(query, { uuid }).then((body) => {
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

const buildUrlFromEntityData = (entityData: any): string | null => {
    if (entityData.link) {
        return entityData.link;
    }

    if (entityData.slug) {
        return normalizeSlug(entityData.slug);
    }

    return null;
};
