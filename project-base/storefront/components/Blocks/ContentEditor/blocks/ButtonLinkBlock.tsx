import { BlockProps } from 'components/Blocks/ContentEditor/ContentRenderer';
import { TIDs } from 'cypress/tids';
import { ButtonLinkBlockData } from 'types/contentEditor';

const positionClassNames = { left: 'mr-auto', center: 'mx-auto', right: 'ml-auto' };

export const ButtonLinkBlock: FC<BlockProps<ButtonLinkBlockData>> = ({ block }) => {
    const { text, url, openInNewTab, position, color } = block.data;

    return (
        <a
            className={`my-3 block h-fit w-fit cursor-pointer rounded-lg border-2 px-3 py-1.75 text-center font-semibold text-button-primary-text-default leading-4.5 no-underline outline-hidden transition-all hover:text-button-primary-text-hovered hover:no-underline ${positionClassNames[position]}`}
            data-tid={TIDs.content_block_button_link}
            href={url}
            style={{ backgroundColor: color, borderColor: color }}
            {...(openInNewTab && { target: '_blank', rel: 'noopener' })}
        >
            {text}
        </a>
    );
};
