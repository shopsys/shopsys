import { fireEvent, render, screen } from '@testing-library/react';
import { AdditionalServices } from 'components/Blocks/Product/AdditionalServices/AdditionalServices';
import { TypeAdditionalServiceFragment } from 'graphql/requests/additionalServices/fragments/AdditionalServiceFragment.generated';
import type React from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/Animations/AnimateCollapseDiv', () => ({
    AnimateCollapseDiv: ({ children }: { children: React.ReactNode }) => <div>{children}</div>,
}));

vi.mock('components/Basic/Image/Image', () => ({
    Image: ({ className, src }: { className?: string; src: string }) => (
        <span className={className} data-testid={src} role="img" />
    ),
}));

vi.mock('components/Forms/Button/IconButton', () => ({
    IconButton: ({
        ariaLabel,
        className,
        onClick,
        tooltipLabel,
    }: {
        ariaLabel: string;
        className?: string;
        onClick: () => void;
        tooltipLabel?: string;
    }) => (
        <button
            aria-label={ariaLabel}
            className={className}
            data-tooltip-label={tooltipLabel}
            type="button"
            onClick={onClick}
        />
    ),
}));

vi.mock('framer-motion', () => ({
    AnimatePresence: ({ children }: { children: React.ReactNode }) => children,
}));

vi.mock('utils/formatting/useFormatPrice', () => ({
    useFormatPrice: () => (price: string | number) => `€${price}`,
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, options?: Record<string, string | number>) =>
            Object.entries(options ?? {}).reduce(
                (translatedKey, [optionKey, optionValue]) =>
                    translatedKey.replaceAll(`{{ ${optionKey} }}`, String(optionValue)),
                key,
            ),
    }),
}));

describe('AdditionalServices', () => {
    const createAdditionalService = (index: number): TypeAdditionalServiceFragment => ({
        __typename: 'AdditionalService',
        id: index,
        catnum: `SERVICE-${index}`,
        deliveryDaysExtension: 1,
        description: `Description ${index}`,
        mainImage: {
            __typename: 'Image',
            name: `Service image ${index}`,
            url: `/service-${index}.jpg`,
        },
        name: `Service ${index}`,
        price: {
            __typename: 'Price',
            priceWithVat: `${index * 10}`,
            priceWithoutVat: `${index * 8}`,
            vatAmount: `${index * 2}`,
        },
        uuid: `service-${index}`,
    });

    test('toggles the service from the checkbox, image, name, and delivery extension only', () => {
        const additionalService = createAdditionalService(1);
        const onToggleService = vi.fn();

        render(
            <AdditionalServices
                additionalServices={[additionalService]}
                selectedServiceUuids={[]}
                unitName="pcs"
                onToggleService={onToggleService}
            />,
        );

        const descriptionButton = screen.getByRole('button', { name: 'Show description of Service 1' });
        expect(descriptionButton).toHaveAttribute('data-tooltip-label', 'Show additional service description');

        const checkbox = screen.getByRole('checkbox', { name: /Service 1/ });
        const checkboxLabel = checkbox.parentElement?.querySelector('label');
        const checkboxVisual = checkboxLabel?.children[0];
        const serviceName = screen.getByText('Service 1');
        const serviceNameLabel = serviceName.closest('label');
        const deliveryExtensionLabel = screen.getByText('Extends delivery by 1 working days').closest('label');
        const serviceContent = serviceName.closest('label')?.parentElement?.parentElement;
        const serviceImage = screen.getByTestId('/service-1.jpg');
        expect(checkbox.closest('li')).not.toHaveClass('py-2');
        expect(checkbox.closest('li')).toHaveClass(
            'grid-cols-[auto_auto_minmax(0,1fr)]',
            'gap-x-2',
            'md:grid-cols-[minmax(0,1fr)_auto]',
            'md:gap-3',
        );
        expect(checkbox.closest('ul')).not.toHaveClass('gap-2');
        expect(checkbox.parentElement?.parentElement).toHaveClass('contents', 'md:flex', 'md:col-start-1');
        expect(checkboxLabel).toHaveClass('w-fit', 'gap-0', 'md:min-h-10');
        expect(serviceContent).toHaveClass('col-start-3', 'md:col-auto');
        expect(checkboxVisual).toHaveClass('size-5', 'border-input-border-default', 'rounded-checkbox');
        expect(serviceImage).toHaveClass('size-8', 'shrink-0', 'object-contain', 'mix-blend-multiply');
        expect(serviceImage.closest('label')).toHaveAttribute('for', checkbox.id);
        expect(serviceNameLabel).toHaveAttribute('for', checkbox.id);
        expect(serviceNameLabel).toHaveClass('w-fit', 'max-w-full');
        expect(serviceNameLabel?.parentElement).toHaveClass('w-fit', 'max-w-full');
        expect(deliveryExtensionLabel).toHaveAttribute('for', checkbox.id);
        expect(deliveryExtensionLabel).toHaveClass('w-fit', 'max-w-full');
        expect(descriptionButton.parentElement).toContainElement(serviceName);

        fireEvent.click(checkbox);
        expect(onToggleService).toHaveBeenCalledWith(additionalService, true);
        expect(checkbox).toBeChecked();
        expect(checkboxVisual).toHaveClass('border-input-border-active', 'bg-input-fill');

        onToggleService.mockClear();
        fireEvent.click(screen.getByText('Service 1'));
        expect(onToggleService).toHaveBeenCalledWith(additionalService, false);

        onToggleService.mockClear();
        fireEvent.click(serviceImage);
        fireEvent.click(screen.getByText('Extends delivery by 1 working days'));

        expect(onToggleService).toHaveBeenCalledTimes(2);
        expect(onToggleService).toHaveBeenNthCalledWith(1, additionalService, true);
        expect(onToggleService).toHaveBeenNthCalledWith(2, additionalService, false);

        onToggleService.mockClear();
        const addOnPrices = screen.getAllByText('+ €10');
        expect(addOnPrices).toHaveLength(2);
        expect(addOnPrices[0].parentElement?.parentElement).toHaveClass('hidden', 'md:inline');
        expect(addOnPrices[0].closest('label')).toBeNull();
        expect(addOnPrices[1].parentElement?.parentElement).toHaveClass('md:hidden');
        fireEvent.click(addOnPrices[1]);

        expect(onToggleService).not.toHaveBeenCalled();

        onToggleService.mockClear();
        fireEvent.click(screen.getByRole('button', { name: 'Show description of Service 1' }));
        expect(onToggleService).not.toHaveBeenCalled();
    });

    test('does not render an info action when the service has no description', () => {
        const additionalService = { ...createAdditionalService(1), description: null };

        render(
            <AdditionalServices
                additionalServices={[additionalService]}
                selectedServiceUuids={[additionalService.uuid]}
                unitName="pcs"
                onToggleService={vi.fn()}
            />,
        );

        expect(screen.queryByRole('button', { name: 'Show description of Service 1' })).not.toBeInTheDocument();
    });

    test('shows an unselected service as a black unit-price add-on regardless of quantity', () => {
        const additionalService = createAdditionalService(1);

        render(
            <AdditionalServices
                additionalServices={[additionalService]}
                quantity={3}
                selectedServiceUuids={[]}
                unitName="pcs"
                onToggleService={vi.fn()}
            />,
        );

        const prices = screen.getAllByText('+ €10');
        expect(prices).toHaveLength(2);
        expect(prices[0]).toHaveClass('text-text-default');
        expect(prices[1].parentElement?.parentElement).toHaveClass('md:hidden');
        expect(screen.queryByText('/ pcs')).not.toBeInTheDocument();
        expect(prices[0].closest('label')).toBeNull();
        expect(screen.queryByText('€10 / pcs')).not.toBeInTheDocument();
        expect(screen.queryByText('€30')).not.toBeInTheDocument();
    });

    test('shows the selected service price in black without an add sign on mobile', () => {
        const additionalService = createAdditionalService(1);

        render(
            <AdditionalServices
                additionalServices={[additionalService]}
                quantity={3}
                selectedServiceUuids={[additionalService.uuid]}
                unitName="pcs"
                onToggleService={vi.fn()}
            />,
        );

        const desktopPrice = screen.getByText('+ €10');
        const mobilePrice = screen.getByText('€10');
        expect(desktopPrice).toHaveClass('font-semibold', 'text-text-default');
        expect(desktopPrice.parentElement?.parentElement).toHaveClass('hidden', 'md:inline');
        expect(mobilePrice).toHaveClass('font-semibold', 'text-text-default');
        expect(mobilePrice.parentElement?.parentElement).toHaveClass('md:hidden');
        expect(screen.getAllByText('/ pcs')[0]).toHaveClass('text-text-less', 'text-sm');
        expect(desktopPrice.closest('label')).toBeNull();
        expect(screen.queryByText('€30')).not.toBeInTheDocument();
    });

    test('shows the selected service total price highlighted without a unit', () => {
        const selectedAdditionalService = createAdditionalService(1);

        render(
            <AdditionalServices
                additionalServices={[selectedAdditionalService]}
                quantity={3}
                selectedServiceUuids={[selectedAdditionalService.uuid]}
                showSelectedServiceTotalPrice
                unitName="pcs"
                onToggleService={vi.fn()}
            />,
        );

        const totalPrices = screen.getAllByText('€30');
        expect(totalPrices).toHaveLength(2);
        expect(totalPrices[0]).toHaveClass('font-semibold', 'text-price-default');
        expect(totalPrices[1]).toHaveClass('font-semibold', 'text-price-default');
        expect(screen.queryByText('+ €10')).not.toBeInTheDocument();
        expect(screen.queryByText('€10')).not.toBeInTheDocument();
        expect(screen.queryByText('/ pcs')).not.toBeInTheDocument();
    });

    test('shows the unit for a not selected service on the product detail', () => {
        const additionalService = createAdditionalService(1);

        render(
            <AdditionalServices
                additionalServices={[additionalService]}
                quantity={3}
                selectedServiceUuids={[]}
                showSelectedServiceTotalPrice
                unitName="pcs"
                onToggleService={vi.fn()}
            />,
        );

        expect(screen.getAllByText('+ €10')).toHaveLength(2);
        expect(screen.getAllByText('/ pcs')).toHaveLength(2);
        expect(screen.queryByText('€30')).not.toBeInTheDocument();
    });

    test('shows the unit price as an add-on inside a not selected service in the cart list', () => {
        const additionalService = createAdditionalService(1);

        render(
            <AdditionalServices
                isInCartList
                additionalServices={[additionalService]}
                quantity={3}
                selectedServiceUuids={[]}
                unitName="pcs"
                onToggleService={vi.fn()}
            />,
        );

        expect(screen.queryByText('(€10)')).not.toBeInTheDocument();
        const prices = screen.getAllByText('+ €10');
        expect(prices).toHaveLength(2);
        expect(prices[0]).toHaveClass('font-semibold', 'text-text-default');
        expect(screen.queryByText('/ pcs')).not.toBeInTheDocument();
        expect(prices[0].closest('label')).toBeNull();
        expect(screen.queryByText('+ €30')).not.toBeInTheDocument();
        expect(screen.queryByText('€10 / pcs')).not.toBeInTheDocument();
    });

    test('shows the quantity with unit and total prices for a selected service in the cart list', () => {
        const additionalService = createAdditionalService(1);

        render(
            <AdditionalServices
                isInCartList
                additionalServices={[additionalService]}
                quantity={3}
                selectedServiceUuids={[additionalService.uuid]}
                unitName="pcs"
                onToggleService={vi.fn()}
            />,
        );

        expect(screen.queryByText('(€10)')).not.toBeInTheDocument();
        const checkbox = screen.getByRole('checkbox', { name: /Service 1/ });
        const checkboxVisual = checkbox.parentElement?.querySelector('label')?.children[0];
        expect(checkbox).toBeChecked();
        expect(checkboxVisual).toHaveClass('size-5', 'border-input-border-active', 'bg-input-fill');
        expect(checkbox.parentElement?.parentElement).toHaveClass(
            '[&>div]:row-span-2',
            '[&>label]:row-span-2',
            'md:[&>div]:row-span-1',
            'md:[&>label]:row-span-1',
        );
        expect(checkbox.parentElement?.querySelector('[data-service-selection-state]')).not.toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Show description of Service 1' }).parentElement).toContainElement(
            screen.getByText('Service 1'),
        );
        expect(screen.queryByText('+ €10')).not.toBeInTheDocument();
        const mobileQuantityAndUnitPrice = screen.getByText('3 × €10');
        const desktopUnitPrice = screen.getByText('€10');
        const [mobileUnit] = screen.getAllByText('/ pcs');
        expect(mobileQuantityAndUnitPrice).toHaveClass('font-semibold', 'text-text-default');
        expect(mobileUnit).toHaveClass('text-text-less');
        expect(screen.getAllByText('€30')[0]).toHaveClass('text-price-default');
        expect(screen.getAllByText('€30')[0].parentElement).toHaveClass(
            'w-full',
            'justify-between',
            'md:flex-col',
            'vl:hidden',
        );
        expect(checkbox.closest('li')).toHaveClass('vl:contents');
        expect(mobileQuantityAndUnitPrice.closest('div')).toHaveClass('col-start-3', 'row-start-2', 'vl:contents');
        expect(screen.getByText('3', { selector: '.vl\\:col-start-2' })).toHaveClass(
            'vl:block',
            'hidden',
            'min-w-35',
            'text-center',
        );
        expect(desktopUnitPrice.parentElement).toHaveClass('hidden', 'vl:col-start-3', 'vl:block');
        expect(screen.getAllByText('€30')[1]).toHaveClass('text-price-default');
        expect(screen.getAllByText('€30')[1].parentElement).toHaveClass('hidden', 'vl:col-start-4', 'vl:flex');
    });

    test('shows an unselected single-piece service as a black unit-price add-on', () => {
        const additionalService = createAdditionalService(1);

        render(
            <AdditionalServices
                additionalServices={[additionalService]}
                quantity={1}
                selectedServiceUuids={[]}
                unitName="pcs"
                onToggleService={vi.fn()}
            />,
        );

        const prices = screen.getAllByText('+ €10');
        expect(prices).toHaveLength(2);
        expect(prices[0]).toHaveClass('text-text-default');
        expect(screen.queryByText('/ pcs')).not.toBeInTheDocument();
        expect(prices[0].closest('label')).toBeNull();
        expect(prices[0].closest('li')).toContainElement(
            screen.getByRole('button', { name: 'Show description of Service 1' }),
        );
    });

    test('shows the selected single-piece service unit price in black', () => {
        const additionalService = createAdditionalService(1);

        render(
            <AdditionalServices
                additionalServices={[additionalService]}
                quantity={1}
                selectedServiceUuids={[additionalService.uuid]}
                unitName="pcs"
                onToggleService={vi.fn()}
            />,
        );

        const desktopPrice = screen.getByText('+ €10');
        const mobilePrice = screen.getByText('€10');
        expect(desktopPrice).toHaveClass('font-semibold', 'text-text-default');
        expect(mobilePrice).toHaveClass('font-semibold', 'text-text-default');
        expect(mobilePrice.parentElement?.parentElement).toHaveClass('md:hidden');
        expect(screen.getAllByText('/ pcs')[0]).toHaveClass('text-text-less');
        expect(desktopPrice.closest('label')).toBeNull();
    });

    test('shows quantity with unit and total prices for one selected cart service', () => {
        const additionalService = createAdditionalService(1);
        const onToggleService = vi.fn();

        render(
            <AdditionalServices
                isInCartList
                additionalServices={[additionalService]}
                quantity={1}
                selectedServiceUuids={[additionalService.uuid]}
                unitName="pcs"
                onToggleService={onToggleService}
            />,
        );

        const mobileQuantityAndUnitPrice = screen.getByText('1 × €10');
        const mobilePriceRow = mobileQuantityAndUnitPrice.parentElement?.parentElement;
        expect(mobilePriceRow).toHaveClass('w-full', 'justify-between', 'vl:hidden');
        expect(mobilePriceRow).toContainElement(mobileQuantityAndUnitPrice);
        expect(screen.getByText('1', { selector: '.vl\\:col-start-2' })).toBeInTheDocument();

        fireEvent.click(mobileQuantityAndUnitPrice);

        expect(onToggleService).not.toHaveBeenCalled();
    });

    test('shows the number of initially hidden services', () => {
        const additionalServices = Array.from({ length: 6 }, (_, index) => createAdditionalService(index + 1));

        render(
            <AdditionalServices
                additionalServices={additionalServices}
                selectedServiceUuids={[]}
                unitName="pcs"
                onToggleService={vi.fn()}
            />,
        );

        expect(screen.getByRole('button', { name: '+ 2 additional services' })).toBeInTheDocument();
        expect(screen.queryByText('Service 5')).not.toBeInTheDocument();

        fireEvent.click(screen.getByRole('button', { name: '+ 2 additional services' }));

        expect(screen.getByText('Service 5')).toBeInTheDocument();
        expect(screen.getByText('Service 6')).toBeInTheDocument();
        expect(screen.getByText('Service 5').closest('ul')).toHaveClass('gap-4', 'pt-4', 'md:gap-2', 'md:pt-2');
    });

    test('keeps selected services visible and excludes them from the hidden count', () => {
        const additionalServices = Array.from({ length: 6 }, (_, index) => createAdditionalService(index + 1));

        render(
            <AdditionalServices
                additionalServices={additionalServices}
                selectedServiceUuids={[additionalServices[4].uuid]}
                unitName="pcs"
                onToggleService={vi.fn()}
            />,
        );

        expect(screen.getByText('Service 5')).toBeInTheDocument();
        expect(screen.queryByText('Service 6')).not.toBeInTheDocument();
        expect(screen.getByRole('button', { name: '+ 1 additional services' })).toBeInTheDocument();

        fireEvent.click(screen.getByRole('button', { name: '+ 1 additional services' }));

        expect(screen.getByText('Service 5')).toBeInTheDocument();
        expect(screen.getByText('Service 6')).toBeInTheDocument();
    });
});
