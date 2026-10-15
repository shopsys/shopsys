import { ArticleDate } from 'components/Basic/ArticleDate/ArticleDate';
import { ContentRenderer } from 'components/Blocks/ContentEditor/ContentRenderer';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { TypeArticleDetailFragment } from 'graphql/requests/articlesInterface/articles/fragments/ArticleDetailFragment.generated';
import { TypeContentDocument } from 'types/contentEditor';
import { useDocumentProducts } from 'utils/contentEditor/useDocumentProducts';

type ArticleDetailContentProps = {
    article: TypeArticleDetailFragment;
};

export const ArticleDetailContent: FC<ArticleDetailContentProps> = ({ article }) => {
    const content = article.content as TypeContentDocument | null;
    const { products, areProductsFetching } = useDocumentProducts(content);

    return (
        <Webline width="vl">
            <VerticalStack gap="sm">
                <h1>{article.seo.h1 || article.articleName}</h1>

                <ArticleDate date={article.createdAt} tid={TIDs.article_creation_date} />

                <ContentRenderer areProductsFetching={areProductsFetching} document={content} products={products} />
            </VerticalStack>
        </Webline>
    );
};
