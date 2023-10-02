import useTranslation from 'next-translate/useTranslation';
import { Heading } from 'components/Basic/Heading/Heading';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useRouter } from 'next/router';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { useWishlist } from 'hooks/useWishlist';
import { RemoveThinIcon } from 'components/Basic/Icon/IconsSvg';

export const Wishlist: FC = () => {
    const { t } = useTranslation();
    const { wishlist, fetching, handleCleanWishlist } = useWishlist();
    const { url } = useDomainConfig();
    const router = useRouter();
    const windowLocation = typeof window !== 'undefined' ? window.location.href : '';

    const buildShareUrl = (): string => {
        if (!wishlist) {
            return windowLocation;
        }

        return (
            url.replace(/\/$/, '') +
            '/' +
            router.asPath.replace(/^\//, '') +
            '?id=' +
            wishlist.products.map((product) => product.catalogNumber).join()
        );
    };

    if (fetching) {
        return <LoaderWithOverlay />;
    }

    return (
        <>
            <Heading type="h1" className="!text-big lg:!text-h1">
                {t('Wishlist')}
            </Heading>
            {!!wishlist?.products.length && (
                <div className="flex w-full flex-col items-center justify-between border-b border-greyLighter pb-2 lg:flex-row">
                    <div
                        className="mb-2 cursor-pointer items-center rounded bg-greyVeryLight py-2 px-4 transition-colors hover:bg-greyLighter sm:inline-flex lg:mb-0"
                        onClick={() => {
                            handleCleanWishlist();
                        }}
                    >
                        <span className="mr-3 text-sm">{t('Delete all from wishlist')}</span>
                        <RemoveThinIcon className="w-3" />
                    </div>
                    <div className="flex w-full flex-col items-center lg:w-1/2 lg:flex-row">
                        <TextInput
                            id="copyUrl-input"
                            value={buildShareUrl()}
                            label={t('Send a link to this wishlist')}
                            onChange={() => {
                                return false;
                            }}
                        />
                        <SubmitButton
                            onClick={() => {
                                navigator.clipboard.writeText(buildShareUrl());
                            }}
                            className="mt-2 lg:ml-2 lg:mt-0"
                        >
                            {t('Copy')}
                        </SubmitButton>
                    </div>
                </div>
            )}

            {wishlist?.products.length ? (
                <div>
                    <ProductsList
                        products={wishlist.products}
                        gtmProductListName={GtmProductListNameType.wishlist}
                        gtmMessageOrigin={GtmMessageOriginType.other}
                        fetching={fetching}
                    />
                </div>
            ) : (
                <div>
                    <strong>{t('There are no products in the wishlist. Add some first.')}</strong>
                </div>
            )}
        </>
    );
};

export default Wishlist;
