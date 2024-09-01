import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { TypeOpeningHoursFragment } from 'graphql/requests/stores/fragments/OpeningHoursFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { Fragment, useEffect, useState } from 'react';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { twMergeCustom } from 'utils/twMerge';

export const StoreListItem: FC<{ store: StoreOrPacketeryPoint; isSelected: boolean }> = ({ store, isSelected }) => {
    const [isExpanded, setIsExpanded] = useState(false);
    const { t } = useTranslation();

    useEffect(() => {
        setIsExpanded(isSelected);
    }, [isSelected]);

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
            className={twMergeCustom(
                'bg-backgroundMore px-5 py-2.5 rounded-xl cursor-pointer border border-transparent',
                isExpanded && 'border-borderAccent',
            )}
            onClick={() => {
                setIsExpanded((isExpanded) => !isExpanded);
            }}
        >
            <div className="flex items-center justify-between gap-3.5">
                <div className="w-full xl:flex items-center justify-between">
                    <div className="max-xl:mb-2.5">
                        <h5>{store.name}</h5>
                        <p className="mt-1.5 text-xs">
                            {store.street}, {store.postcode} {store.city}
                        </p>
                    </div>
                    <div className="xl:text-right flex items-center xl:block">
                        <OpeningStatus className="xl:mb-1.5" isOpen={store.openingHours.isOpen} />
                        <p className="ml-1.5 text-xs">{getCurrentOpeningHours(store.openingHours)}</p>
                    </div>
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
