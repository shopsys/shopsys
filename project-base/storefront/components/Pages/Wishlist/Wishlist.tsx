import { RemoveThinIcon } from 'components/Basic/Icon/IconsSvg';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { isClient } from 'helpers/isClient';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useWishlist } from 'hooks/useWishlist';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';

export const Wishlist: FC = () => {
    const { t } = useTranslation();
    const { wishlist, fetching, handleCleanWishlist } = useWishlist();
    const { url } = useDomainConfig();
    const router = useRouter();

    const buildShareUrl = (): string => {
        if (!wishlist) {
            return isClient ? window.location.href : '';
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
            <h1 className="mb-3">{t('Wishlist')}</h1>

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
                            label={t('Send a link to this wishlist')}
                            value={buildShareUrl()}
                            onChange={() => {
                                return false;
                            }}
                        />
                        <SubmitButton
                            className="mt-2 lg:ml-2 lg:mt-0"
                            onClick={() => {
                                navigator.clipboard.writeText(buildShareUrl());
                            }}
                        >
                            {t('Copy')}
                        </SubmitButton>
                    </div>
                </div>
            )}

            {wishlist?.products.length ? (
                <div>
                    <ProductsList
                        fetching={fetching}
                        gtmMessageOrigin={GtmMessageOriginType.other}
                        gtmProductListName={GtmProductListNameType.wishlist}
                        products={wishlist.products}
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
