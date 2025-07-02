import { FacebookIcon } from 'components/Basic/Icon/FacebookIcon';
import { InstagramIcon } from 'components/Basic/Icon/InstagramIcon';
import { YoutubeIcon } from 'components/Basic/Icon/YoutubeIcon';
import { Image } from 'components/Basic/Image/Image';
import { FooterContainer } from 'components/Layout/Footer/FooterContainer';
import { TIDs } from 'cypress/tids';
import { useTransportsImage } from 'graphql/requests/transports/queries/TransportsImage.generated';
import useTranslation from 'next-translate/useTranslation';

export const FooterExtras = () => {
    const { t } = useTranslation();
    const [{ data: transportsImages }] = useTransportsImage();

    const transportsWithImages = transportsImages?.transports.filter((transport) => transport.mainImage) || [];

    return (
        <FooterContainer>
            <div className="flex flex-col items-center justify-between gap-5 lg:flex-row">
                {transportsWithImages.length > 0 && (
                    <div
                        className="border-border-less flex flex-wrap items-center justify-center gap-4 rounded-lg border-1 px-5 py-4 lg:flex-nowrap"
                        data-tid={TIDs.footer_payment_images}
                    >
                        {transportsWithImages.map((transport, index) => (
                            <div key={transport.mainImage?.name || index} className="flex items-center">
                                <Image
                                    alt={transport.mainImage?.name || 'Transport'}
                                    className="h-8 w-16 object-contain object-center"
                                    height="64"
                                    sizes="(max-width: 768px) 64px, 128px"
                                    src={transport.mainImage?.url}
                                    width="128"
                                />
                            </div>
                        ))}
                    </div>
                )}

                <div className="flex gap-4" data-tid={TIDs.footer_social_links}>
                    <a aria-label={t('Go to Instagram')} href="/" tabIndex={0} target="_blank" title="Instagram">
                        <InstagramIcon className="size-10" />
                    </a>
                    <a aria-label={t('Go to Facebook')} href="/" tabIndex={0} target="_blank" title="Facebook">
                        <FacebookIcon className="size-10" />
                    </a>
                    <a aria-label={t('Go to Youtube')} href="/" tabIndex={0} target="_blank" title="Youtube">
                        <YoutubeIcon className="size-10" />
                    </a>
                </div>
            </div>
        </FooterContainer>
    );
};
