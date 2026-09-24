import { Image } from 'components/Basic/Image/Image';
import type { ComponentProps } from 'react';
import { useEffect, useState } from 'react';
import { getYouTubeThumbnailUrl } from 'utils/youtube/getYouTubeThumbnailUrl';

type YouTubeThumbnailProps = Omit<ComponentProps<typeof Image>, 'onError' | 'src'> & {
    videoId: string;
};

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
