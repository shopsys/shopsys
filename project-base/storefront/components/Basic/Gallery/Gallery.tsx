import { twMergeCustom } from 'helpers/twMerge';
import 'lightgallery/css/lg-thumbnail.css';
import 'lightgallery/css/lg-video.css';
import 'lightgallery/css/lightgallery.css';
import lgThumbnail from 'lightgallery/plugins/thumbnail';
import lgVideo from 'lightgallery/plugins/video';
import LightGallery from 'lightgallery/react';
import getConfig from 'next/config';

type GalleryProps = {
    selector: string;
};

export const Gallery: FC<GalleryProps> = ({ selector, children, className }) => {
    const {
        publicRuntimeConfig: { lightgalleryLicenseKey },
    } = getConfig();

    const TwClass = twMergeCustom('relative flex flex-row items-start justify-start', className);

    return (
        <LightGallery
            thumbnail
            download={false}
            elementClassNames={TwClass}
            licenseKey={lightgalleryLicenseKey}
            mode="lg-fade"
            plugins={[lgThumbnail, lgVideo]}
            selector={selector}
        >
            {children}
        </LightGallery>
    );
};
