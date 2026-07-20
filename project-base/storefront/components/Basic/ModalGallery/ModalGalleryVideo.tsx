import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { YouTubeThumbnail } from 'components/Basic/YouTubeThumbnail/YouTubeThumbnail';
import { useEffect, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ModalGalleryVideoProps = {
    description: string | null;
    isSelected: boolean;
    isTrackScrolling: boolean;
    videoId: string;
};

export const ModalGalleryVideo: FC<ModalGalleryVideoProps> = ({
    description,
    isSelected,
    isTrackScrolling,
    videoId,
}) => {
    const { t } = useTranslation();
    const [isIframeLoaded, setIsIframeLoaded] = useState(false);
    const isIframeVisible = isSelected && isIframeLoaded && !isTrackScrolling;

    useEffect(() => {
        if (!isSelected) {
            setIsIframeLoaded(false);
        }
    }, [isSelected]);

    return (
        <>
            {isSelected && (
                /* biome-ignore lint/a11y/noNoninteractiveElementInteractions: The iframe load event is the readiness signal that keeps the thumbnail visible until the video can be displayed. */
                <iframe
                    allowFullScreen
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    aria-label={description ?? t('Product Video', { ns: 'accessibility' })}
                    className={twJoin(
                        'aspect-video max-h-full w-full max-w-xl transition-opacity duration-200 md:max-w-375',
                        isIframeVisible ? 'opacity-100' : 'pointer-events-none opacity-0',
                    )}
                    src={`https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1`}
                    tabIndex={isIframeVisible ? 0 : -1}
                    title={description ?? t('Product Video')}
                    onLoad={() => setIsIframeLoaded(true)}
                />
            )}

            <YouTubeThumbnail
                fill
                aria-hidden="true"
                alt=""
                className={twJoin(
                    'pointer-events-none max-h-full object-contain mix-blend-multiply transition-opacity duration-200',
                    isIframeVisible ? 'opacity-0' : 'opacity-100',
                )}
                draggable={false}
                sizes="100vw"
                videoId={videoId}
            />

            {isSelected && !isIframeLoaded && (
                <span
                    aria-hidden="true"
                    className="pointer-events-none absolute z-above flex size-full items-center justify-center"
                >
                    <SpinnerIcon className="size-8 text-text-inverted" />
                </span>
            )}
        </>
    );
};
