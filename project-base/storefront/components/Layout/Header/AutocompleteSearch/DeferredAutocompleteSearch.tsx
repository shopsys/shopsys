import { SkeletonModuleAutocompleteSearch } from 'components/Blocks/Skeleton/SkeletonModuleAutocompleteSearch';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const AutocompleteSearch = dynamic(
    () => import('./AutocompleteSearch').then((component) => ({
        default: component.AutocompleteSearch
    })),
    {
        ssr: false,
        loading: () => <SkeletonModuleAutocompleteSearch />,
    },
);

export const DeferredAutocompleteSearch: FC = () => {
    const shouldRender = useDeferredRender('autocomplete_search');

    return shouldRender ? <AutocompleteSearch /> : <SkeletonModuleAutocompleteSearch />;
};
