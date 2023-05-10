import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { Link } from 'components/Basic/Link/Link';
import { ListedStoreFragmentApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';

type InfoBoxProps = {
    store: ListedStoreFragmentApi;
    closeInfoBoxCallback: () => void;
};

export const InfoBox: FC<InfoBoxProps> = ({ store, closeInfoBoxCallback }) => {
    const t = useTypedTranslationFunction();

    return (
        <div className="absolute top-0 left-0 z-above hidden h-full w-full bg-white py-4 px-10 text-center md:block">
            <Icon
                onClick={closeInfoBoxCallback}
                iconType="icon"
                icon="Remove"
                className="absolute top-4 right-4 w-5 cursor-pointer text-primary transition-colors hover:text-orangeDarker "
            />
            <Heading type="h2" className="mb-3">
                {store.name}
            </Heading>
            <div>
                {store.street}
                <br />
                {store.postcode} {store.city}
            </div>
            {store.openingHoursHtml !== null && (
                <>
                    <Heading type="h3" className="m-0 mt-3">
                        {t('Opening hours')}
                    </Heading>
                    <div dangerouslySetInnerHTML={{ __html: store.openingHoursHtml }} />
                </>
            )}
            <br />
            <Link href={store.slug} isButton className="mt-5">
                {t('Store detail')}
            </Link>
        </div>
    );
};
