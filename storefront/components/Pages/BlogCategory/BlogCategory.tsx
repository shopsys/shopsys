import { BlogCategoryListStyled, BlogCategoryPanelStyled, BlogCategoryStyled } from './BlogCategory.style';
import { FC, useRef } from 'react';
import BlogArticlesList from './BlogArticlesList';
import { BlogCategoryDetailType } from 'types/blogCategory';
import BlogSignpost from 'components/Blocks/BlogSignpost';
import { getBlogCategoryItems } from 'connectors/blogCategory/BlogCategory';
import Heading from 'components/Basic/Heading';
import Pagination from 'components/Blocks/Pagination/Pagination';
import Webline from 'components/Layout/Webline';

type BlogCategoryProps = {
    blogCategory: BlogCategoryDetailType;
};

const BlogCategory: FC<BlogCategoryProps> = (props) => {
    const blogCategoriesItems = getBlogCategoryItems();
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
                        <BlogSignpost blogCategoryItems={blogCategoriesItems} activeItem={props.blogCategory.uuid} />
                    </BlogCategoryPanelStyled>
                </BlogCategoryStyled>
            </div>
        </Webline>
    );
};

export default BlogCategory;
