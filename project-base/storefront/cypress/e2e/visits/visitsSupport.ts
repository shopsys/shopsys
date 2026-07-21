import { staticData } from 'fixtures/demodata';
import { changeElementText } from 'support';
import { TIDs } from 'tids';

export const changeBlogArticleDynamicPartsToStaticDemodata = () => {
    changeElementText(TIDs.blog_article_publication_date, staticData.blogArticle.publicationDate, false);
};

export const changeArticleDynamicPartsToStaticDemodata = () => {
    changeElementText(TIDs.article_creation_date, staticData.article.creationDate, false);
};
