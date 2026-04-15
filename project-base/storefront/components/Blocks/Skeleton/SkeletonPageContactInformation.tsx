import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageContactInformation: FC = () => (
    <Webline>
        <Skeleton className="mx-auto mt-1 mb-5 flex h-16 vl:h-11 w-full max-w-[840px] rounded-xl lg:mt-6 lg:mb-10" />

        <div className="grid vl:grid-cols-3 vl:gap-10">
            <div className="vl:col-span-2 flex flex-col gap-5">
                <Skeleton className="h-80 rounded-xl" />

                <Skeleton className="h-[640px] rounded-xl" />

                <Skeleton className="h-20 rounded-xl" />

                <Skeleton className="h-32 rounded-xl" />

                <Skeleton className="mx-5 vl:mx-20 h-6 w-1/3" />

                <div className="mx-5 vl:mx-20 flex flex-col gap-1">
                    <Skeleton className="h-6" />
                    <Skeleton className="h-6 w-2/3" />
                </div>

                <div className="flex flex-col-reverse items-center justify-between gap-4 md:flex-row">
                    <Skeleton className="h-10 vl:w-40" />
                    <Skeleton className="h-14 vl:w-52" />
                </div>
            </div>

            <div className="vl:col-span-1">
                <Skeleton className="h-64 rounded-xl" />
            </div>
        </div>
    </Webline>
);
