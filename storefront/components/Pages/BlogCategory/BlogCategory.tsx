import { BlogCategoryListStyled, BlogCategoryPanelStyled, BlogCategoryStyled } from './BlogCategory.style';
import { BlogCategoryType } from 'connectors/blogCategory/types';
import BlogSignpost from 'components/Blocks/BlogSignpost';
import { FC } from 'react';
import { getBlogCategoriesItems } from 'connectors/blogCategories/BlogCategories';
import Heading from 'components/Basic/Heading';
import List from './List';
import Pagination from 'components/Blocks/Pagination/Pagination';
import Webline from 'components/Layout/Webline';

type BlogCategoryProps = {
    blogCategory: BlogCategoryType;
};

const BlogCategory: FC<BlogCategoryProps> = (props) => {
    const blogCategoriesItems = getBlogCategoriesItems();

    return (
        <Webline>
            <Heading type="h1">{props.blogCategory.blogCategoryName}</Heading>
            <BlogCategoryStyled>
                <BlogCategoryListStyled>
                    <List blogArticles={props.blogCategory.blogArticles} />
                    <Pagination totalCount={props.blogCategory.blogArticles.totalCount} />
                </BlogCategoryListStyled>
                <BlogCategoryPanelStyled>
                    <BlogSignpost blogCategoriesItems={blogCategoriesItems} activeItem={props.blogCategory.uuid} />
                </BlogCategoryPanelStyled>
            </BlogCategoryStyled>
        </Webline>
    );
};

export default BlogCategory;
