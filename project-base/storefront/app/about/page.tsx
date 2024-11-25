import { isNotFoundError } from 'app/_urql/errorExchange';
import { createQuery } from 'app/_urql/urql-dto';
import {
    BlogArticleDetailQueryDocument,
    TypeBlogArticleDetailQuery,
    TypeBlogArticleDetailQueryVariables,
} from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticleDetailQuery.ssr';
import {
    CurrentCustomerUserQueryDocument,
    TypeCurrentCustomerUserQuery,
    TypeCurrentCustomerUserQueryVariables,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.ssr';
import { notFound } from 'next/navigation';
import { getServerT } from 'utils/getServerTranslation';

async function getArticle() {
    return createQuery<TypeBlogArticleDetailQuery, TypeBlogArticleDetailQueryVariables>(
        BlogArticleDetailQueryDocument,
        {
            urlSlug: 'blog-article-example-2-e',
        },
    );
}

async function getCustomerUser() {
    return createQuery<TypeCurrentCustomerUserQuery, TypeCurrentCustomerUserQueryVariables>(
        CurrentCustomerUserQueryDocument,
        {},
    );
}

export default async function IndexPage() {
    const t = await getServerT();
    const [articleResponse, customerUserResponse] = await Promise.all([getArticle(), getCustomerUser()]);

    const { data: articleData, error: articleError } = articleResponse;
    const { data: customerUserData } = customerUserResponse;

    if (isNotFoundError(articleError)) {
        return notFound();
    }

    return (
        <div>
            <h1>{articleData?.blogArticle?.name}</h1>
            <div>
                <p>This text is rendered on the server: {t('Delivery in {{count}} days', { count: 1 })}</p>
                {customerUserData?.currentCustomerUser && (
                    <p>
                        Logged in user:{' '}
                        {`${customerUserData.currentCustomerUser.firstName} ${customerUserData.currentCustomerUser.lastName}`}
                    </p>
                )}
            </div>
        </div>
    );
}
