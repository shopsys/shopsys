import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageTransportAndPayment: FC = () => (
    <Webline>
        <Skeleton
            className="vl:h-11 mx-auto mt-1 mb-5 h-16 w-full max-w-[840px] lg:mt-6 lg:mb-10"
            containerClassName="flex"
        />

        <div className="vl:flex-row flex w-full flex-col flex-wrap">
            <div className="vl:mb-0 vl:flex-1 vl:pr-10 mb-16 w-full">
                <Skeleton className="h-80 w-full" />
                <div className="vl:flex-row mt-8 flex flex-col justify-between gap-3">
                    <Skeleton className="vl:w-40 h-12 w-full" />
                    <Skeleton className="vl:w-52 h-12 w-full" />
                </div>
            </div>
            <div className="vl:max-w-md w-full">
                <Skeleton className="h-40 w-full" />
            </div>
        </div>
    </Webline>
);
