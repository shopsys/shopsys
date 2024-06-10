import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { TypeAdvertsFragment_AdvertImage_ } from 'graphql/requests/adverts/fragments/AdvertsFragment.generated';
import { TypeImage } from 'graphql/types';

type ImageComponentProps = {
    mainImage: TypeImage | null;
    mainImageMobile: TypeImage | null;
    altBackup: string;
};

type AdvertImageProps = {
    advert: TypeAdvertsFragment_AdvertImage_;
};

const ImageComponent = ({ mainImage, mainImageMobile, altBackup }: ImageComponentProps) => {
    return (
        <>
            <Image
                alt={mainImage?.name || altBackup}
                className="hidden lg:block"
                height={400}
                src={mainImage?.url}
                width={1280}
            />
            <Image
                alt={mainImageMobile?.name || altBackup}
                className="lg:hidden"
                height={300}
                src={mainImageMobile?.url}
                width={770}
            />
        </>
    );
};

export const AdvertImage: FC<AdvertImageProps> = ({ advert: { mainImage, mainImageMobile, name, link } }) => {
    if (!link) {
        return <ImageComponent altBackup={name} mainImage={mainImage} mainImageMobile={mainImageMobile} />;
    }

    return (
        <ExtendedNextLink href={link} target="_blank">
            <ImageComponent altBackup={name} mainImage={mainImage} mainImageMobile={mainImageMobile} />
        </ExtendedNextLink>
    );
};
