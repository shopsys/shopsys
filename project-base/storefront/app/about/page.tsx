import { getUrqlData } from 'app/_urql/urql-dto';
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
import { Client, OperationResult } from 'urql';
import { getServerT } from 'utils/getServerTranslation';

async function getArticle(client: Client) {
    const blogArticleResponse: OperationResult<TypeBlogArticleDetailQuery, TypeBlogArticleDetailQueryVariables> =
        await client.query(BlogArticleDetailQueryDocument, {
            urlSlug: 'blog-article-example-2-en',
        });

    return blogArticleResponse;
}

async function getCustomerUser(client: Client) {
    const customerUserResponse: OperationResult<TypeCurrentCustomerUserQuery, TypeCurrentCustomerUserQueryVariables> =
        await client.query(CurrentCustomerUserQueryDocument, {});

    return customerUserResponse;
}

export default async function IndexPage() {
    const t = await getServerT();
    const client = await getUrqlData();
    const { data } = await getArticle(client);
    const { data: customerUserData } = await getCustomerUser(client);

    return (
        <div>
            <h1>{data?.blogArticle?.name}</h1>
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
