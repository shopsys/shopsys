'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    ArticleDetailQueryDocument,
    TypeArticleDetailQuery,
    TypeArticleDetailQueryVariables,
} from 'graphql/requests/articles/queries/ArticleDetailQuery.ssr';

export async function getArticleDetailQuery(articleSlug: string) {
    const result = await createQuery<TypeArticleDetailQuery, TypeArticleDetailQueryVariables>(
        ArticleDetailQueryDocument,
        {
            urlSlug: articleSlug,
        },
    );

    return result.data?.article;
}
