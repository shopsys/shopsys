import { FC, Fragment } from 'react';
import {
    ListItemContentDateStyled,
    ListItemContentStyled,
    ListItemContentTextStyled,
    ListItemImageStyled,
    ListItemStyled,
    ListItemTitleStyled,
    ListStyled,
} from './BlogArticlesList.style';
import { BlogArticleConnectionType } from 'types/blogArticle';
import Flag from 'components/Basic/Flag';
import Heading from 'components/Basic/Heading';
import Image from 'components/Basic/Image';
import { useShopsysSelector } from 'redux/main';

type ListProps = {
    blogArticles: BlogArticleConnectionType;
};

const List: FC<ListProps> = (props) => {
    const testIdentifier = 'pages-blogcategory-blogarticleslist-';

    const currentDomainConfig = useShopsysSelector((state) => state.domain);

    return (
        <ListStyled>
            {props.blogArticles.edges.map((blogArticle, blogArticleIndex) => (
                <ListItemStyled key={blogArticle.uuid} data-testid={testIdentifier + blogArticleIndex}>
                    <ListItemImageStyled data-testid={testIdentifier + blogArticleIndex + '-image'}>
                        <a href={blogArticle.link}>
                            <Image image={blogArticle.image} type="list" alt={blogArticle.name} />
                        </a>
                    </ListItemImageStyled>
                    <ListItemContentStyled>
                        <div>
                            {blogArticle.blogCategories.map((blogArticleCategory, blogArticleCategoryIndex) => (
                                <Fragment key={blogArticleCategory.uuid}>
                                    {blogArticleCategory.parent !== null && (
                                        <Flag
                                            href={blogArticleCategory.link}
                                            color="#cdb3ff"
                                            data-testid={
                                                testIdentifier +
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
                        <ListItemTitleStyled
                            href={blogArticle.link}
                            data-testid={testIdentifier + blogArticleIndex + '-title'}
                        >
                            <Heading type="h2">{blogArticle.name}</Heading>
                        </ListItemTitleStyled>
                        {blogArticle.perex !== null && (
                            <ListItemContentTextStyled data-testid={testIdentifier + blogArticleIndex + '-perex'}>
                                {blogArticle.perex}
                            </ListItemContentTextStyled>
                        )}
                        <ListItemContentDateStyled data-testid={testIdentifier + blogArticleIndex + '-date'}>
                            {new Date(blogArticle.publishDate).toLocaleDateString(currentDomainConfig.defaultLocale)}
                        </ListItemContentDateStyled>
                    </ListItemContentStyled>
                </ListItemStyled>
            ))}
        </ListStyled>
    );
};

export default List;
