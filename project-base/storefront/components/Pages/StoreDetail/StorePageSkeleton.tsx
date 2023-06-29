import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const StorePageSkeleton: FC = () => (
    <Webline>
        <div className="flex flex-row items-stretch gap-16">
            <Skeleton className="hidden h-[600px] w-[600px] vl:block" />

            <div className="w-full">
                <div className="mb-12 flex w-full flex-col gap-4 ">
                    <Skeleton className="h-9 w-1/2" />
                    <Skeleton count={3} className="mb-3 h-4" />
                </div>

                <div className="flex">
                    <div className="flex w-full flex-col">
                        <div className="mb-7">
                            <Skeleton className="mb-4 h-6 w-40" />
                            <Skeleton count={5} className="mb-2 h-6 w-40 rounded-lg" />
                        </div>
                        <Skeleton className="h-12 w-full" />
                    </div>
                </div>
            </div>
        </div>

        <Skeleton count={4} className="h-48" containerClassName="mt-10 flex justify-between gap-2" />
    </Webline>
);
