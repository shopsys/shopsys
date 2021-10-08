import { BlogCategoryType } from 'connectors/blogCategory/BlogCategory';
import { FC } from 'react';
import Webline from 'components/Layout/Webline';

type BlogCategoryProps = {
    blogCategory: BlogCategoryType;
};

const BlogCategory: FC<BlogCategoryProps> = (props) => {
    return <Webline>Blog page: {props.blogCategory.blogCategoryName}</Webline>;
};

export default BlogCategory;
