import { StoreContact } from './StoreContact';
import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { Infobox } from 'components/Basic/Infobox/Infobox';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import OpeningHoursToday from 'components/Blocks/OpeningHours/OpeningHoursToday';
import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { TIDs } from 'cypress/tids';
import { AnimatePresence } from 'framer-motion';
import useTranslation from 'next-translate/useTranslation';
import { useEffect, useRef, useState } from 'react';
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
    const storeInfoId = `store-info-${store.slug.replace(/\//g, '-')}`;

    useEffect(() => {
        setIsExpanded(isSelected);
    }, [isSelected]);

    useEffect(() => {
        if (isExpanded && itemRef.current) {
            const timeoutId = setTimeout(() => {
                itemRef.current!.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                    inline: 'end',
                });
            }, 100);
            return () => clearTimeout(timeoutId);
        }

        return undefined;
    }, [isExpanded]);

    const handleKeyDown = (e: React.KeyboardEvent<HTMLDivElement>) => {
        if (e.key === 'Enter' || e.key === ' ') {
            setIsExpanded((isExpanded) => !isExpanded);
        }
    };

    return (
        <div
            aria-controls={storeInfoId}
            aria-expanded={isExpanded}
            ref={itemRef}
            role="button"
            tabIndex={0}
            title={isExpanded ? t('Collapse store info') : t('Expand store info')}
            aria-label={
                isExpanded
                    ? t('Collapse store info {{storeName}}', { storeName: store.name })
                    : t('Expand store info {{storeName}}', { storeName: store.name })
            }
            className={twMergeCustom(
                'bg-background-more cursor-pointer rounded-xl border border-transparent px-5 py-2.5 text-left',
                isExpanded && 'border-border-less',
            )}
            onKeyDown={handleKeyDown}
            onClick={() => {
                setIsExpanded((isExpanded) => !isExpanded);
            }}
        >
            <div aria-label={t('Store info')} className="flex items-center justify-between gap-3.5">
                <div className="w-full items-center justify-between xl:flex">
                    <div className="max-xl:mb-2.5 xl:w-[215px]">
                        <span className="h5">{store.name}</span>

                        <p aria-label={t('Address')} className="mt-1.5 text-xs">
                            {store.street}, {store.postcode} {store.city}
                        </p>
                    </div>

                    {store.distance && (
                        <p className="text-input-placeholder-default text-xs max-xl:hidden">
                            {t('{{ distance }} km from you', {
                                distance: (store.distance / 1000).toFixed(0),
                            })}
                        </p>
                    )}
                    <div
                        className="flex items-center xl:block xl:w-44 xl:text-right"
                        data-tid={TIDs.store_opening_status}
                    >
                        <OpeningStatus className="xl:mb-1.5" status={store.openingHours.status} />

                        <OpeningHoursToday openingHours={store.openingHours} />
                    </div>
                </div>

                <ArrowIcon
                    aria-hidden="true"
                    className={`size-5 motion-safe:transform ${isExpanded ? 'rotate-180' : ''}`}
                />
            </div>

            <div id={storeInfoId}>
                <AnimatePresence initial={false}>
                    {isExpanded && (
                        <AnimateCollapseDiv className="mt-2.5 !block" keyName={storeInfoId}>
                            {!!store.specialMessage && (
                                <InfoItem>
                                    <Infobox message={store.specialMessage} />
                                </InfoItem>
                            )}
                            {store.description && (
                                <InfoItem>
                                    <p className="text-sm" dangerouslySetInnerHTML={{ __html: store.description }} />
                                </InfoItem>
                            )}

                            {store.phone || store.email ? (
                                <InfoItem>
                                    <StoreContact email={store.email} phone={store.phone} />
                                </InfoItem>
                            ) : null}

                            <InfoItem>
                                <p className="h5 mb-2">{t('Opening hours')}</p>
                                <OpeningHours openingHours={store.openingHours} />
                            </InfoItem>

                            <LinkButton href={store.slug} size="small" type="store" variant="secondary">
                                {t('Store detail')}
                            </LinkButton>
                        </AnimateCollapseDiv>
                    )}
                </AnimatePresence>
            </div>
        </div>
    );
};

const InfoItem: FC = ({ children }) => <div className="mb-5">{children}</div>;
