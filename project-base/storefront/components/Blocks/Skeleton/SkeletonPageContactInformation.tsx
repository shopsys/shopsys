import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageContactInformation: FC = () => (
    <Webline>
        <Skeleton className="mx-auto mb-5 mt-1 h-11 w-full max-w-[840px] lg:mb-10 lg:mt-6" containerClassName="flex" />

        <div className="mb-24 flex w-full flex-col flex-wrap vl:mb-16 vl:flex-row">
            <div className="mb-16 w-full vl:mb-0 vl:min-h-[61vh] vl:flex-1 vl:pr-10">
                <Skeleton className="mb-3 h-14 w-full lg:w-[65%]" />
                <Skeleton className="h-20 w-full lg:h-16" />
                <div className="mt-8 flex flex-col justify-between gap-3 vl:flex-row">
                    <Skeleton className="h-12 w-full vl:w-40" />
                    <Skeleton className="h-12 w-full vl:w-52" />
                </div>
            </div>
            <div className="w-full vl:max-w-md">
                <Skeleton className="h-40 w-full" />
            </div>
        </div>
    </Webline>
);
