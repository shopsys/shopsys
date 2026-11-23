import { AdditionalServices } from 'components/Blocks/Product/AdditionalServices/AdditionalServices';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { CheckboxColor } from 'components/Forms/CheckboxColor/CheckboxColor';
import { TypeAdditionalServiceFragment } from 'graphql/requests/additionalServices/fragments/AdditionalServiceFragment.generated';
import { useState } from 'react';
import { StyleguidePreview, StyleguideSubSection } from './StyleguideElements';

const createStyleguideAdditionalService = (index: number): TypeAdditionalServiceFragment => ({
    __typename: 'AdditionalService',
    id: index,
    uuid: `styleguide-additional-service-${index}`,
    name: `Additional service ${index}`,
    catnum: `STYLEGUIDE-${index}`,
    description: index === 1 ? 'The description opens separately from the checkbox.' : null,
    deliveryDaysExtension: index === 1 ? 2 : null,
    price: { __typename: 'Price', priceWithVat: `${index * 100}`, priceWithoutVat: `${index * 80}`, vatAmount: '0' },
    mainImage: index === 1 ? { __typename: 'Image', name: null, url: '/images/noimage.webp' } : null,
});

// one more than the initially visible services, so the "show more" text action is shown
const STYLEGUIDE_ADDITIONAL_SERVICES = Array.from({ length: 5 }, (_, index) =>
    createStyleguideAdditionalService(index + 1),
);

export const StyleguideCheckboxes: FC = () => {
    const [isChecked, setIsChecked] = useState(false);
    const [isChecked2, setIsChecked2] = useState(false);
    const [isChecked3, setIsChecked3] = useState(true);
    const [isChecked4, setIsChecked4] = useState(true);
    const [isColorChecked, setIsColorChecked] = useState(false);
    const [isColorChecked2, setIsColorChecked2] = useState(true);
    const [selectedServiceUuids, setSelectedServiceUuids] = useState<string[]>([]);

    const toggleAdditionalService = (additionalService: TypeAdditionalServiceFragment, isSelected: boolean) =>
        setSelectedServiceUuids((currentUuids) =>
            isSelected
                ? [...currentUuids, additionalService.uuid]
                : currentUuids.filter((uuid) => uuid !== additionalService.uuid),
        );

    return (
        <>
            <StyleguideSubSection title="Regular checkboxes">
                <StyleguidePreview className="grid items-start gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <Checkbox
                        id="checkboxRegular"
                        label="Regular checkbox"
                        name="Regular checkbox"
                        value={isChecked}
                        onChange={() => setIsChecked((currentChecked) => !currentChecked)}
                    />
                    <Checkbox
                        id="checkboxChecked"
                        label="Checked checkbox"
                        name="Checked checkbox"
                        value={isChecked4}
                        onChange={() => setIsChecked4((currentChecked) => !currentChecked)}
                    />
                    <Checkbox
                        count={2}
                        id="checkboxWithCount"
                        label="Checkbox with count"
                        name="Checkbox with count"
                        value={isChecked2}
                        onChange={() => setIsChecked2((currentChecked) => !currentChecked)}
                    />
                    <Checkbox
                        disabled
                        id="checkboxDisabled"
                        label="Disabled checkbox"
                        name="Disabled checkbox"
                        value={false}
                        onChange={() => null}
                    />
                    <Checkbox
                        disabled
                        id="checkboxDisabledChecked"
                        label="Disabled checked checkbox"
                        name="Disabled checked checkbox"
                        value={isChecked3}
                        onChange={() => setIsChecked3((currentChecked) => !currentChecked)}
                    />
                </StyleguidePreview>
            </StyleguideSubSection>

            <StyleguideSubSection title="Color checkboxes">
                <StyleguidePreview className="flex flex-col gap-3">
                    <CheckboxColor
                        bgColor="#e11d48"
                        id="checkboxColorRegular"
                        label="Red"
                        name="Red"
                        value={isColorChecked}
                        onChange={() => setIsColorChecked((currentChecked) => !currentChecked)}
                    />
                    <CheckboxColor
                        bgColor="#2563eb"
                        id="checkboxColorChecked"
                        label="Blue"
                        name="Blue"
                        value={isColorChecked2}
                        onChange={() => setIsColorChecked2((currentChecked) => !currentChecked)}
                    />
                    <CheckboxColor
                        disabled
                        bgColor="#16a34a"
                        id="checkboxColorDisabled"
                        label="Disabled green"
                        name="Disabled green"
                        value={false}
                        onChange={() => null}
                    />
                </StyleguidePreview>
            </StyleguideSubSection>

            <StyleguideSubSection
                description="Five services, one more than the initially visible ones, so the show more action is shown."
                title="Additional services"
            >
                <StyleguidePreview className="max-w-xl">
                    <AdditionalServices
                        additionalServices={STYLEGUIDE_ADDITIONAL_SERVICES}
                        selectedServiceUuids={selectedServiceUuids}
                        tidDiscriminator="styleguide"
                        unitName="pcs"
                        onToggleService={toggleAdditionalService}
                    />
                </StyleguidePreview>
            </StyleguideSubSection>
        </>
    );
};
