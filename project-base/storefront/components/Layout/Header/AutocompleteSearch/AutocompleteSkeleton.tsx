import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VISIBLE_SLIDER_ITEMS_AUTOCOMPLETE } from 'components/Blocks/Product/ProductsSlider';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { SearchResultSectionGroup } from './AutocompleteSearchPopup';

export const AutocompleteSkeleton: FC = () => {
    return (
        <div className="flex flex-col gap-5 vl:gap-6">
            <div>
                <Skeleton className="mb-2 h-7 w-24" />

                <div className="flex gap-4 overflow-hidden lg:grid lg:grid-cols-5 lg:gap-5">
                    {createEmptyArray(VISIBLE_SLIDER_ITEMS_AUTOCOMPLETE).map((_, index) => (
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
