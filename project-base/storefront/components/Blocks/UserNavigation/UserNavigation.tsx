import { UserMenu } from 'components/Blocks/UserMenu/UserMenu';
import { Button } from 'components/Forms/Button/Button';
import { m } from 'framer-motion';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { collapseExpandAnimation } from 'utils/animations/animationVariants';

export const UserNavigation: FC = () => {
    const { t } = useTranslation();
    const [isExpanded, setIsExpanded] = useState(false);

    return (
        <aside>
            <Button
                className="w-full lg:hidden"
                variant={isExpanded ? 'secondary' : 'inverted'}
                onClick={() => setIsExpanded((prev) => !prev)}
            >
                {t('My menu')}
            </Button>

            <m.div
                key="user-navigation"
                animate={isExpanded ? 'open' : 'closed'}
                className="!flex min-w-[275px] flex-col lg:!h-fit"
                initial={false}
                variants={collapseExpandAnimation}
            >
                <UserMenu className="mt-6 lg:mt-0" />
            </m.div>
        </aside>
    );
};
