import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import { TIDs } from 'cypress/tids';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const ContactInformationDeliveryPickUpAddress: FC = () => {
    const { t } = useTranslation();
    const { pickupPlace } = useCurrentCart();

    if (!pickupPlace) {
        return null;
    }

    return (
        <div className="flex flex-col gap-5 md:flex-row">
            <div className="flex flex-col gap-2.5">
                <p className="font-secondary font-semibold">{t('Address')}</p>

                <p className="text-sm">
                    {pickupPlace.street}
                    <br />
                    {pickupPlace.postcode}
                    <br />
                    {pickupPlace.city}
                    <br />
                    {pickupPlace.country.name}
                </p>
            </div>

            <div className="flex flex-1 flex-col gap-2.5" data-tid={TIDs.opening_hours_in_contact_information}>
                <p className="font-secondary font-semibold">{t('Opening hours')}</p>

                <OpeningHours className="text-sm" openingHours={pickupPlace.openingHours} />
            </div>
        </div>
    );
};
