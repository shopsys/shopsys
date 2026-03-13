# Snapshots Info Lookup Table

## Snapshot Group - AUTHENTICATION

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 1-0-0 | [Register B2C] should register as a B2C customer | filled registration form | registration.cy.ts |
| 1-0-1 | [Register B2C] should register as a B2C customer | customer edit page | registration.cy.ts |
| 1-0-2 | [Register B2B] should register as a B2B (company) customer | filled b2b registration form | registration.cy.ts |
| 1-0-3 | [Register B2B] should register as a B2B (company) customer | b2b customer edit page | registration.cy.ts |
| 1-0-4 | [Empty Form] should disallow registration with empty registration form, show flash message, then allow after filling | after invalid try | registration.cy.ts |

## Snapshot Group - B2B

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 9-2-0 | should be able to view the orders list page | accountant-orders-list | accountantViewOnly.cy.ts |
| 9-2-1 | should be able to view the complaints list page | accountant-complaints-list | accountantViewOnly.cy.ts |
| 9-1-0 | should show the save profile button | edit-profile-owner | editProfileB2b.cy.ts |
| 9-1-1 | should have disabled company data fields | edit-profile-user-disabled-fields | editProfileB2b.cy.ts |
| 9-1-2 | should show the save profile button | edit-profile-limited-user | editProfileB2b.cy.ts |
| 9-3-0 | [Withdrawal Form] should display withdrawal form with pre-filled data | withdrawal-form-prefilled | orderWithdrawal.cy.ts |
| 9-3-1 | [Withdrawal Submit] should submit and redirect to success page | withdrawal-success | orderWithdrawal.cy.ts |

## Snapshot Group - CART

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 2-0-0 | [Cart Header Remove] should remove products from cart using cart in header and then display empty cart message | after first remove | cartInHeader.cy.ts |
| 2-0-1 | [Cart Header Remove] should remove products from cart using cart in header and then display empty cart message | after second remove | cartInHeader.cy.ts |
| 2-1-0 | [Prefilled Cart] should log in, add product to cart to an already prefilled cart, and empty cart after log out | cart page after login | cartLogin.cy.ts |
| 2-1-1 | [Prefilled Cart] should log in, add product to cart to an already prefilled cart, and empty cart after log out | cart page after adding product to cart | cartLogin.cy.ts |
| 2-1-2 | [Empty Cart] should log in, add product to an empty cart, and empty cart after log out | cart page after adding product to cart | cartLogin.cy.ts |
| 2-1-3 | [Merge Cart] should repeatedly merge carts when logged in (starting with an empty cart for the registered customer) | cart page after adding product to cart | cartLogin.cy.ts |
| 2-1-4 | [Merge Cart] should repeatedly merge carts when logged in (starting with an empty cart for the registered customer) | cart page after adding second product to cart | cartLogin.cy.ts |
| 2-1-5 | [Merge Cart] should repeatedly merge carts when logged in (starting with an empty cart for the registered customer) | cart page after second login | cartLogin.cy.ts |
| 2-1-6 | [Discard Cart] should discard user's previous cart after logging in in order 3rd step | cart page after first login | cartLogin.cy.ts |
| 2-1-7 | [Discard Cart] should discard user's previous cart after logging in in order 3rd step | third step before second login | cartLogin.cy.ts |
| 2-1-8 | [Discard Cart] should discard user's previous cart after logging in in order 3rd step | third step after second login | cartLogin.cy.ts |
| 2-2-0 | [Fast Quantity Clicked] should increase and decrease product quantity using spinbox in cart (once if clicked fast) | after increase | cartPage.cy.ts |
| 2-2-1 | [Fast Quantity Clicked] should increase and decrease product quantity using spinbox in cart (once if clicked fast) | after decrease | cartPage.cy.ts |
| 2-2-2 | [Slow Quantity Clicked] should increase and decrease product quantity using spinbox in cart (multiple times if clicked slowly) | after increase | cartPage.cy.ts |
| 2-2-3 | [Slow Quantity Clicked] should increase and decrease product quantity using spinbox in cart (multiple times if clicked slowly) | after decrease | cartPage.cy.ts |
| 2-2-4 | [Remove Products] should remove products from cart | after first removal | cartPage.cy.ts |
| 2-2-5 | [Remove Products] should remove products from cart | empty cart after second removal | cartPage.cy.ts |
| 2-2-6 | [Add Remove Promo] should add promo code to cart, check it, remove promo code from cart, and then add a different one | cart page after applying first promocode | cartPage.cy.ts |
| 2-2-7 | [Add Remove Promo] should add promo code to cart, check it, remove promo code from cart, and then add a different one | transport and payment page after applying first promocode | cartPage.cy.ts |
| 2-2-8 | [Add Remove Promo] should add promo code to cart, check it, remove promo code from cart, and then add a different one | cart page after removing first promocode | cartPage.cy.ts |
| 2-2-9 | [Add Remove Promo] should add promo code to cart, check it, remove promo code from cart, and then add a different one | cart page after removing second promocode | cartPage.cy.ts |
| 2-2-10 | [Add Promo Remove Product] should add promo code to cart, remove product that allows it, and see the promo code removed | after applying promocode | cartPage.cy.ts |
| 2-2-11 | [Add Promo Remove Product] should add promo code to cart, remove product that allows it, and see the promo code removed | after removing product that allows promocode | cartPage.cy.ts |
| 2-2-12 | [No Free Transport] transport should not be free if price minus promo code discount is below the free transport limit | cart page with non-free transport after applying promocode | cartPage.cy.ts |
| 2-2-13 | [No Free Transport] transport should not be free if price minus promo code discount is below the free transport limit | transport and payment page with non-free options after applying promocode | cartPage.cy.ts |
| 2-3-0 | [Brand Page Add] should add product to cart from brand page | add to cart popup | productAddToCart.cy.ts |
| 2-3-1 | [Product Detail Add] should add product to cart from product detail | add to cart popup | productAddToCart.cy.ts |
| 2-3-2 | [Category Page Add] should add product to cart from category page | add to cart popup | productAddToCart.cy.ts |
| 2-3-3 | [Product Variant Add] should add variant product to cart from product detail | add to cart popup | productAddToCart.cy.ts |
| 2-3-4 | [Promoted Products Add] should add product to cart from promoted products on homepage | add to cart popup | productAddToCart.cy.ts |
| 2-3-5 | [Search Page Add] should add product to cart from search results page | add to cart popup | productAddToCart.cy.ts |

## Snapshot Group - COMPARISON

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 11-0-0 | [Empty Comparison] should show empty comparison page | empty comparison | productComparison.cy.ts |
| 11-0-1 | [Add From Listing] should add a product to comparison from category listing and navigate to comparison page | comparison with one product from listing | productComparison.cy.ts |
| 11-0-2 | [Add And Navigate] should add products from listing and detail, then navigate to comparison page | comparison with two products | productComparison.cy.ts |
| 11-0-3 | [Remove Single Product] should add two products, remove one, and verify the other remains | comparison after removing one product | productComparison.cy.ts |

## Snapshot Group - COMPLAINTS

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 7-0-0 | should display the complaints list page | complaints-list | complaints.cy.ts |
| 7-0-1 | should navigate to the new complaint page | new-complaint-page | complaints.cy.ts |
| 7-0-2 | should allow Accountant to view complaints list | complaints-list-accountant | complaints.cy.ts |

## Snapshot Group - CUSTOMER_USERS

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 6-0-0 | should display the customer users table | customer-users-table | customerUsers.cy.ts |
| 6-0-1 | should open add user popup and add a new user | add-user-popup | customerUsers.cy.ts |
| 6-0-2 | should open edit user popup and edit a user | edit-user-popup | customerUsers.cy.ts |
| 6-0-3 | should open delete user popup and delete a user | delete-user-popup | customerUsers.cy.ts |

## Snapshot Group - FILTER

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 12-1-0 | [Parameter Checkbox Filter] should filter products by parameter checkbox and verify URL persistence | parameter checkbox filter applied | parameterFilter.cy.ts |
| 12-1-1 | [Multiple Parameter Filters] should combine multiple parameter filters and verify product count changes | multiple parameter filters | parameterFilter.cy.ts |
| 12-1-2 | [Sort With Parameter Filter] should change sort order while parameter filter is active | sorted with parameter filter | parameterFilter.cy.ts |
| 12-1-3 | [Clear Parameter Filters] should clear all parameter filters and restore product listing | filters cleared | parameterFilter.cy.ts |

## Snapshot Group - FREE_SHIPPING

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 15-0-0 | [Free Shipping Banner] should show free shipping progress bar in cart and reach free threshold with increased quantity | cart with free shipping remaining | freeShipping.cy.ts |
| 15-0-1 | [Free Shipping Banner] should show free shipping progress bar in cart and reach free threshold with increased quantity | cart with free shipping reached | freeShipping.cy.ts |
| 15-0-2 | [Free Shipping Transport Step] should show free transport options on step 2 when threshold is exceeded | transport step with free shipping | freeShipping.cy.ts |

## Snapshot Group - GIFT

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 16-0-0 | [Product Detail & Cart] should display gift on product detail, add-to-cart popup, and cart page | product detail with gift | giftWithProduct.cy.ts |
| 16-0-1 | [Product Detail & Cart] should display gift on product detail, add-to-cart popup, and cart page | add to cart popup with gift | giftWithProduct.cy.ts |
| 16-0-2 | [Product Detail & Cart] should display gift on product detail, add-to-cart popup, and cart page | cart page with gift | giftWithProduct.cy.ts |
| 16-0-3 | [Checkout Flow] should show gift in order summary, order confirmation, and order detail | contact information with gift in order summary | giftWithProduct.cy.ts |
| 16-0-4 | [Checkout Flow] should show gift in order summary, order confirmation, and order detail | order confirmation with gift | giftWithProduct.cy.ts |
| 16-0-5 | [Checkout Flow] should show gift in order summary, order confirmation, and order detail | order detail with gift | giftWithProduct.cy.ts |

## Snapshot Group - LIMITED_USER

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 8-0-0 | should display product prices on the category page | category-with-prices-owner | limitedUser.cy.ts |
| 8-0-1 | should not display product prices on the category page | category-no-prices-limited | limitedUser.cy.ts |
| 8-0-2 | should not display product prices on the category page | category-no-prices-catalog | limitedUser.cy.ts |

## Snapshot Group - MATRIX

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 0-0-0 | [Matrix] should visit matrix page with screenshot | matrix page | matrixTest.cy.ts |

## Snapshot Group - ORDER

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 3-0-0 | [Anon Transport & Payment] should redirect to transport and payment select page and not display contact information form if transport and payment are not selected and user is not logged in | transport and payment page | contactInformation.cy.ts |
| 3-0-1 | [Anon Transport & Payment] should redirect to transport and payment select page and not display contact information form if transport and payment are not selected and user is not logged in | transport and payment page | contactInformation.cy.ts |
| 3-0-2 | [Preserve Contact Form] should keep filled contact information after page refresh | contact information page after reload | contactInformation.cy.ts |
| 3-0-3 | [Preserve Contact Form] should keep filled contact information after page refresh | contact information page after reload | contactInformation.cy.ts |
| 3-0-4 | [Logout Clear Form] should remove contact information after logout | filled contact information form before logout | contactInformation.cy.ts |
| 3-0-5 | [Logout Clear Form] should remove contact information after logout | empty contact information form after logout | contactInformation.cy.ts |
| 3-1-0 | [Anon Registered Home Cash] should create order as unlogged user with a registered email (transport to home, cash on delivery) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-1 | [Anon Home Cash] should create order as unlogged user (transport to home, cash on delivery) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-2 | [Anon Collect Cash] should create order as unlogged user (personal collection, cash) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-3 | [Anon PPL Card] should create order as unlogged user (PPL, credit card) and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-4 | [Anon Promo Code] should create order with promo code, verify promo code summary visibility on steps 2 and 3, and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-5 | [Anon Promo Code] should create order with promo code, verify promo code summary visibility on steps 2 and 3, and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-1-6 | [Anon Promo Code] should create order with promo code, verify promo code summary visibility on steps 2 and 3, and check it in order detail | customer edit page | createOrder.cy.ts |
| 3-1-7 | [Anon Promo Code] should create order with promo code, verify promo code summary visibility on steps 2 and 3, and check it in order detail | filled contact information form | createOrder.cy.ts |
| 3-2-0 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-1 | [Preserve Form On Refresh] should keep filled delivery address after page refresh | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-2 | [Preserve Form On Checkbox Change] should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-3 | [Preserve Form On Checkbox Change] should keep filled delivery address after unchecking the checkbox for different delivery address and then checking it again | contact information form after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-2-4 | [Logged Popup Add Address] should add delivery address via popup for logged-in user and take snapshot after saving | contact information with new delivery address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-5 | [Logged Default Fill New] should first select saved default delivery address for logged-in user, but then fill and keep new delivery address after refresh | with default address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-6 | [Logged Default Fill New] should first select saved default delivery address for logged-in user, but then fill and keep new delivery address after refresh | changed contact information after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-7 | [Logged Default Fill New Default] should first select saved default delivery address for logged-in user, then fill new delivery address, then change it to a saved one and back to the new address which should stay filled | with default address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-8 | [Logged Default Fill New Default] should first select saved default delivery address for logged-in user, then fill new delivery address, then change it to a saved one and back to the new address which should stay filled | with changed delivery address | createOrderWithDeliveryAddress.cy.ts |
| 3-2-9 | [Logged Default Fill New Default] should first select saved default delivery address for logged-in user, then fill new delivery address, then change it to a saved one and back to the new address which should stay filled | with changed delivery address after switching back from default | createOrderWithDeliveryAddress.cy.ts |
| 3-2-10 | [Preserve Pickup On Refresh] should prefill delivery address from selected pickup point and keep delivery contact after refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-11 | [Preserve Pickup On Refresh] should prefill delivery address from selected pickup point and keep delivery contact after refresh | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-12 | [Preserve Pickup On Checkbox Change] should prefill delivery address from selected pickup point and keep delivery contact after unchecking the checkbox for different delivery contact and then checking it again | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-13 | [Preserve Pickup On Checkbox Change] should prefill delivery address from selected pickup point and keep delivery contact after unchecking the checkbox for different delivery contact and then checking it again | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-2-14 | [Logged No Prefill On Pickup Preserve On Refresh] should not prefill delivery contact for logged-in user with saved address and with selected pickup point, and then keep the filled delivery information after refresh | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-15 | [Logged No Prefill On Pickup Preserve On Refresh] should not prefill delivery contact for logged-in user with saved address and with selected pickup point, and then keep the filled delivery information after refresh | contact information form after refresh | createOrderWithDeliveryAddress.cy.ts |
| 3-2-16 | [Logged No Prefill On Pickup Preserve On Checkbox Change] should not prefill delivery contact for logged-in user with saved address and pickup point, but keep filled delivery information after unchecking and checking checkbox for different delivery address | contact information form before filling | createOrderWithDeliveryAddress.cy.ts |
| 3-2-17 | [Logged No Prefill On Pickup Preserve On Checkbox Change] should not prefill delivery contact for logged-in user with saved address and pickup point, but keep filled delivery information after unchecking and checking checkbox for different delivery address | after checking again | createOrderWithDeliveryAddress.cy.ts |
| 3-3-0 | [Logged Repeat With Empty] should repeat order (pre-fill cart) for logged-in user with initially empty cart | after repeat | orderRepeat.cy.ts |
| 3-3-1 | [Logged Repeat With Prefilled And Merge] should repeat order (pre-fill cart) for logged-in user with initially filled cart and allowed merging | after repeat | orderRepeat.cy.ts |
| 3-3-2 | [Logged Repeat With Prefilled And No Merge] should repeat order (pre-fill cart) for logged-in user with initially filled cart and disallowed merging | after repeat | orderRepeat.cy.ts |
| 3-3-3 | [Anon Repeat With Empty] should repeat order (pre-fill cart) for unlogged user with initially empty cart | after repeat | orderRepeat.cy.ts |
| 3-3-4 | [Anon Repeat With Prefilled Merge] should repeat order (pre-fill cart) for unlogged user with initially filled cart and allowed merging | after repeat | orderRepeat.cy.ts |
| 3-3-5 | [Anon Repeat With Prefilled No Merge] should repeat order (pre-fill cart) for unlogged user with initially filled cart and disallowed merging | after repeat | orderRepeat.cy.ts |

## Snapshot Group - SEO_CATEGORY

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 14-0-0 | [SEO Category Navigation] should click an SEO category link and verify URL changes with filtered products | seo category detail page | seoCategory.cy.ts |

## Snapshot Group - STORES

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 13-0-0 | [Stores To Store Detail] should navigate from homepage to stores, then to store detail | stores page | stores.cy.ts |
| 13-0-1 | [Stores To Store Detail] should navigate from homepage to stores, then to store detail | store detail page | stores.cy.ts |

## Snapshot Group - TRANSPORT_AND_PAYMENT

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 4-0-0 | [Preselect T&P] should preselect transport and payment from last order for logged-in user | preselected transport and payment | lastOrderTransportAndPayment.cy.ts |
| 4-0-1 | [Change T&P And Preserve On Refresh] should change preselected transport and payment from last order for logged-in user and keep the new selection after refresh | after first change and refresh | lastOrderTransportAndPayment.cy.ts |
| 4-0-2 | [Change T&P And Preserve On Refresh] should change preselected transport and payment from last order for logged-in user and keep the new selection after refresh | after second change and refresh | lastOrderTransportAndPayment.cy.ts |
| 4-1-0 | [Select Payment] should select payment on delivery | after payment selection | paymentSelect.cy.ts |
| 4-1-1 | [Select And Change Payment] should select a payment, deselect it, and then change the payment option | after changing payment selection | paymentSelect.cy.ts |
| 4-1-2 | [Remove Payment Repeated Click] should remove payment using repeated clicks | after selecting | paymentSelect.cy.ts |
| 4-1-3 | [Remove Payment Repeated Click] should remove payment using repeated clicks | after removing | paymentSelect.cy.ts |
| 4-1-4 | [Remove Payment Button Click] should remove payment using reset button | after selecting | paymentSelect.cy.ts |
| 4-1-5 | [Remove Payment Button Click] should remove payment using reset button | after removing | paymentSelect.cy.ts |
| 4-1-6 | [Remove & Select New T&P] should remove transport to remove payment as well, and then allow to select transport incompatible with previous payment | after selecting | paymentSelect.cy.ts |
| 4-1-7 | [Remove & Select New T&P] should remove transport to remove payment as well, and then allow to select transport incompatible with previous payment | after removing transport | paymentSelect.cy.ts |
| 4-1-8 | [Remove & Select New T&P] should remove transport to remove payment as well, and then allow to select transport incompatible with previous payment | after selecting transport incompatible with the previous payment | paymentSelect.cy.ts |
| 4-2-0 | [Transport Home] should select transport to home | after selecting | transportSelect.cy.ts |
| 4-2-1 | [Personal Collection] should select personal pickup transport | after selecting | transportSelect.cy.ts |
| 4-2-2 | [Change Transport] should select a transport, deselect it, and then change the transport option | after selecting, deselecting, and selecting again | transportSelect.cy.ts |
| 4-2-3 | [Remove Transport Repeated Click] should be able to remove transport using repeated clicks | after selecting | transportSelect.cy.ts |
| 4-2-4 | [Remove Transport Repeated Click] should be able to remove transport using repeated clicks | after removing | transportSelect.cy.ts |
| 4-2-5 | [Remove Transport Button Click] should remove transport using reset button | after selecting | transportSelect.cy.ts |
| 4-2-6 | [Remove Transport Button Click] should remove transport using reset button | after removing | transportSelect.cy.ts |
| 4-2-7 | [Transport Fee] should change price for transport when cart is large enough for transport to be free | transport and payment page with too few products | transportSelect.cy.ts |
| 4-2-8 | [Transport Fee] should change price for transport when cart is large enough for transport to be free | cart page with enough products | transportSelect.cy.ts |
| 4-2-9 | [Transport Fee] should change price for transport when cart is large enough for transport to be free | transport and payment page with enough products | transportSelect.cy.ts |

## Snapshot Group - VISITS

| Snapshot Id | Test Name | Snapshot Detail | File |
|-------------|-----------|-----------------|------|
| 5-0-0 | [Homepage] should visit homepage with screenshot | homepage | simpleVisitsWithScreenshots.cy.ts |
| 5-0-1 | [Product Detail] should visit product detail with screenshot | product detail | simpleVisitsWithScreenshots.cy.ts |
| 5-0-2 | [Category Detail] should visit category detail with screenshot | category detail | simpleVisitsWithScreenshots.cy.ts |
| 5-0-3 | [Blog Detail] should visit blog article detail with screenshot | blog article detail | simpleVisitsWithScreenshots.cy.ts |
