import 'lightgallery/css/lightgallery.css';
import 'lightgallery/css/lg-thumbnail.css';
import 'lightgallery/css/lg-video.css';
import lgThumbnail from 'lightgallery/plugins/thumbnail';
import lgVideo from 'lightgallery/plugins/video';
import getConfig from 'next/config';
import LightGallery from 'lightgallery/react';
import { twMergeCustom } from 'helpers/twMerge';

type GalleryProps = {
    selector: string;
};

export const Gallery: FC<GalleryProps> = ({ selector, children, className }) => {
    const {
        publicRuntimeConfig: { lightgalleryLicenseKey },
    } = getConfig();

    const TwClass = twMergeCustom(
        'relative flex flex-row items-start justify-start overflow-hidden lg:rounded basis-3/5',
        className,
    );

    return (
        <LightGallery
            mode="lg-fade"
            thumbnail
            plugins={[lgThumbnail, lgVideo]}
            selector={selector}
            licenseKey={lightgalleryLicenseKey}
            download={false}
            elementClassNames={TwClass}
        >
            {children}
        </LightGallery>
    );
};
