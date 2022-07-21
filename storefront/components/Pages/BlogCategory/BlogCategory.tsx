import BlogArticlesList from './BlogArticlesList';
import { BlogCategoryListStyled, BlogCategoryPanelStyled, BlogCategoryStyled } from './BlogCategory.style';
import Heading from 'components/Basic/Heading';
import BlogSignpost from 'components/Blocks/BlogSignpost';
import Pagination from 'components/Blocks/Pagination/Pagination';
import Webline from 'components/Layout/Webline';
import { FC, useRef } from 'react';
import { BlogCategoryDetailType } from 'types/blogCategory';

type BlogCategoryProps = {
    blogCategory: BlogCategoryDetailType;
};

const BlogCategory: FC<BlogCategoryProps> = (props) => {
    const containerWrapRef = useRef<null | HTMLDivElement>(null);

    return (
        <Webline>
            <div ref={containerWrapRef}>
                <Heading type="h1">{props.blogCategory.name}</Heading>
                <BlogCategoryStyled>
                    {props.blogCategory.blogArticles !== null && (
                        <BlogCategoryListStyled>
                            <BlogArticlesList blogArticles={props.blogCategory.blogArticles} />
                            <Pagination
                                totalCount={props.blogCategory.blogArticles.totalCount}
                                containerWrapRef={containerWrapRef}
                            />
                        </BlogCategoryListStyled>
                    )}
                    <BlogCategoryPanelStyled>
                        <BlogSignpost
                            blogCategoryItems={props.blogCategory.blogCategoriesTree}
                            activeItem={props.blogCategory.uuid}
                        />
                    </BlogCategoryPanelStyled>
                </BlogCategoryStyled>
            </div>
        </Webline>
    );
};

export default BlogCategory;
