import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageBlogArticle: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={3} />
        <div className="scroll-mt-5">
            <div className="vl:flex-row mb-16 flex flex-col">
                <div className="vl:order-1 vl:flex-1 order-2 mb-16 flex w-full flex-col">
                    <Skeleton className="mb-3 h-8 w-3/5" />
                    <div className="mb-16 flex flex-col gap-8">
                        <Skeleton className="mb-5 h-12 w-full" />
                        <Skeleton className="mb-5 h-6 w-28" />
                        <Skeleton className="mb-5 h-6 w-full" />
                        <Skeleton className="mb-5 h-96 w-full" />
                    </div>
                </div>
                <div className="vl:order-2 vl:w-5/12 vl:pl-8 order-1 mb-7 flex w-full flex-col xl:w-1/3">
                    <Skeleton className="vl:w-[400px] h-[450px]" containerClassName="order-1 vl:order-2" />
                </div>
            </div>
        </div>
    </Webline>
);
