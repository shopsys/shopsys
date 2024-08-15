import { blogArticle } from 'fixtures/demodata';
import { changeElementText } from 'support';
import { TIDs } from 'tids';

export const changeBlogArticleDynamicPartsToStaticDemodata = () => {
    changeElementText(TIDs.blog_article_publication_date, blogArticle.publicationDate, false);
};
