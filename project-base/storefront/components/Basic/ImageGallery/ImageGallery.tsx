import lgThumbnail from 'lightgallery/plugins/thumbnail';
import LightGallery from 'lightgallery/react';
import getConfig from 'next/config';

type ImageGalleryProps = {
    selector: string;
};

export const ImageGallery: FC<ImageGalleryProps> = ({ selector, children }) => {
    const {
        publicRuntimeConfig: { lightgalleryLicenceKey },
    } = getConfig();

    return (
        <LightGallery
            mode="lg-fade"
            thumbnail
            plugins={[lgThumbnail]}
            selector={selector}
            licenseKey={lightgalleryLicenceKey}
        >
            {children}
        </LightGallery>
    );
};
