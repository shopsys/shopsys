import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Icon } from 'components/Basic/Icon/Icon';
import { Button } from 'components/Forms/Button/Button';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';

// TODO PRG
const dummyData = {
    phone: '+420 111 222 333',
    opening: 'Po - Út, 10 - 16 hod',
};

const TEST_IDENTIFIER = 'layout-footer-footerboxinfo';

export const FooterBoxInfo: FC = () => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const [contactUrl] = getInternationalizedStaticUrls(['/contact'], url);

    return (
        <div className="relative mb-11 flex items-center lg:mb-24" data-testid={TEST_IDENTIFIER}>
            <picture>
                <source srcSet="/images/need_advice2x.png 2x, /images/need_advice2x.png 1x" />
                <img
                    className="absolute left-0 bottom-0 block h-12 w-12 translate-y-1/2 lg:h-16 lg:w-16"
                    src="/images/need_advice.png"
                    alt={t('Need advice?')}
                />
            </picture>
            <div className="relative ml-16 flex flex-1 flex-col items-start rounded-xl bg-primary p-4 before:absolute before:-left-1 before:-bottom-1 before:h-6 before:w-4 before:rounded-bl-md before:bg-primary before:content-[''] before:[transform:rotate(0deg)skewX(-41deg)scale(1.414,0.707)] lg:ml-24 lg:flex-row lg:items-center lg:justify-between lg:py-5 lg:pr-5 lg:pl-8">
                <div className="mb-3 text-lg font-bold text-white lg:mr-3 lg:mb-0 lg:flex-1 lg:text-2xl vl:flex-none">
                    {t('Need advice?')}
                </div>
                <div className="mb-6 flex flex-wrap items-center lg:mb-0 lg:mr-2 lg:flex-1 vl:justify-center">
                    <Icon iconType="icon" icon="Phone" className="mr-3 w-5 text-orange" />
                    <a
                        className="mr-4 font-bold text-white no-underline hover:text-white lg:text-lg"
                        href={'tel:' + dummyData.phone}
                    >
                        {dummyData.phone}
                    </a>
                    <p className="m-0 text-sm text-white">{dummyData.opening}</p>
                </div>
                <ExtendedNextLink href={contactUrl} type="static">
                    <Button variant="secondary" className="z-above">
                        {t('Write to us')}
                    </Button>
                </ExtendedNextLink>
            </div>
        </div>
    );
};
