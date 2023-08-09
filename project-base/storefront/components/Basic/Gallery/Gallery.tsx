import 'lightgallery/css/lightgallery.css';
import 'lightgallery/css/lg-thumbnail.css';
import 'lightgallery/css/lg-video.css';
import lgThumbnail from 'lightgallery/plugins/thumbnail';
import lgVideo from 'lightgallery/plugins/video';
import getConfig from 'next/config';
import LightGallery from 'lightgallery/react';

type GalleryProps = {
    selector: string;
};

export const Gallery: FC<GalleryProps> = ({ selector, children }) => {
    const {
        publicRuntimeConfig: { lightgalleryLicenseKey },
    } = getConfig();

    return (
        <LightGallery
            mode="lg-fade"
            thumbnail
            plugins={[lgThumbnail, lgVideo]}
            selector={selector}
            licenseKey={lightgalleryLicenseKey}
            download={false}
        >
            {children}
        </LightGallery>
    );
};
