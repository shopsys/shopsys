import { StyleguideSection } from './StyleguideElements';
import { useEffect, useState } from 'react';

type StyleguideIconsProps = { iconList: string[] };

type IconType = {
    Icon: FC;
    name: string;
};

export const StyleguideIcons: FC<StyleguideIconsProps> = ({ iconList }) => {
    const [icons, setIcons] = useState<IconType[]>();

    const getAllIcons = () =>
        iconList.map(async (iconFileNameWithExtension) => {
            const iconFileName = iconFileNameWithExtension.split('.')[0];
            const Icon = (await import(`/components/Basic/Icon/${iconFileName}`))[iconFileName];

            return { Icon, name: iconFileName };
        });

    useEffect(() => {
        const fetch = async () => {
            const resolvedIcons = await Promise.all(getAllIcons());

            setIcons(resolvedIcons);
        };

        fetch();
    }, []);

    return (
        <StyleguideSection className="md:columns-2 lg:columns-3 gap-3" title="Icons">
            {icons?.map(({ Icon, name }, index) => (
                <div key={index} className="flex gap-3 items-center mt-3 first:mt-0">
                    <Icon className="w-10" />
                    <span>{name}</span>
                </div>
            ))}
        </StyleguideSection>
    );
};
