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

export const Gallery: FC<GalleryProps> = ({ selector, children }) => {
    const {
        publicRuntimeConfig: { lightgalleryLicenceKey },
    } = getConfig();

    return (
        <LightGallery
            mode="lg-fade"
            thumbnail
            plugins={[lgThumbnail, lgVideo]}
            selector={selector}
            licenseKey={lightgalleryLicenceKey}
        >
            {children}
        </LightGallery>
    );
};
