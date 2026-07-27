import { Image } from 'components/Basic/Image/Image';
import type { ComponentProps } from 'react';
import { useEffect, useState } from 'react';

type YouTubeThumbnailProps = Omit<ComponentProps<typeof Image>, 'onError' | 'src'> & {
    videoId: string;
};

export const getYouTubeThumbnailUrl = (videoId: string, isHighResolution = true) =>
    `https://img.youtube.com/vi/${videoId}/${isHighResolution ? 'maxresdefault' : 'hqdefault'}.jpg`;

export const YouTubeThumbnail: FC<YouTubeThumbnailProps> = ({ videoId, ...imageProps }) => {
    const [shouldUseFallback, setShouldUseFallback] = useState(false);

    useEffect(() => {
        setShouldUseFallback(false);
    }, [videoId]);

    return (
        <Image
            {...imageProps}
            src={getYouTubeThumbnailUrl(videoId, !shouldUseFallback)}
            onError={shouldUseFallback ? undefined : () => setShouldUseFallback(true)}
        />
    );
};
