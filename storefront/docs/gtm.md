
#  GTM
## This documentation is complemented with a video-introduction to GTM that can be found [here](https://drive.google.com/file/d/1YL_5h8HuNyuB2JlcTcEe2nNMQ15DPBKh/view?usp=sharing)

## User stories in which the changes were introduced

- [FWCC-799](https://shopsys.atlassian.net/browse/FWCC-799) - basic GTM layer
- [FWCC-1074](https://shopsys.atlassian.net/browse/FWCC-1074) - multi-domain GTM
- [FWCC-1079](https://shopsys.atlassian.net/browse/FWCC-1079) - improvements according to updated specifications

## Folder structure
- hooks used for asynchronous events that need to wait for something (mostly view events) can be found in `storefront/hooks/gtm/*.ts`
	```markdown
	├── storefront
	│   ├── hooks
	│   │   ├── gtm
	│   │   │   ├── *.ts
	```
- event handlers used for sending events that are sent after a user action (on-click and on-event actions) can be found in `storefront/utils/Gtm`
	```markdown
	├── storefront
	│   ├── utils
	│   │   ├── Gtm
	│   │   │   ├── *.{ts,tsx}
	```
- types of all GTM related events and enums can be found in `storefront/types/gtm.ts`
	```markdown
	├── storefront
	│   ├── types
	│   │   ├── gtm.ts
	```

## Hooks  <small> - used for asynchronous events that need to wait for something (mostly view events)</small> 

### Page view hooks

#### useGtmCartView
- user has viewed the cart page (1st order step)

| name | description |
| --- | --- |
| gtmStaticPageViewEvent | Page view event object obtained from the `useGtmStaticPageViewEvent` helper |

---

#### useGtmFriendlyPageView
- user has viewed a friendly URL page (general hook for all friendly URL pages)

| name | description |
| --- | --- |
| event | Page view event object obtained from the `useGtmPageViewEvent` helper in a combination with the `getGtmPageInfoForFriendlyUrl` method|
| slug | Slug of the friendly URL entity used to track URL changes |

---

#### useGtmStaticPageView
- user has viewed a static page
- if you have a new static page that requires the static page view event to be pushed, you have to use the `useGtmStaticPageView` hook, to which you must send the previously obtained `event` object
	- either send a special page type `
	```ts
	const gtmStaticPageViewEvent =  useGtmStaticPageViewEvent('my special page type');
	useGtmStaticPageView(gtmStaticPageViewEvent);
	```
	- or the default 'other' type
	```ts
	const gtmStaticPageViewEvent =  useGtmStaticPageViewEvent('other');
	useGtmStaticPageView(gtmStaticPageViewEvent);
	```

| name | description |
| --- | --- |
| event | Page view event object obtained from the `useGtmPageViewEvent` helper |

---

#### useGtmProductDetailView
- user has viewed a product detail page (extra hook if the friendly URL page is a product detail page as well)

| name | description |
| --- | --- |
| data | Product data of the currently viewed product |
| slug | slug of the currently viewed product used to track URL changes |

---

#### useGtmSearchResultView
- user has viewed the search results page

| name | description |
| --- | --- |
| searchResult | Object with the current search results |
| keyword | Search keyword for which the current search results are displayed |

---

#### useGtmPaymentShippingView
- user has viewed the shipping & payment page (2nd order step)

| name | description |
| --- | --- |
| gtmStaticPageViewEvent | Page view event object obtained from the `useGtmStaticPageViewEvent` helper |

---

#### useGtmShippingDataView
- user has viewed the contact information page (3rd order step)

| name | description |
| --- | --- |
| gtmStaticPageViewEvent | Page view event object obtained from the `useGtmStaticPageViewEvent` helper |

---

### List view hooks

#### useGtmCategoryProductListView
- user has viewed the product list on the category page 

| name | description |
| --- | --- |
| data | Friendly URL page data, which are then tested, if they are of type `Category` |
| slug | Slug of the friendly URL entity used to track URL changes |

---

#### useGtmSliderProductListView
- user has viewed the product slider on homepage

| name | description |
| --- | --- |
| products | Products of the currently displayed slider |
| listName | Special GTM list name, which is used to differentiate between different list placements |

---

#### useGtmBrandProductListView
- user has viewed the product list on the brand page

| name | description |
| --- | --- |
| data | Friendly URL page data, which are then tested, if they are of type `Brand` |
| slug | Slug of the friendly URL entity used to track URL changes |

---

#### useGtmFlagProductListView
- user has viewed the product list on the flag page

| name | description |
| --- | --- |
| data | Friendly URL page data, which are then tested, if they are of type `Flag` |
| slug | Slug of the friendly URL entity used to track URL changes |

---

#### useGtmSearchResultsListView
- user has viewed the promoted products on homepage

| name | description |
| --- | --- |
| data | Search page data with search results |
| searchQuery | Current search query for which the search results are displayed |

## Event handlers <small> - used for sending events that are sent after a user action (on-click and on-event actions)</small> 

### On-click event handlers

#### onClickProductDetailGtmEventHandler
- used when the user clicks on a product that takes him to the product detail page (autocomplete, slider, category)

| name | description |
| --- | --- |
| product | Product on which the user has clicked |
| listName | Special GTM list name of the list on which the user has clicked |
| index | Index (position) of the product in the list |
| domainUrl | Current domain URL used for the composition of the product's absolute URL |

---

### Order event handlers

#### onChangeCartItemGtmEventHandler
- used when the cart item quantity changes (increases, decreases)

| name | description |
| --- | --- |
| addedCartItem | Product which the user has added to his cart |
| currencyCode | Currency of the current domain |
| eventValue | Value of the current event without tax (VAT), corresponding to the quantity of the added/removed items multiplied by the per-item price of the product |
| eventValueWithTax | Value of the current event with tax (VAT), corresponding to the quantity of the added/removed items multiplied by the per-item price of the product |
| listIndex | Index (position) of the product in the list |
| quantityDifference | Difference between the quantity before the event and after the event, used to differentiate between incrementing and decrementing |
| listName | Special GTM list name of the list from which the user has added the product |
| domainUrl | Current domain URL used for the composition of the product's absolute URL |

---

#### onRemoveCartItemGtmEventHandler
- used when the cart item is completely removed from the cart

| name | description |
| --- | --- |
| removedCartItem | Product which the user has removed from his cart |
| currencyCode | Currency of the current domain |
| eventValue | Value of the current event without tax (VAT), corresponding to the quantity of the removed items multiplied by the per-item price of the product |
| eventValueWithTax | Value of the current event with tax (VAT), corresponding to the quantity of the removed items multiplied by the per-item price of the product |
| listIndex | Index (position) of the product in the list |
| listName | Special GTM list name of the list from which the user has removed the product |
| domainUrl | Current domain URL used for the composition of the product's absolute URL |

---

#### onPurchaseOrderGtmEventHandler
- used when the user creates and order

| name | description |
| --- | --- |
| cart | Cart object of the current user |
| transport | Transport object of the currently selected transport option |
| pickupPlace | Pickup place (store) object of the currently selected pickup place |
| payment |Payment object of the currently selected payment option |
| promoCode | Value of the currently applied promo code |
| orderNumber | Number of the newly created order |
| domainUrl | Current domain URL used for the composition of the product's absolute URL |

---

#### onClickSuggestResultGtmEventHandler
- used when the user clicks on a search result in autocomplete

| name | description |
| --- | --- |
| keyword | Current keyword for which the search results are displayed |
| section | Special GTM section name indicating of what type is the clicked result (product, brand, category...) |
| itemName | Name of the clicked entity |

---

#### onTransportChangeGtmEventHandler
- used when a new transport method is selected

| name | description |
| --- | --- |
| gtmCartInfo | GTM cart info object of the current cart |
| updatedTransport | Transport object of the newly selected transport option |
| updatedPickupPlace | Pickup place (store) object of the newly selected pickup place |
| updatedPaymentName | Name of the newly selected payment option |
| currencyCode | Currency of the current domain |

---

#### onPaymentChangeGtmEventHandler
- used when a new payment method is selected

| name | description |
| --- | --- |
| gtmCartInfo | GTM cart info object of the current cart |
| updatedPayment | Payment object of the newly selected payment option |
| currencyCode | Currency of the current domain |

---

#### onConsentUpdateGtmEventHandler
- used when the user updates his cookie consent either using 
	- the consent popup
	- or the update consent page 

| name | description |
| --- | --- |
| updatedConsent | Object with boolean values for each consent option |

## Implementing new GTM events
- if you have a new special event that needs to be called asynchronously when the page is loaded/viewed, you will have to write the hook yourself, but can take inspiration from:
	- `useGtmSliderProductListView` if it is a view event that depends on custom data to be loaded (products in this case)
	- `useGtmStaticPageView` if it is a view event that depends on the cart to be loaded
	- 
- if you have a new special event that needs to be called after a user action is taken, you will have to implement it yourself, but you can inspire yourself by looking at:
	- `onClickSuggestResultEvent` if the event is a click event (like clicking on a button or a link)
	- `onChangeCartItemGtmEvent` if the event can have multiple GTM types but is the same on the application level

- if you have a list of products which should trigger a list view event when displayed, you will have to take care of pagination and how it should trigger page rerendering
	- a good example of this behaviour is the `useGtmCategoryProductListView` hook
	- in the code below you can see how we are:
		- indexing the products relatively to the current page
		- remembering the previous page start cursor and comparing it to the new one to see if the page changed and the data has loaded
	```ts
	export  const  useGtmCategoryProductListView  =  (data:  Maybe<FriendlyUrlPageType>  |  undefined, slug:  string):  void  =>  {
		const lastViewedCategorySlug =  useRef<string  |  undefined>(undefined);
		const lastViewedCategoryPageStartCursor =  useRef<string  |  undefined>(undefined);
		const  { currentPage, pageSize }  =  useShopsysSelector((state)  => state.user.pagination);

		useEffect(()  =>  {
			if  (
				data !==  null  &&
				data !==  undefined  &&
				data.__typename  ===  'Category'  &&
				(lastViewedCategorySlug.current  !== slug ||
				lastViewedCategoryPageStartCursor.current  !== data.productConnection.pageInfo.startCursor)
			) {
				lastViewedCategorySlug.current = slug;
				lastViewedCategoryPageStartCursor.current = data.productConnection.pageInfo.startCursor;
				
				const event =  getNewGtmEcommerceEvent('ec.products_list',  true);
				event.ecommerce  =  getGtmProductsListEvent(
					data.productConnection.products,
					getCategoryOrSeoCategoryGtmListName(data, slug),
					currentPage,
					pageSize,
				);
		
				gtmSafePushEvent(event);
			}
		},  [data, slug, currentPage, pageSize]);
	};
	```

##  Multi-domain GTM support
- multiple GTM API keys are supported on a per-domain basis
- if you need multiple keys, you have to add them everywhere, where `GTM_ID` is present
	- deploy script `deploy-project.sh`
	- file with environmental variables `.env`
	- GitLab environmental variables
- after creating another API key, you can now fill it according to your need in `next.config.js`
```ts
publicRuntimeConfig:  {
	...
	domains: [
		{
			...
			gtmId: process.env.GTM_ID,
		},
		{
			...
			gtmId: process.env.GTM_ID_2,
		},
		{
			...
			gtmId: process.env.GTM_ID_3,
		},
	],
},
```
