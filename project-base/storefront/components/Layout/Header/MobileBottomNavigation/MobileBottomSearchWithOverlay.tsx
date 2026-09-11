import { AutocompleteSearch } from 'components/Layout/Header/AutocompleteSearch/AutocompleteSearch';
import { type RefObject, useEffect, useState } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type MobileBottomSearchWithOverlayProps = {
    searchInputRef: RefObject<HTMLInputElement | null>;
    onClose: () => void;
};

export const MobileBottomSearchWithOverlay: FC<MobileBottomSearchWithOverlayProps> = ({ searchInputRef, onClose }) => {
    const { t } = useTranslation();
    const [visualViewportOffsetTop, setVisualViewportOffsetTop] = useState(0);

    useEffect(() => {
        const visualViewport = window.visualViewport;

        if (!visualViewport) {
            return undefined;
        }

        const updateVisualViewportOffsetTop = () => setVisualViewportOffsetTop(visualViewport.offsetTop);

        updateVisualViewportOffsetTop();
        visualViewport.addEventListener('resize', updateVisualViewportOffsetTop);
        visualViewport.addEventListener('scroll', updateVisualViewportOffsetTop);

        return () => {
            visualViewport.removeEventListener('resize', updateVisualViewportOffsetTop);
            visualViewport.removeEventListener('scroll', updateVisualViewportOffsetTop);
        };
    }, []);

    return (
        <>
            <button
                aria-label={t('Close search overlay', { ns: 'accessibility' })}
                className="fixed inset-0 z-aboveOverlay cursor-pointer border-0 bg-overlay-default p-0"
                type="button"
                onClick={onClose}
            />

            <div
                className="pointer-events-none fixed inset-x-0 top-5 z-aboveOverlay flex justify-center px-5"
                style={{ transform: `translateY(${visualViewportOffsetTop}px)` }}
            >
                <div className="pointer-events-auto w-full max-w-xl">
                    <AutocompleteSearch
                        inputRef={searchInputRef}
                        inputClassName="h-14 rounded-xl border-2 pl-12 text-base shadow-[0_12px_32px_rgba(0,0,0,0.24)]"
                        popupClassName="left-1/2 w-[calc(100vw-40px)] max-w-214 -translate-x-1/2"
                        shouldOpenPopupOnMount
                        shouldRenderResultsOverlay={false}
                        onClearEmpty={onClose}
                        onSearchSubmit={onClose}
                    />
                </div>
            </div>
        </>
    );
};
