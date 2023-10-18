import { Heading } from 'components/Basic/Heading/Heading';
import { RemoveIcon } from 'components/Basic/Icon/IconsSvg';
import { Link } from 'components/Basic/Link/Link';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';
import { ListedStoreFragmentApi } from 'graphql/generated';
import useTranslation from 'next-translate/useTranslation';

type StoreInfoBoxProps = {
    store: ListedStoreFragmentApi;
    closeInfoBoxCallback: () => void;
};

export const StoreInfoBox: FC<StoreInfoBoxProps> = ({ store, closeInfoBoxCallback }) => {
    const { t } = useTranslation();

    return (
        <div className="top-0 left-0 z-above flex h-full w-full flex-col items-center justify-center bg-white py-4 text-center vl:absolute vl:px-10">
            <RemoveIcon
                className="absolute top-4 right-4 w-5 cursor-pointer text-primary transition-colors hover:text-orangeDarker "
                onClick={closeInfoBoxCallback}
            />

            <Heading className="" type="h2">
                {store.name}
            </Heading>

            <OpeningStatus className="mb-3" isOpen={store.openingHours.isOpen} />

            <div>
                {store.street}
                <br />
                {store.postcode} {store.city}
            </div>

            <Heading className="m-0 mt-3" type="h3">
                {t('Opening hours')}
            </Heading>

            <OpeningHours openingHours={store.openingHours} />

            <Link isButton className="mt-5" href={store.slug}>
                {t('Store detail')}
            </Link>
        </div>
    );
};
