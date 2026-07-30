import { initializePersistStoreInLocalStorageToDefaultValues } from 'support';
import { TIDs } from 'tids';

const ARTICLE_INTRODUCTION_ID = 'article-introduction';
const BLOG_ARTICLE_PATH = '/how-to-choose-the-right-tv-for-your-living-room';
const MOBILE_VIEWPORT_HEIGHT = 667;
const MOBILE_VIEWPORT_WIDTH = 375;

describe('Blog Article Tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    it('should display the article heading before the sidebar and article content on mobile', () => {
        cy.viewport(MOBILE_VIEWPORT_WIDTH, MOBILE_VIEWPORT_HEIGHT);
        cy.visitAndWaitForStableAndInteractiveDOM(BLOG_ARTICLE_PATH);

        cy.get(`#${ARTICLE_INTRODUCTION_ID}`).should('be.visible').then(($articleHeading) => {
            const articleHeadingRect = $articleHeading[0].getBoundingClientRect();

            expect(articleHeadingRect.bottom).to.be.lessThan(MOBILE_VIEWPORT_HEIGHT);

            cy.getByTID([TIDs.blog_sidebar]).then(($blogSidebar) => {
                const blogSidebarTop = $blogSidebar[0].getBoundingClientRect().top;

                cy.getByTID([TIDs.blog_article_content]).should(($articleContent) => {
                    const articleContentTop = $articleContent[0].getBoundingClientRect().top;

                    expect(articleHeadingRect.top).to.be.lessThan(blogSidebarTop);
                    expect(blogSidebarTop).to.be.lessThan(articleContentTop);
                });
            });
        });
    });
});
