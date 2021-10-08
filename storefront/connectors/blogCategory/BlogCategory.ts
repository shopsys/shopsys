import { BreadcrumbType } from 'connectors/breadcrumb/Breadcrumb';
import { SlugType } from 'connectors/slug/Slug';
import { v4 as uuid } from 'uuid';

export const blogCategoryBody = `
    uuid      
    blogCategoryName: name
    blogArticles {
        edges {
            node {
                name
                uuid
            }
        }
    }
    breadcrumb {
        name
        slug
    }` as const;

export interface BlogCategoryType extends SlugType, BreadcrumbType {
    uuid: typeof uuid;
    blogCategoryName: string;
    blogArticles: {
        edges: {
            node: {
                uuid: typeof uuid;
                name: string;
            };
        };
    };
}
