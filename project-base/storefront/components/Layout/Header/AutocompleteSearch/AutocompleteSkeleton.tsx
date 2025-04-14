import { SearchResultSectionGroup } from './AutocompleteSearchPopup';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const AutocompleteSkeleton: FC = () => {
    return (
        <div className="vl:gap-6 flex flex-col gap-5">
            <div>
                <Skeleton className="mb-2 h-7 w-24" />

                <div className="flex gap-4 overflow-hidden lg:grid lg:grid-cols-5 lg:gap-5">
                    {createEmptyArray(4).map((_, index) => (
                        <Skeleton key={index} className="h-full min-h-[250px] min-w-[138px]" />
                    ))}
                </div>
            </div>

            <div>
                <Skeleton className="mb-2 h-7 w-32" />

                <SearchResultSectionGroup>
                    {createEmptyArray(3).map((_, index) => (
                        <Skeleton key={index} className="h-7 w-20 rounded-full" />
                    ))}
                </SearchResultSectionGroup>
            </div>

            <div>
                <Skeleton className="mb-2 h-7 w-32" />

                <SearchResultSectionGroup>
                    {createEmptyArray(5).map((_, index) => (
                        <Skeleton key={index} className="h-7 w-20 rounded-full" />
                    ))}
                </SearchResultSectionGroup>
            </div>

            <Skeleton className="mx-auto h-9 w-32" />
        </div>
    );
};
