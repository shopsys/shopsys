import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageTransportAndPayment: FC = () => (
    <Webline>
        <Skeleton className="vl:h-11 mx-auto mt-1 mb-5 flex h-16 w-full max-w-[840px] rounded-xl lg:mt-6 lg:mb-10" />

        <div className="vl:grid-cols-3 vl:gap-10 grid">
            <div className="vl:col-span-2">
                <Skeleton className="h-80 rounded-xl" />

                <div className="mt-5 flex flex-col-reverse items-center justify-between gap-4 md:mt-10 md:flex-row">
                    <Skeleton className="vl:w-40 h-10" />
                    <Skeleton className="vl:w-52 h-14" />
                </div>
            </div>

            <div className="vl:col-span-1">
                <Skeleton className="h-96 rounded-xl" />
            </div>
        </div>
    </Webline>
);
