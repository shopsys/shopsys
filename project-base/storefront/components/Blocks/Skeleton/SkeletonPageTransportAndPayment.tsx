import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageTransportAndPayment: FC = () => (
    <Webline>
        <Skeleton className="vl:h-11 mx-auto mt-1 mb-5 flex h-16 w-full max-w-[840px] rounded-xl lg:mt-6 lg:mb-10" />

        <div className="vl:flex-row flex flex-col flex-wrap">
            <div className="vl:flex-1 vl:pr-10">
                <Skeleton className="h-80 rounded-xl" />

                <div className="mt-4 mb-12 flex flex-col flex-wrap items-center lg:mb-24 lg:w-full lg:flex-row lg:justify-between">
                    <Skeleton className="vl:w-40 h-9" />
                    <Skeleton className="vl:w-52 h-9" />
                </div>
            </div>

            <div className="vl:max-w-md w-full">
                <Skeleton className="h-64 rounded-xl" />
            </div>
        </div>
    </Webline>
);
