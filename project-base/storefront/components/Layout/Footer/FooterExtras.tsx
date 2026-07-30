import { FacebookIcon } from 'components/Basic/Icon/FacebookIcon';
import { InstagramIcon } from 'components/Basic/Icon/InstagramIcon';
import { YoutubeIcon } from 'components/Basic/Icon/YoutubeIcon';
import { Image } from 'components/Basic/Image/Image';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Tooltip } from 'components/Basic/Tooltip/Tooltip';
import { FooterContainer } from 'components/Layout/Footer/FooterContainer';
import { TIDs } from 'cypress/tids';
import { useTransportsImage } from 'graphql/requests/transports/queries/TransportsImage.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const FooterExtras = () => {
    const { t } = useTranslation();
    const [{ data: transportsImages, fetching: areTransportsImagesFetching }] = useTransportsImage();

    const transportsWithImages = transportsImages?.transports.filter((transport) => transport.mainImage) || [];
    const shouldShowTransportsImagesSkeleton = areTransportsImagesFetching && transportsWithImages.length === 0;

    return (
        <FooterContainer>
            <div className="flex flex-col items-center justify-between gap-5 lg:flex-row">
                {shouldShowTransportsImagesSkeleton ? (
                    <Skeleton className="h-16 w-64 rounded-lg" />
                ) : (
                    transportsWithImages.length > 0 && (
                        <div
                            className="flex flex-wrap items-center justify-center gap-4 lg:flex-nowrap"
                            data-tid={TIDs.footer_payment_images}
                        >
                            {transportsWithImages.map((transport, index) => (
                                <Tooltip key={transport.mainImage?.name || index} label={transport.name}>
                                    <div className="flex items-center">
                                        <Image
                                            alt={transport.name}
                                            className="h-8 w-16 object-contain object-center"
                                            height={32}
                                            src={transport.mainImage?.url}
                                            width={64}
                                        />
                                    </div>
                                </Tooltip>
                            ))}
                        </div>
                    )
                )}

                <div className="flex gap-4 lg:ml-auto" data-tid={TIDs.footer_social_links}>
                    <Tooltip label="Instagram">
                        <a
                            aria-label={t('Go to Instagram', { ns: 'accessibility' })}
                            href="https://example.com/demo-shop/instagram"
                            rel="noopener"
                            tabIndex={0}
                            target="_blank"
                        >
                            <InstagramIcon className="size-10" />
                        </a>
                    </Tooltip>
                    <Tooltip label="Facebook">
                        <a
                            aria-label={t('Go to Facebook', { ns: 'accessibility' })}
                            href="https://example.com/demo-shop/facebook"
                            rel="noopener"
                            tabIndex={0}
                            target="_blank"
                        >
                            <FacebookIcon className="size-10" />
                        </a>
                    </Tooltip>
                    <Tooltip label="Youtube">
                        <a
                            aria-label={t('Go to Youtube', { ns: 'accessibility' })}
                            href="https://example.com/demo-shop/youtube"
                            rel="noopener"
                            tabIndex={0}
                            target="_blank"
                        >
                            <YoutubeIcon className="size-10" />
                        </a>
                    </Tooltip>
                </div>
            </div>
        </FooterContainer>
    );
};
