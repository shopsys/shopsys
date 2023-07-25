import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { Link } from 'components/Basic/Link/Link';
import { ListedStoreFragmentApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';

type InfoBoxProps = {
    store: ListedStoreFragmentApi;
    closeInfoBoxCallback: () => void;
};

export const InfoBox: FC<InfoBoxProps> = ({ store, closeInfoBoxCallback }) => {
    const t = useTypedTranslationFunction();

    return (
        <div className="top-0 left-0 z-above flex h-full w-full flex-col items-center justify-center bg-white py-4 text-center vl:absolute vl:px-10">
            <Icon
                onClick={closeInfoBoxCallback}
                iconType="icon"
                icon="Remove"
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
            <br />
            <Link href={store.slug} isButton className="mt-5">
                {t('Store detail')}
            </Link>
        </div>
    );
};
