import { BlogCategoryListStyled, BlogCategoryPanelStyled, BlogCategoryStyled } from './BlogCategory.style';
import { BlogCategoryType } from 'connectors/blogCategory/BlogCategory';
import BlogSignpost from 'components/Blocks/BlogSignpost';
import { FC } from 'react';
import { getBlogCategoriesItems } from 'connectors/blogCategories/BlogCategories';
import Heading from 'components/Basic/Heading';
import List from './List';
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
                </BlogCategoryListStyled>
                <BlogCategoryPanelStyled>
                    <BlogSignpost blogCategoriesItems={blogCategoriesItems} activeItem={props.blogCategory.uuid} />
                </BlogCategoryPanelStyled>
            </BlogCategoryStyled>
        </Webline>
    );
};

export default BlogCategory;
