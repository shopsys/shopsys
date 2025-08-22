import { UpsListItem } from './UpsListItem';
import { UpsFifthIcon } from 'components/Basic/Icon/UpsFifthIcon';
import { UpsFirstIcon } from 'components/Basic/Icon/UpsFirstIcon';
import { UpsFourthIcon } from 'components/Basic/Icon/UpsFourthIcon';
import { UpsSecondIcon } from 'components/Basic/Icon/UpsSecondIcon';
import { UpsThirdIcon } from 'components/Basic/Icon/UpsThirdIcon';
import { Webline } from 'components/Layout/Webline/Webline';
import useTranslation from 'next-translate/useTranslation';
import { twMergeCustom } from 'utils/twMerge';

export const UpsList: FC = () => {
    const { t } = useTranslation();

    return (
        <Webline
            className={twMergeCustom([
                'hide-scrollbar grid snap-x snap-mandatory grid-flow-col gap-5 overflow-x-auto overscroll-x-contain',
                'auto-cols-[60%] sm:auto-cols-[37%] md:auto-cols-[26%] lg:auto-cols-[20%]',
                'vl:flex vl:justify-around',
            ])}
        >
            <UpsListItem>
                <UpsFirstIcon className="text-icon-accent size-14" />
                <span className="h6">{t('The most reliable online store in the Czech Republic')}</span>
            </UpsListItem>
            <UpsListItem>
                <UpsSecondIcon className="text-icon-accent size-14" />
                <span className="h6">{t('We will deliver the goods on the day of ordering')}</span>
            </UpsListItem>
            <UpsListItem>
                <UpsThirdIcon className="text-icon-accent size-14" />
                <span className="h6">{t('24/7 customer support')}</span>
            </UpsListItem>
            <UpsListItem>
                <UpsFourthIcon className="text-icon-accent size-14" />
                <span className="h6">{t('We have 98% of all goods in stock')}</span>
            </UpsListItem>
            <UpsListItem>
                <UpsFifthIcon className="text-icon-accent size-14" />
                <span className="h6">{t('Stores and collection points throughout the Czech Republic')}</span>
            </UpsListItem>
        </Webline>
    );
};
