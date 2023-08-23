import { Heading } from 'components/Basic/Heading/Heading';
import { Link } from 'components/Basic/Link/Link';
import { ListedStoreFragmentApi } from 'graphql/generated';
import useTranslation from 'next-translate/useTranslation';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';
import { Remove } from 'components/Basic/Icon/IconsSvg';

type StoreInfoBoxProps = {
    store: ListedStoreFragmentApi;
    closeInfoBoxCallback: () => void;
};

export const StoreInfoBox: FC<StoreInfoBoxProps> = ({ store, closeInfoBoxCallback }) => {
    const { t } = useTranslation();

    return (
        <div className="top-0 left-0 z-above flex h-full w-full flex-col items-center justify-center bg-white py-4 text-center vl:absolute vl:px-10">
            <Remove
                onClick={closeInfoBoxCallback}
                className="absolute top-4 right-4 w-5 cursor-pointer text-primary transition-colors hover:text-orangeDarker "
            />

            <Heading type="h2" className="">
                {store.name}
            </Heading>

            <OpeningStatus isOpen={store.openingHours.isOpen} className="mb-3" />

            <div>
                {store.street}
                <br />
                {store.postcode} {store.city}
            </div>

            <Heading type="h3" className="m-0 mt-3">
                {t('Opening hours')}
            </Heading>

            <OpeningHours openingHours={store.openingHours} />

            <Link href={store.slug} isButton className="mt-5">
                {t('Store detail')}
            </Link>
        </div>
    );
};
