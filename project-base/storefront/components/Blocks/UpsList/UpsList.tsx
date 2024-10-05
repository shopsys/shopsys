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
        <Webline className="mb-14">
            <div
                className={twMergeCustom([
                    'vl:flex vl:justify-around',
                    'grid snap-x snap-mandatory auto-cols-[60%] grid-flow-col gap-5 overflow-x-auto sm:auto-cols-[37%] md:auto-cols-[26%] lg:auto-cols-[20%] ',
                    "overscroll-x-contain [-ms-overflow-style:'none'] [scrollbar-width:'none'] [&::-webkit-scrollbar]:hidden",
                ])}
            >
                <UpsListItem>
                    <UpsFirstIcon className="size-14 text-textAccent" />
                    <h6>{t('The most reliable online store in the Czech Republic')}</h6>
                </UpsListItem>
                <UpsListItem>
                    <UpsSecondIcon className="size-14 text-textAccent" />
                    <h6>{t('We will deliver the goods on the day of ordering')}</h6>
                </UpsListItem>
                <UpsListItem>
                    <UpsThirdIcon className="size-14 text-textAccent" />
                    <h6>{t('24/7 customer support')}</h6>
                </UpsListItem>
                <UpsListItem>
                    <UpsFourthIcon className="size-14 text-textAccent" />
                    <h6>{t('We have 98% of all goods in stock')}</h6>
                </UpsListItem>
                <UpsListItem>
                    <UpsFifthIcon className="size-14 text-textAccent" />
                    <h6>{t('Stores and collection points throughout the Czech Republic')}</h6>
                </UpsListItem>
            </div>
        </Webline>
    );
};
