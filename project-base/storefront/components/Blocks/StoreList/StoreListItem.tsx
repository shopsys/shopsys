import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { useEffect, useRef, useState } from 'react';
import { getTodayOpeningHours } from 'utils/openingHours/openingHoursHelper';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { twMergeCustom } from 'utils/twMerge';

type StoreListItemProps = {
    store: StoreOrPacketeryPoint;
    isSelected: boolean;
};

export const StoreListItem: FC<StoreListItemProps> = ({ store, isSelected }) => {
    const [isExpanded, setIsExpanded] = useState(false);
    const { t } = useTranslation();
    const itemRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        setIsExpanded(isSelected);
    }, [isSelected]);

    useEffect(() => {
        if (isExpanded && itemRef.current) {
            itemRef.current.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'center',
            });
        }
    }, [isExpanded]);

    return (
        <div
            ref={itemRef}
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
                    <div className="xl:text-right flex items-center xl:block" tid={TIDs.store_opening_status}>
                        <OpeningStatus className="xl:mb-1.5" status={store.openingHours.status} />
                        <p className="ml-2.5 text-xs">{getTodayOpeningHours(store.openingHours)}</p>
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
