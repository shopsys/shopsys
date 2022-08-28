import { BlogArticlesList } from './BlogArticlesList/BlogArticlesList';
import { BlogCategoryListStyled, BlogCategoryPanelStyled, BlogCategoryStyled } from './BlogCategoryContent.style';
import { Heading } from 'components/Basic/Heading/Heading';
import { BlogSignpost } from 'components/Blocks/BlogSignpost/BlogSignpost';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { Webline } from 'components/Layout/Webline/Webline';
import { FC, useRef } from 'react';
import { BlogCategoryDetailType } from 'types/blogCategory';

type BlogCategoryContentProps = {
    blogCategory: BlogCategoryDetailType;
};

export const BlogCategoryContent: FC<BlogCategoryContentProps> = ({ blogCategory }) => {
    const containerWrapRef = useRef<null | HTMLDivElement>(null);

    return (
        <Webline>
            <div ref={containerWrapRef}>
                <Heading type="h1">{blogCategory.name}</Heading>
                <BlogCategoryStyled>
                    {blogCategory.blogArticles !== null && (
                        <BlogCategoryListStyled>
                            <BlogArticlesList blogArticles={blogCategory.blogArticles} />
                            <Pagination
                                totalCount={blogCategory.blogArticles.totalCount}
                                containerWrapRef={containerWrapRef}
                            />
                        </BlogCategoryListStyled>
                    )}
                    <BlogCategoryPanelStyled>
                        <BlogSignpost
                            blogCategoryItems={blogCategory.blogCategoriesTree}
                            activeItem={blogCategory.uuid}
                        />
                    </BlogCategoryPanelStyled>
                </BlogCategoryStyled>
            </div>
        </Webline>
    );
};
