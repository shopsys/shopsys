import { SearchResultSectionGroup } from './AutocompleteSearchPopup';
import Skeleton from 'react-loading-skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const AutocompleteSkeleton: FC = () => {
    return (
        <div className="flex flex-col gap-5 vl:gap-6">
            <div>
                <Skeleton className="w-[100px] h-5 mb-2" />
                <div className="flex gap-4 overflow-hidden lg:grid lg:grid-cols-5 lg:gap-5">
                    {createEmptyArray(4).map((_, index) => (
                        <Skeleton key={index} className="min-w-[138px] h-full min-h-[218px]" />
                    ))}
                </div>
            </div>
            <div>
                <Skeleton className="w-[80px] h-5 mb-2" />
                <SearchResultSectionGroup>
                    {createEmptyArray(3).map((_, index) => (
                        <Skeleton key={index} className="h-[32px] w-[70px] !rounded-full" />
                    ))}
                </SearchResultSectionGroup>
            </div>

            <div className="flex justify-center">
                <Skeleton className="w-full vl:w-[186px] h-[52px]" containerClassName="w-full vl:w-fit" />
            </div>
        </div>
    );
};
