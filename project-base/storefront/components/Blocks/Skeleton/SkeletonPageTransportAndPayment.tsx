import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageTransportAndPayment: FC = () => (
    <Webline>
        <div className="w-full mb-6 border-b border-graySlate p-0 lg:mb-3">
            <Skeleton className="w-full h-8" />
        </div>

        <div className="mb-24 flex w-full flex-col flex-wrap vl:mt-7 vl:mb-16 vl:flex-row">
            <div className="mb-16 w-full vl:mb-0 vl:min-h-[61vh] vl:flex-1 vl:pr-10">
                <Skeleton className="h-80 w-full" />
                <div className="flex flex-col vl:flex-row gap-3 justify-between mt-8">
                    <Skeleton className="w-full vl:w-40 h-12" />
                    <Skeleton className="w-full vl:w-52 h-12" />
                </div>
            </div>
            <div className="w-full vl:max-w-md">
                <Skeleton className="w-full h-40" />
            </div>
        </div>
    </Webline>
);
