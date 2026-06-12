import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { Infobox } from 'components/Basic/Infobox/Infobox';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import OpeningHoursToday from 'components/Blocks/OpeningHours/OpeningHoursToday';
import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';
import { Button } from 'components/Forms/Button/Button';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { TIDs } from 'cypress/tids';
import { AnimatePresence } from 'framer-motion';
import { useEffect, useRef, useState } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { twMergeCustom } from 'utils/twMerge';
import { StoreContact } from './StoreContact';

type StoreListItemProps = {
    store: StoreOrPacketeryPoint;
    isSelected: boolean;
    isDistanceFromSearchText: boolean;
    onSelectStoreCallback?: (storeUuid: string | null) => void;
};

export const StoreListItem: FC<StoreListItemProps> = ({
    store,
    isSelected,
    isDistanceFromSearchText,
    onSelectStoreCallback,
}) => {
    const [isExpanded, setIsExpanded] = useState(isSelected);
    const { t } = useTranslation();
    const itemRef = useRef<HTMLDivElement>(null);
    const storeInfoId = `store-info-${store.slug.replace(/\//g, '-')}`;
    const [prevIsSelected, setPrevIsSelected] = useState(isSelected);

    if (isSelected !== prevIsSelected) {
        setPrevIsSelected(isSelected);
        setIsExpanded(isSelected);
    }

    useEffect(() => {
        if (isExpanded && itemRef.current) {
            const timeoutId = setTimeout(() => {
                itemRef.current?.scrollIntoView({
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
            setIsExpanded((currentIsExpanded) => !currentIsExpanded);
        }
    };

    return (
        /* biome-ignore lint/a11y/useSemanticElements: The store card header toggles expandable content while the expanded panel contains nested links, so the wrapper cannot be a semantic button. */
        <div
            aria-controls={storeInfoId}
            aria-expanded={isExpanded}
            data-tid={`${TIDs.store_list_item_}${store.identifier}`}
            ref={itemRef}
            role="button"
            tabIndex={0}
            title={isExpanded ? t('Collapse store info') : t('Expand store info')}
            aria-label={
                isExpanded
                    ? t('Collapse store info {{storeName}}', { ns: 'accessibility', storeName: store.name })
                    : t('Expand store info {{storeName}}', { ns: 'accessibility', storeName: store.name })
            }
            className={twMergeCustom(
                'cursor-pointer rounded-xl border border-transparent bg-background-more px-5 py-2.5 text-left',
                isExpanded && 'border-border-less',
            )}
            onKeyDown={handleKeyDown}
            onClick={() => {
                setIsExpanded((currentIsExpanded) => !currentIsExpanded);
            }}
        >
            <div
                aria-label={t('Store info', { ns: 'accessibility' })}
                className="flex items-center justify-between gap-3.5"
            >
                <div className="w-full items-center justify-between xl:flex">
                    <div className="max-xl:mb-2.5 xl:w-[215px]">
                        <span className="h5">{store.name}</span>

                        <p aria-label={t('Address', { ns: 'accessibility' })} className="mt-1.5 text-xs">
                            {store.street}, {store.postcode} {store.city}
                        </p>
                    </div>

                    {store.distance && (
                        <p className="text-input-placeholder-default text-xs max-xl:hidden">
                            {isDistanceFromSearchText
                                ? t('{{ distance }} km away', {
                                      distance: (store.distance / 1000).toFixed(0),
                                  })
                                : t('{{ distance }} km from you', {
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

                <ArrowIcon aria-hidden="true" className={`size-5 ${isExpanded ? 'rotate-180' : ''}`} />
            </div>

            <div id={storeInfoId}>
                <AnimatePresence initial={false}>
                    {isExpanded && (
                        <AnimateCollapseDiv className="block! mt-2.5" keyName={storeInfoId}>
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
                                <p className="h5 mb-2">{t('Opening hours', { ns: 'accessibility' })}</p>
                                <OpeningHours openingHours={store.openingHours} />
                            </InfoItem>

                            {onSelectStoreCallback && (
                                <Button
                                    className="mr-2.5"
                                    hasDisabledLook={isSelected}
                                    size="small"
                                    tid={TIDs.store_select_button}
                                    variant={isSelected ? 'inverted' : 'primary'}
                                    onClick={(event) => {
                                        event.stopPropagation();
                                        onSelectStoreCallback(store.identifier);
                                    }}
                                    onKeyDown={(event) => event.stopPropagation()}
                                >
                                    {isSelected ? t('Selected store') : t('Select store')}
                                </Button>
                            )}

                            <LinkButton
                                aria-label={t('Store detail for {{storeName}}', {
                                    ns: 'accessibility',
                                    storeName: store.name,
                                })}
                                href={store.slug}
                                size="small"
                                tid={TIDs.store_detail_link}
                                type="store"
                                variant="secondary"
                            >
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
