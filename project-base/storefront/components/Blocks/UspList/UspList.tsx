import { UspFifthIcon } from 'components/Basic/Icon/UspFifthIcon';
import { UspFirstIcon } from 'components/Basic/Icon/UspFirstIcon';
import { UspFourthIcon } from 'components/Basic/Icon/UspFourthIcon';
import { UspSecondIcon } from 'components/Basic/Icon/UspSecondIcon';
import { UspThirdIcon } from 'components/Basic/Icon/UspThirdIcon';
import { Webline } from 'components/Layout/Webline/Webline';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { UspListItem } from './UspListItem';

export const UspList: FC = () => {
    const { t } = useTranslation();

    return (
        <Webline
            className={twMergeCustom([
                'hide-scrollbar grid snap-x snap-mandatory grid-flow-col gap-5 overflow-x-auto overscroll-x-contain',
                'auto-cols-[60%] sm:auto-cols-[37%] md:auto-cols-[26%] lg:auto-cols-[20%]',
                'vl:flex vl:justify-around',
            ])}
        >
            <UspListItem>
                <UspFirstIcon className="size-14 text-icon-accent" />
                <div className="flex flex-col items-center gap-0.5">
                    <span className="font-secondary font-semibold text-sm leading-5">
                        {t('Trusted Czech retailer')}
                    </span>
                    <span className="font-secondary text-text-less text-xs leading-4">
                        {t('Reliable shopping you can count on')}
                    </span>
                </div>
            </UspListItem>
            <UspListItem>
                <UspSecondIcon className="size-14 text-icon-accent" />
                <div className="flex flex-col items-center gap-0.5">
                    <span className="font-secondary font-semibold text-sm leading-5">{t('Fast delivery')}</span>
                    <span className="font-secondary text-text-less text-xs leading-4">
                        {t('In-stock orders dispatched today')}
                    </span>
                </div>
            </UspListItem>
            <UspListItem>
                <UspThirdIcon className="size-14 text-icon-accent" />
                <div className="flex flex-col items-center gap-0.5">
                    <span className="font-secondary font-semibold text-sm leading-5">{t('Customer support')}</span>
                    <span className="font-secondary text-text-less text-xs leading-4">
                        {t('Here when you need us')}
                    </span>
                </div>
            </UspListItem>
            <UspListItem>
                <UspFourthIcon className="size-14 text-icon-accent" />
                <div className="flex flex-col items-center gap-0.5">
                    <span className="font-secondary font-semibold text-sm leading-5">{t('98% in stock')}</span>
                    <span className="font-secondary text-text-less text-xs leading-4">
                        {t('Thousands of products ready to ship')}
                    </span>
                </div>
            </UspListItem>
            <UspListItem>
                <UspFifthIcon className="size-14 text-icon-accent" />
                <div className="flex flex-col items-center gap-0.5">
                    <span className="font-secondary font-semibold text-sm leading-5">{t('Pickup across Czechia')}</span>
                    <span className="font-secondary text-text-less text-xs leading-4">
                        {t('Stores and collection points nationwide')}
                    </span>
                </div>
            </UspListItem>
        </Webline>
    );
};
