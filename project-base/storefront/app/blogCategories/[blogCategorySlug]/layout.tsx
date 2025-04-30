import { LastVisitedProducts } from 'app/_components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { BlogLayout } from 'app/_components/Layout/BlogLayout';
import { BlogCategoryContent } from 'app/_components/Page/BlogCategory/BlogCategoryContent';
import { getBlogCategoryDetailQuery } from 'app/_queries/getBlogCategoryDetailQuery';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { notFound } from 'next/navigation';

type Params = Promise<{ blogCategorySlug: string }>;

type BlogCategoryLayoutProps = {
    children: React.ReactNode;
    params: Params;
};

const BlogCategoryLayout = async ({ children, params }: BlogCategoryLayoutProps) => {
    const { blogCategorySlug } = await params;
    const blogCategoryData = await getBlogCategoryDetailQuery(blogCategorySlug);

    if (!blogCategoryData) {
        return notFound();
    }

    return (
        <VerticalStack gap="lg">
            <BlogCategoryContent blogCategory={blogCategoryData}>
                <BlogLayout activeCategoryUuid={blogCategoryData.uuid} blogCategory={blogCategoryData}>
                    {children}
                </BlogLayout>
            </BlogCategoryContent>

            <LastVisitedProducts />
        </VerticalStack>
    );
};

export default BlogCategoryLayout;
