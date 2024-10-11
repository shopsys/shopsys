import { SearchResultSectionGroup } from './AutocompleteSearchPopup';
import Skeleton from 'react-loading-skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const AutocompleteSkeleton: FC = () => {
    return (
        <div className="flex flex-col gap-5 vl:gap-6">
            <div>
                <Skeleton className="mb-2 h-5 w-[100px]" />
                <div className="flex gap-4 overflow-hidden lg:grid lg:grid-cols-5 lg:gap-5">
                    {createEmptyArray(4).map((_, index) => (
                        <Skeleton key={index} className="h-full min-h-[250px] min-w-[138px]" />
                    ))}
                </div>
            </div>
            <div>
                <Skeleton className="mb-2 h-5 w-[80px]" />
                <SearchResultSectionGroup>
                    {createEmptyArray(3).map((_, index) => (
                        <Skeleton key={index} className="h-[32px] w-[70px] !rounded-full" />
                    ))}
                </SearchResultSectionGroup>
            </div>

            <div className="flex justify-center">
                <Skeleton className="h-[52px] w-full vl:w-[186px]" containerClassName="w-full vl:w-fit" />
            </div>
        </div>
    );
};
