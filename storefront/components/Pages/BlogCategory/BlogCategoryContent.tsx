import { BlogCategoryArticlesWrapper } from './BlogCategoryArticlesWrapper';
import { BlogCategoryListStyled, BlogCategoryPanelStyled, BlogCategoryStyled } from './BlogCategoryContent.style';
import { Heading } from 'components/Basic/Heading/Heading';
import { BlogSignpost } from 'components/Blocks/BlogSignpost/BlogSignpost';
import { PaginationProvider } from 'components/Blocks/Pagination/PaginationProvider';
import { Webline } from 'components/Layout/Webline/Webline';
import { getNewPagination } from 'helpers/pagination/getNewPagination';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useRouter } from 'next/router';
import { FC, useRef } from 'react';
import { BlogCategoryDetailType } from 'types/blogCategory';

type BlogCategoryContentProps = {
    blogCategory: BlogCategoryDetailType;
};

export const BlogCategoryContent: FC<BlogCategoryContentProps> = ({ blogCategory }) => {
    const router = useRouter();
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    const currentPage = parsePageNumberFromQuery(router.query[PAGE_QUERY_PARAMETER_NAME]);

    return (
        <PaginationProvider key={blogCategory.uuid} {...getNewPagination(currentPage)}>
            <Webline>
                <div ref={containerWrapRef}>
                    <Heading type="h1">{blogCategory.name}</Heading>
                    <BlogCategoryStyled>
                        <BlogCategoryListStyled>
                            <BlogCategoryArticlesWrapper uuid={blogCategory.uuid} />
                        </BlogCategoryListStyled>
                        <BlogCategoryPanelStyled>
                            <BlogSignpost
                                blogCategoryItems={blogCategory.blogCategoriesTree}
                                activeItem={blogCategory.uuid}
                            />
                        </BlogCategoryPanelStyled>
                    </BlogCategoryStyled>
                </div>
            </Webline>
        </PaginationProvider>
    );
};
