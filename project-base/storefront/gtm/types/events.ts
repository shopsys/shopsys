import {
    GtmCartInfoType,
    GtmCartItemType,
    GtmConsentInfoType,
    GtmListedProductType,
    GtmPageInfoType,
    GtmProductInterface,
    GtmReviewConsentsType,
    GtmUserEntryInfoType,
    GtmUserInfoType,
} from './objects';
import { GtmDeviceTypes } from 'gtm/enums/GtmDeviceTypes';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmFormType } from 'gtm/enums/GtmFormType';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmMessageType } from 'gtm/enums/GtmMessageType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { GtmSectionType } from 'gtm/enums/GtmSectionType';

export type GtmEventInterface<EventType, EventContent> = {
    event: EventType;
    _clear: boolean;
} & EventContent;

export type GtmPageViewEventType = GtmEventInterface<
    GtmEventType.page_view,
    {
        language: string;
        currencyCode: string;
        consent: GtmConsentInfoType;
        page: GtmPageInfoType;
        user: GtmUserInfoType;
        device: GtmDeviceTypes;
        cart?: GtmCartInfoType | null;
        _isLoaded: boolean;
    }
>;

export type GtmConsentUpdateEventType = GtmEventInterface<
    GtmEventType.consent_update,
    {
        consent: GtmConsentInfoType;
    }
>;

export type GtmChangeCartItemEventType = GtmAddToCartEventType | GtmRemoveFromCartEventType;

export type GtmAddToCartEventType = GtmEventInterface<
    GtmEventType.add_to_cart,
    {
        ecommerce: {
            listName: GtmProductListNameType;
            currencyCode: string;
            valueWithoutVat: number | null;
            valueWithVat: number | null;
            products: GtmCartItemType[] | undefined;
            arePricesHidden: boolean;
        };
        cart?: GtmCartInfoType | null;
    }
>;

export type GtmRemoveFromCartEventType = GtmEventInterface<
    GtmEventType.remove_from_cart,
    {
        ecommerce: {
            listName: GtmProductListNameType;
            currencyCode: string;
            valueWithoutVat: number | null;
            valueWithVat: number | null;
            products: GtmCartItemType[] | undefined;
            arePricesHidden: boolean;
        };
        cart?: GtmCartInfoType | null;
    }
>;

export type GtmCartViewEventType = GtmEventInterface<
    GtmEventType.cart_view,
    {
        ecommerce: {
            currencyCode: string;
            valueWithoutVat: number | null;
            valueWithVat: number | null;
            products: GtmCartItemType[] | undefined;
            arePricesHidden: boolean;
        };
    }
>;

export type GtmProductListViewEventType = GtmEventInterface<
    GtmEventType.product_list_view,
    {
        ecommerce: {
            listName: GtmProductListNameType;
            products: GtmListedProductType[] | undefined;
            arePricesHidden: boolean;
        };
    }
>;

export type GtmProductClickEventType = GtmEventInterface<
    GtmEventType.product_click,
    {
        ecommerce: {
            listName: GtmProductListNameType;
            products: GtmListedProductType[] | undefined;
            arePricesHidden: boolean;
        };
    }
>;

export type GtmProductDetailViewEventType = GtmEventInterface<
    GtmEventType.product_detail_view,
    {
        ecommerce: {
            currencyCode: string;
            valueWithoutVat: number | null;
            valueWithVat: number | null;
            products: GtmProductInterface[] | undefined;
            arePricesHidden: boolean;
        };
    }
>;

export type GtmPaymentAndTransportPageViewEventType = GtmEventInterface<
    GtmEventType.payment_and_transport_page_view,
    {
        ecommerce: {
            currencyCode: string;
            valueWithoutVat: number | null;
            valueWithVat: number | null;
            products: GtmCartItemType[] | undefined;
            arePricesHidden: boolean;
        };
    }
>;

export type GtmAutocompleteResultsViewEventType = GtmEventInterface<
    GtmEventType.autocomplete_results_view,
    {
        autocompleteResults: {
            keyword: string;
            results: number;
            sections: { [key in GtmSectionType]: number };
        };
    }
>;

export type GtmAutocompleteResultClickEventType = GtmEventInterface<
    GtmEventType.autocomplete_result_click,
    {
        autocompleteResultClick: {
            section: GtmSectionType;
            itemName: string;
            keyword: string;
        };
    }
>;

export type GtmTransportChangeEventType = GtmEventInterface<
    GtmEventType.transport_change,
    {
        ecommerce: {
            valueWithoutVat: number | null;
            valueWithVat: number | null;
            currencyCode: string;
            promoCodes?: string[];
            paymentType?: string;
            transportPriceWithoutVat: number | null;
            transportPriceWithVat: number | null;
            transportType: string;
            transportDetail: string;
            transportExtra: string[];
            products: GtmCartItemType[];
            arePricesHidden: boolean;
        };
    }
>;

export type GtmContactInformationPageViewEventType = GtmEventInterface<
    GtmEventType.contact_information_page_view,
    {
        ecommerce: {
            currencyCode: string;
            valueWithoutVat: number | null;
            valueWithVat: number | null;
            promoCodes?: string[];
            products: GtmCartItemType[] | undefined;
            arePricesHidden: boolean;
        };
    }
>;

export type GtmPaymentChangeEventType = GtmEventInterface<
    GtmEventType.payment_change,
    {
        ecommerce: {
            currencyCode: string;
            valueWithoutVat: number | null;
            valueWithVat: number | null;
            promoCodes?: string[];
            paymentType: string;
            paymentPriceWithoutVat: number | null;
            paymentPriceWithVat: number | null;
            products: GtmCartItemType[] | undefined;
            arePricesHidden: boolean;
        };
    }
>;

export type GtmPaymentFailEventType = GtmEventInterface<
    GtmEventType.payment_fail,
    {
        paymentFail: {
            id: string;
        };
    }
>;

export type GtmCreateOrderEventOrderPartType = {
    currencyCode: string;
    id: string;
    valueWithoutVat: number | null;
    valueWithVat: number | null;
    vatAmount: number;
    paymentPriceWithoutVat: number | null;
    paymentPriceWithVat: number | null;
    promoCodes?: string[];
    discountAmount?: number;
    paymentType: string;
    reviewConsents: GtmReviewConsentsType;
    products: GtmCartItemType[] | undefined;
};

export type GtmPurchaseEventPaymentPartType = {
    isPaymentSuccessful?: boolean;
};

export type GtmCreateOrderEventType = GtmEventInterface<
    GtmEventType.create_order,
    {
        ecommerce: GtmCreateOrderEventOrderPartType & GtmPurchaseEventPaymentPartType & { arePricesHidden: boolean };
        user: GtmUserInfoType;
    }
>;

export type GtmShowMessageEventType = GtmEventInterface<
    GtmEventType.show_message,
    {
        eventParameters: {
            type: GtmMessageType;
            origin?: GtmMessageOriginType;
            detail?: string;
            message: string;
        };
    }
>;

export type GtmSendFormEventType = GtmEventInterface<
    GtmEventType.send_form,
    {
        eventParameters: {
            form: GtmFormType;
        };
    }
>;

export type GtmUserEntryEventType = GtmEventInterface<
    GtmEventType.login | GtmEventType.registration,
    {
        user: GtmUserEntryInfoType | null | undefined;
    }
>;
