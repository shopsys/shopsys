import { TypeOpeningHoursFragment } from 'graphql/requests/stores/fragments/OpeningHoursFragment.generated';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';
import useTranslation from 'next-translate/useTranslation';
import { Fragment, useState } from 'react';

export const StoreListItem: FC<{ store: StoreOrPacketeryPoint }> = ({ store }) => {
    const [isExpanded, setIsExpanded] = useState(false);
    const { t } = useTranslation();

    const getCurrentOpeningHours = (openingHours: TypeOpeningHoursFragment) => {
        const todayOpeningDayRanges = openingHours.openingHoursOfDays[0].openingHoursRanges;

        if (todayOpeningDayRanges.length === 0) {
            return null;
        }

        return (
            <>
                {todayOpeningDayRanges.map(({ openingTime, closingTime }, index) => (
                    <Fragment key={index}>
                        {index > 0 && ','} {openingTime}&nbsp;&#8209;&nbsp;{closingTime}
                    </Fragment>
                ))}
            </>
        );
    };

    return (
        <div
            className="bg-backgroundMore px-5 py-2.5 rounded-xl cursor-pointer"
            onClick={() => {
                setIsExpanded((isExpanded) => !isExpanded);
            }}
        >
            <div className="flex items-center justify-between gap-2.5">
                <div className="w-full">
                    <div className="max-vl:mb-2.5">
                        <h5>{store.name}</h5>
                        <p className="mt-1.5">
                            {store.street}, {store.postcode} {store.city}
                        </p>
                    </div>
                    {!isExpanded && (
                        <div className="flex items-center mt-1.5">
                            <OpeningStatus isOpen={store.openingHours.isOpen} />
                            <span className="ml-2.5">{getCurrentOpeningHours(store.openingHours)}</span>
                        </div>
                    )}
                </div>
                <div>
                    <ArrowIcon className={`transform ${isExpanded ? 'rotate-180' : ''}`} />
                </div>
            </div>

            {isExpanded && (
                <div className="mt-2.5">
                    {store.description && (
                        <InfoItem>
                            <StoreHeading text={t('Store description')} />
                            <p>{store.description}</p>
                        </InfoItem>
                    )}

                    <InfoItem>
                        <StoreHeading text={t('Opening hours')} />
                        <OpeningHours openingHours={store.openingHours} />
                    </InfoItem>

                    <LinkButton href={store.slug} size="small" type="store" variant="inverted">
                        {t('Store detail')}
                    </LinkButton>
                </div>
            )}
        </div>
    );
};

const StoreHeading: FC<{ text: string }> = ({ text }) => <h6 className="mb-2">{text}</h6>;

const InfoItem: FC = ({ children }) => <div className="mb-2">{children}</div>;
