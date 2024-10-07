import { TypeBlogArticleConnectionFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleConnectionFragment.generated';
import { TypeListedProductConnectionFragment } from 'graphql/requests/products/fragments/ListedProductConnectionFragment.generated';

export const mergeItemEdges = (
    previousItemEdges?: TypeListedProductConnectionFragment['edges'] | TypeBlogArticleConnectionFragment['edges'],
    newItemEdges?: TypeListedProductConnectionFragment['edges'] | TypeBlogArticleConnectionFragment['edges'],
) => [...(previousItemEdges || []), ...(newItemEdges || [])];
