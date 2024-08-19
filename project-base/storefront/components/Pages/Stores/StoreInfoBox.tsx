import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { Link } from 'components/Basic/Link/Link';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';
import useTranslation from 'next-translate/useTranslation';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

type StoreInfoBoxProps = {
    store: StoreOrPacketeryPoint;
    closeInfoBoxCallback: () => void;
};

export const StoreInfoBox: FC<StoreInfoBoxProps> = ({ store, closeInfoBoxCallback }) => {
    const { t } = useTranslation();

    return (
        <div className="top-0 left-0 z-above flex h-full w-full flex-col items-center justify-center bg-background py-4 text-center vl:absolute vl:px-10">
            <RemoveIcon
                className="absolute top-4 right-4 w-5 cursor-pointer text-textAccent"
                onClick={closeInfoBoxCallback}
            />

            <h2 className="mb-3">{store.name}</h2>

            <OpeningStatus className="mb-3" isOpen={store.openingHours.isOpen} />

            <div>
                {store.street}
                <br />
                {store.postcode} {store.city}
            </div>

            <div className="h3 mt-3">{t('Opening hours')}</div>

            <OpeningHours openingHours={store.openingHours} />

            <Link isButton className="mt-5" href={store.slug}>
                {t('Store detail')}
            </Link>
        </div>
    );
};
