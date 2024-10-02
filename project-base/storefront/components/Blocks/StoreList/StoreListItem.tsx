import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { TIDs } from 'cypress/tids';
import { AnimatePresence } from 'framer-motion';
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
                'cursor-pointer rounded-xl border border-transparent bg-backgroundMore px-5 py-2.5',
                isExpanded && 'border-borderAccent',
            )}
            onClick={() => {
                setIsExpanded((isExpanded) => !isExpanded);
            }}
        >
            <div className="flex items-center justify-between gap-3.5">
                <div className="w-full items-center justify-between xl:flex">
                    <div className="max-xl:mb-2.5">
                        <h5>{store.name}</h5>
                        <p className="mt-1.5 text-xs">
                            {store.street}, {store.postcode} {store.city}
                        </p>
                    </div>
                    <div className="flex items-center xl:block xl:text-right" tid={TIDs.store_opening_status}>
                        <OpeningStatus className="xl:mb-1.5" status={store.openingHours.status} />
                        <p className="ml-2.5 text-xs" tid={TIDs.store_opening_hours}>
                            {getTodayOpeningHours(store.openingHours)}
                        </p>
                    </div>
                </div>
                <div>
                    <ArrowIcon className={`transform ${isExpanded ? 'rotate-180' : ''}`} />
                </div>
            </div>

            <AnimatePresence initial={false}>
                {isExpanded && (
                    <AnimateCollapseDiv className="mt-2.5 !block" keyName="store-info">
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
                    </AnimateCollapseDiv>
                )}
            </AnimatePresence>
        </div>
    );
};

const StoreHeading: FC<{ text: string }> = ({ text }) => <h6 className="mb-2">{text}</h6>;

const InfoItem: FC = ({ children }) => <div className="mb-2">{children}</div>;
