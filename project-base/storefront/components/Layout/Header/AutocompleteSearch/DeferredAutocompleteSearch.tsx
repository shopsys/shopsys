import { SkeletonModuleAutocompleteSearch } from 'components/Blocks/Skeleton/SkeletonModuleAutocompleteSearch';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';
import type { AutocompleteSearchProps } from './AutocompleteSearch';

const AutocompleteSearch = dynamic<AutocompleteSearchProps>(
    () => import('./AutocompleteSearch').then((component) => component.AutocompleteSearch),
    {
        ssr: false,
        loading: () => <SkeletonModuleAutocompleteSearch />,
    },
);

export const DeferredAutocompleteSearch: FC<AutocompleteSearchProps> = ({
    inputRef,
    popupClassName,
    shouldFocusOnMount,
    shouldOpenPopupOnMount,
    shouldRenderResultsOverlay,
}) => {
    const shouldRender = useDeferredRender('autocomplete_search');

    return shouldRender ? (
        <AutocompleteSearch
            inputRef={inputRef}
            popupClassName={popupClassName}
            shouldFocusOnMount={shouldFocusOnMount}
            shouldOpenPopupOnMount={shouldOpenPopupOnMount}
            shouldRenderResultsOverlay={shouldRenderResultsOverlay}
        />
    ) : (
        <SkeletonModuleAutocompleteSearch />
    );
};
