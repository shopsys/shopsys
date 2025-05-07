import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageContactInformation: FC = () => (
    <Webline>
        <Skeleton className="vl:h-11 mx-auto mt-1 mb-5 flex h-16 w-full max-w-[840px] rounded-xl lg:mt-6 lg:mb-10" />

        <div className="vl:flex-row flex flex-col flex-wrap">
            <div className="vl:flex-1 vl:pr-10">
                <Skeleton className="h-96 rounded-xl" />

                <div className="mt-4 flex flex-col gap-1">
                    <Skeleton className="h-6" />
                    <Skeleton className="h-6 w-2/3" />
                </div>

                <Skeleton className="mt-4 h-6 w-1/3" />

                <div className="mt-5 flex flex-col-reverse items-center justify-between gap-4 md:mt-10 md:flex-row">
                    <Skeleton className="vl:w-40 h-10" />
                    <Skeleton className="vl:w-52 h-14" />
                </div>
            </div>

            <div className="vl:max-w-md w-full">
                <Skeleton className="h-64 rounded-xl" />
            </div>
        </div>
    </Webline>
);
