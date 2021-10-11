import { FC, Fragment } from 'react';
import {
    ListItemContentDateStyled,
    ListItemContentStyled,
    ListItemContentTextStyled,
    ListItemImageStyled,
    ListItemStyled,
    ListItemTitleStyled,
    ListStyled,
} from './List.style';
import { BlogArticlesType } from 'connectors/blogCategory/BlogCategory';
import Flag from 'components/Basic/Flag';
import Heading from 'components/Basic/Heading';
import Image from 'components/Basic/Image';
import { useShopsysSelector } from 'redux/main';

type ListProps = {
    blogArticles: BlogArticlesType;
};

const List: FC<ListProps> = (props) => {
    const currentDomainConfig = useShopsysSelector((state) => state.domain);

    return (
        <ListStyled>
            {props.blogArticles.edges.map((blogArticle) => (
                <ListItemStyled key={blogArticle.uuid}>
                    <ListItemImageStyled>
                        <a href={blogArticle.link}>
                            <Image image={blogArticle.image} alt={blogArticle.name} />
                        </a>
                    </ListItemImageStyled>
                    <ListItemContentStyled>
                        <div>
                            {blogArticle.blogCategories.map((blogArticleCategory) => (
                                <Fragment key={blogArticleCategory.uuid}>
                                    {blogArticleCategory.parent !== null && (
                                        <Flag href={blogArticleCategory.link} color="#cdb3ff">
                                            {blogArticleCategory.name}
                                        </Flag>
                                    )}
                                </Fragment>
                            ))}
                        </div>
                        <ListItemTitleStyled href={blogArticle.node.link}>
                            <Heading type="h2">{blogArticle.node.name}</Heading>
                        </ListItemTitleStyled>
                        {blogArticle.perex !== undefined && (
                            <ListItemContentTextStyled>{blogArticle.perex}</ListItemContentTextStyled>
                        )}
                        <ListItemContentDateStyled>
                            {new Date(blogArticle.createdAt).toLocaleDateString(currentDomainConfig.defaultLocale)}
                        </ListItemContentDateStyled>
                    </ListItemContentStyled>
                </ListItemStyled>
            ))}
        </ListStyled>
    );
};

export default List;
