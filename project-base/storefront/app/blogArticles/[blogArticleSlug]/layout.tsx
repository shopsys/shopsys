import { LastVisitedProducts } from 'app/_components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { BlogLayout } from 'app/_components/Layout/BlogLayout';
import { getBlogArticleDetailQuery } from 'app/_queries/getBlogArticleDetailQuery';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { notFound } from 'next/navigation';

type Params = Promise<{ blogArticleSlug: string }>;

type BlogArticleDetailLayoutProps = {
    children: React.ReactNode;
    params: Params;
};

const BlogArticleDetailLayout = async ({ children, params }: BlogArticleDetailLayoutProps) => {
    const { blogArticleSlug } = await params;
    const blogArticleData = await getBlogArticleDetailQuery(blogArticleSlug);

    if (!blogArticleData) {
        return notFound();
    }

    return (
        <VerticalStack gap="md">
            <BlogLayout activeCategoryUuid={blogArticleData.mainBlogCategoryUuid}>
                <h1>{blogArticleData.seoH1 || blogArticleData.name}</h1>
                {children}
            </BlogLayout>
            <LastVisitedProducts />
        </VerticalStack>
    );
};

export default BlogArticleDetailLayout;
