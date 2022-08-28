import {
    ListItemContentDateStyled,
    ListItemContentStyled,
    ListItemContentTextStyled,
    ListItemImageStyled,
    ListItemStyled,
    ListItemTitleStyled,
    ListStyled,
} from './BlogArticlesList.style';
import { Flag } from 'components/Basic/Flag/Flag';
import { Heading } from 'components/Basic/Heading/Heading';
import { Image } from 'components/Basic/Image/Image';
import NextLink from 'next/link';
import { FC, Fragment } from 'react';
import { useShopsysSelector } from 'redux/main';
import { BlogArticleConnectionType } from 'types/blogArticle';

type BlogArticlesListProps = {
    blogArticles: BlogArticleConnectionType;
};

const TEST_IDENTIFIER = 'pages-blogcategory-blogarticleslist-';

export const BlogArticlesList: FC<BlogArticlesListProps> = ({ blogArticles }) => {
    const currentDomainConfig = useShopsysSelector((state) => state.domain);

    return (
        <ListStyled>
            {blogArticles.edges.map((blogArticle, blogArticleIndex) => (
                <ListItemStyled key={blogArticle.uuid} data-testid={TEST_IDENTIFIER + blogArticleIndex}>
                    <ListItemImageStyled data-testid={TEST_IDENTIFIER + blogArticleIndex + '-image'}>
                        <NextLink href={blogArticle.link} passHref>
                            <a>
                                <Image image={blogArticle.image} type="list" alt={blogArticle.name} />
                            </a>
                        </NextLink>
                    </ListItemImageStyled>
                    <ListItemContentStyled>
                        <div>
                            {blogArticle.blogCategories.map((blogArticleCategory, blogArticleCategoryIndex) => (
                                <Fragment key={blogArticleCategory.uuid}>
                                    {blogArticleCategory.parent !== null && (
                                        <Flag
                                            href={blogArticleCategory.link}
                                            color="#cdb3ff"
                                            testIdentifier={
                                                TEST_IDENTIFIER +
                                                blogArticleIndex +
                                                '-section-' +
                                                blogArticleCategoryIndex
                                            }
                                        >
                                            {blogArticleCategory.name}
                                        </Flag>
                                    )}
                                </Fragment>
                            ))}
                        </div>
                        <NextLink href={blogArticle.link} passHref>
                            <ListItemTitleStyled data-testid={TEST_IDENTIFIER + blogArticleIndex + '-title'}>
                                <Heading type="h2">{blogArticle.name}</Heading>
                            </ListItemTitleStyled>
                        </NextLink>
                        {blogArticle.perex !== null && (
                            <ListItemContentTextStyled data-testid={TEST_IDENTIFIER + blogArticleIndex + '-perex'}>
                                {blogArticle.perex}
                            </ListItemContentTextStyled>
                        )}
                        <ListItemContentDateStyled data-testid={TEST_IDENTIFIER + blogArticleIndex + '-date'}>
                            {new Date(blogArticle.publishDate).toLocaleDateString(currentDomainConfig.defaultLocale)}
                        </ListItemContentDateStyled>
                    </ListItemContentStyled>
                </ListItemStyled>
            ))}
        </ListStyled>
    );
};
