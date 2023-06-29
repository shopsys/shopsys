# Schema Types

<details>
  <summary><strong>Table of Contents</strong></summary>

  * [Query](#query)
  * [Mutation](#mutation)
  * [Objects](#objects)
    * [AddProductResult](#addproductresult)
    * [AddToCartResult](#addtocartresult)
    * [AdditionalSize](#additionalsize)
    * [AdvertCode](#advertcode)
    * [AdvertImage](#advertimage)
    * [AdvertPosition](#advertposition)
    * [ArticleConnection](#articleconnection)
    * [ArticleEdge](#articleedge)
    * [ArticleLink](#articlelink)
    * [ArticleSite](#articlesite)
    * [Availability](#availability)
    * [BlogArticle](#blogarticle)
    * [BlogArticleConnection](#blogarticleconnection)
    * [BlogArticleEdge](#blogarticleedge)
    * [BlogCategory](#blogcategory)
    * [Brand](#brand)
    * [BrandFilterOption](#brandfilteroption)
    * [Cart](#cart)
    * [CartItem](#cartitem)
    * [CartItemModificationsResult](#cartitemmodificationsresult)
    * [CartModificationsResult](#cartmodificationsresult)
    * [CartPaymentModificationsResult](#cartpaymentmodificationsresult)
    * [CartPromoCodeModificationsResult](#cartpromocodemodificationsresult)
    * [CartTransportModificationsResult](#carttransportmodificationsresult)
    * [Category](#category)
    * [CategoryConnection](#categoryconnection)
    * [CategoryEdge](#categoryedge)
    * [CategoryHierarchyItem](#categoryhierarchyitem)
    * [CompanyCustomerUser](#companycustomeruser)
    * [Comparison](#comparison)
    * [Country](#country)
    * [CreateOrderResult](#createorderresult)
    * [DeliveryAddress](#deliveryaddress)
    * [File](#file)
    * [Flag](#flag)
    * [FlagFilterOption](#flagfilteroption)
    * [GoPayBankSwift](#gopaybankswift)
    * [GoPayCreatePaymentSetup](#gopaycreatepaymentsetup)
    * [GoPayPaymentMethod](#gopaypaymentmethod)
    * [Image](#image)
    * [ImageSize](#imagesize)
    * [LanguageConstant](#languageconstant)
    * [Link](#link)
    * [LoginResult](#loginresult)
    * [MainVariant](#mainvariant)
    * [NavigationItem](#navigationitem)
    * [NavigationItemCategoriesByColumns](#navigationitemcategoriesbycolumns)
    * [NewsletterSubscriber](#newslettersubscriber)
    * [NotificationBar](#notificationbar)
    * [Order](#order)
    * [OrderConnection](#orderconnection)
    * [OrderEdge](#orderedge)
    * [OrderItem](#orderitem)
    * [PageInfo](#pageinfo)
    * [Parameter](#parameter)
    * [ParameterCheckboxFilterOption](#parametercheckboxfilteroption)
    * [ParameterColorFilterOption](#parametercolorfilteroption)
    * [ParameterSliderFilterOption](#parametersliderfilteroption)
    * [ParameterValue](#parametervalue)
    * [ParameterValueColorFilterOption](#parametervaluecolorfilteroption)
    * [ParameterValueFilterOption](#parametervaluefilteroption)
    * [Payment](#payment)
    * [PaymentSetupCreationData](#paymentsetupcreationdata)
    * [PersonalData](#personaldata)
    * [PersonalDataPage](#personaldatapage)
    * [Price](#price)
    * [PricingSetting](#pricingsetting)
    * [ProductConnection](#productconnection)
    * [ProductEdge](#productedge)
    * [ProductFilterOptions](#productfilteroptions)
    * [ProductPrice](#productprice)
    * [RegularCustomerUser](#regularcustomeruser)
    * [RegularProduct](#regularproduct)
    * [SeoPage](#seopage)
    * [SeoSetting](#seosetting)
    * [Settings](#settings)
    * [SliderItem](#slideritem)
    * [Store](#store)
    * [StoreAvailability](#storeavailability)
    * [StoreConnection](#storeconnection)
    * [StoreEdge](#storeedge)
    * [Token](#token)
    * [Transport](#transport)
    * [TransportType](#transporttype)
    * [Unit](#unit)
    * [Variant](#variant)
    * [VideoToken](#videotoken)
  * [Inputs](#inputs)
    * [AddToCartInput](#addtocartinput)
    * [ApplyPromoCodeToCartInput](#applypromocodetocartinput)
    * [CartInput](#cartinput)
    * [ChangePasswordInput](#changepasswordinput)
    * [ChangePaymentInCartInput](#changepaymentincartinput)
    * [ChangePersonalDataInput](#changepersonaldatainput)
    * [ChangeTransportInCartInput](#changetransportincartinput)
    * [ContactInput](#contactinput)
    * [DeliveryAddressInput](#deliveryaddressinput)
    * [LoginInput](#logininput)
    * [NewsletterSubscriptionDataInput](#newslettersubscriptiondatainput)
    * [OrderInput](#orderinput)
    * [OrderProductInput](#orderproductinput)
    * [ParameterFilter](#parameterfilter)
    * [PaymentInput](#paymentinput)
    * [PersonalDataAccessRequestInput](#personaldataaccessrequestinput)
    * [PriceInput](#priceinput)
    * [ProductFilter](#productfilter)
    * [RecoverPasswordInput](#recoverpasswordinput)
    * [RefreshTokenInput](#refreshtokeninput)
    * [RegistrationDataInput](#registrationdatainput)
    * [RemoveFromCartInput](#removefromcartinput)
    * [RemovePromoCodeFromCartInput](#removepromocodefromcartinput)
    * [TransportInput](#transportinput)
  * [Enums](#enums)
    * [ArticlePlacementTypeEnum](#articleplacementtypeenum)
    * [AvailabilityStatusEnum](#availabilitystatusenum)
    * [PersonalDataAccessRequestTypeEnum](#personaldataaccessrequesttypeenum)
    * [ProductOrderingModeEnum](#productorderingmodeenum)
  * [Scalars](#scalars)
    * [Boolean](#boolean)
    * [DateTime](#datetime)
    * [Float](#float)
    * [Int](#int)
    * [Money](#money)
    * [Password](#password)
    * [String](#string)
    * [Uuid](#uuid)
  * [Interfaces](#interfaces)
    * [Advert](#advert)
    * [ArticleInterface](#articleinterface)
    * [Breadcrumb](#breadcrumb)
    * [CartInterface](#cartinterface)
    * [CustomerUser](#customeruser)
    * [NotBlogArticleInterface](#notblogarticleinterface)
    * [ParameterFilterOptionInterface](#parameterfilteroptioninterface)
    * [PriceInterface](#priceinterface)
    * [Product](#product)
    * [ProductListable](#productlistable)
    * [Slug](#slug)

</details>

## Query
<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>accessPersonalData</strong></td>
<td valign="top"><a href="#personaldata">PersonalData</a>!</td>
<td>

Access personal data using hash received in email from personal data access request

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">hash</td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Hash to securely recognize access

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>AdvertCode</strong></td>
<td valign="top"><a href="#advertcode">AdvertCode</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>AdvertImage</strong></td>
<td valign="top"><a href="#advertimage">AdvertImage</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>advertPositions</strong></td>
<td valign="top">[<a href="#advertposition">AdvertPosition</a>!]!</td>
<td>

Returns list of advert positions.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>adverts</strong></td>
<td valign="top">[<a href="#advert">Advert</a>!]!</td>
<td>

Returns list of adverts, optionally filtered by `positionName`

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">categoryUuid</td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">positionName</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>article</strong></td>
<td valign="top"><a href="#notblogarticleinterface">NotBlogArticleInterface</a></td>
<td>

Returns article filtered using UUID or URL slug

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">urlSlug</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">uuid</td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>ArticleLink</strong></td>
<td valign="top"><a href="#articlelink">ArticleLink</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>articles</strong></td>
<td valign="top"><a href="#articleconnection">ArticleConnection</a>!</td>
<td>

Returns list of articles that can be paginated using `first`, `last`, `before`
and `after` keywords and filtered by `placement`

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">after</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">before</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">first</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">last</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">placement</td>
<td valign="top">[<a href="#articleplacementtypeenum">ArticlePlacementTypeEnum</a>!]</td>
<td>

An array of the required articles placements

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>ArticleSite</strong></td>
<td valign="top"><a href="#articlesite">ArticleSite</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>articlesSearch</strong></td>
<td valign="top">[<a href="#articleinterface">ArticleInterface</a>!]!</td>
<td>

Returns list of searched articles and blog articles

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">search</td>
<td valign="top"><a href="#string">String</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>blogArticle</strong></td>
<td valign="top"><a href="#blogarticle">BlogArticle</a></td>
<td>

Returns blog article filtered using UUID or URL slug

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">urlSlug</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">uuid</td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>blogArticles</strong></td>
<td valign="top"><a href="#blogarticleconnection">BlogArticleConnection</a>!</td>
<td>

Returns a list of the blog articles that can be paginated using `first`, `last`, `before` and `after` keywords

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">after</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">before</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">first</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">last</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">onlyHomepageArticles</td>
<td valign="top"><a href="#boolean">Boolean</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>blogCategories</strong></td>
<td valign="top">[<a href="#blogcategory">BlogCategory</a>!]!</td>
<td>

Returns a complete list of the blog categories

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>blogCategory</strong></td>
<td valign="top"><a href="#blogcategory">BlogCategory</a></td>
<td>

Returns blog category filtered using UUID or URL slug

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">urlSlug</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">uuid</td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>brand</strong></td>
<td valign="top"><a href="#brand">Brand</a></td>
<td>

Returns brand filtered using UUID or URL slug

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">urlSlug</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">uuid</td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>brands</strong></td>
<td valign="top">[<a href="#brand">Brand</a>!]!</td>
<td>

Returns complete list of brands

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>brandSearch</strong></td>
<td valign="top">[<a href="#brand">Brand</a>!]!</td>
<td>

Returns list of searched brands

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">search</td>
<td valign="top"><a href="#string">String</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>cart</strong></td>
<td valign="top"><a href="#cart">Cart</a></td>
<td>

Return cart of logged customer or cart by UUID for anonymous user

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">cartInput</td>
<td valign="top"><a href="#cartinput">CartInput</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>categories</strong></td>
<td valign="top">[<a href="#category">Category</a>!]!</td>
<td>

Returns complete list of categories

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>categoriesSearch</strong></td>
<td valign="top"><a href="#categoryconnection">CategoryConnection</a>!</td>
<td>

Returns list of searched categories that can be paginated using `first`, `last`, `before` and `after` keywords

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">after</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">before</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">first</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">last</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">search</td>
<td valign="top"><a href="#string">String</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>category</strong></td>
<td valign="top"><a href="#category">Category</a></td>
<td>

Returns category filtered using UUID or URL slug

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">urlSlug</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">uuid</td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>CompanyCustomerUser</strong></td>
<td valign="top"><a href="#companycustomeruser">CompanyCustomerUser</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>comparison</strong></td>
<td valign="top"><a href="#comparison">Comparison</a></td>
<td>

Get comparison by UUID or comparison of logged customer user.

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">uuid</td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>cookiesArticle</strong></td>
<td valign="top"><a href="#articlesite">ArticleSite</a></td>
<td>

Returns information about cookies article

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>countries</strong></td>
<td valign="top">[<a href="#country">Country</a>!]!</td>
<td>

Returns available countries

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>currentCustomerUser</strong></td>
<td valign="top"><a href="#customeruser">CustomerUser</a></td>
<td>

Returns currently logged in customer user

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>flag</strong></td>
<td valign="top"><a href="#flag">Flag</a></td>
<td>

Returns a flag by uuid or url slug

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">urlSlug</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">uuid</td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>flags</strong></td>
<td valign="top">[<a href="#flag">Flag</a>!]</td>
<td>

Returns a complete list of the flags

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>GoPaySwifts</strong></td>
<td valign="top">[<a href="#gopaybankswift">GoPayBankSwift</a>!]!</td>
<td>

List of available banks for GoPay bank transfer payment

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">currencyCode</td>
<td valign="top"><a href="#string">String</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isCustomerUserRegistered</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Check if email is registered

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">email</td>
<td valign="top"><a href="#string">String</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>languageConstants</strong></td>
<td valign="top">[<a href="#languageconstant">LanguageConstant</a>!]!</td>
<td>

Return user translated language constants for current domain locale

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>lastOrder</strong></td>
<td valign="top"><a href="#order">Order</a></td>
<td>

Returns last order of the user or null if no order was placed yet

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>MainVariant</strong></td>
<td valign="top"><a href="#mainvariant">MainVariant</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>navigation</strong></td>
<td valign="top">[<a href="#navigationitem">NavigationItem</a>!]!</td>
<td>

Returns complete navigation menu

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>notificationBars</strong></td>
<td valign="top">[<a href="#notificationbar">NotificationBar</a>!]</td>
<td>

Returns a list of notifications supposed to be displayed on all pages

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>order</strong></td>
<td valign="top"><a href="#order">Order</a></td>
<td>

Returns order filtered using UUID, orderNumber, or urlHash

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">orderNumber</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">urlHash</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">uuid</td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>orders</strong></td>
<td valign="top"><a href="#orderconnection">OrderConnection</a></td>
<td>

Returns list of orders that can be paginated using `first`, `last`, `before` and `after` keywords

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">after</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">before</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">first</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">last</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>orderSentPageContent</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Returns HTML content for order sent page.

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">orderUuid</td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>ParameterCheckboxFilterOption</strong></td>
<td valign="top"><a href="#parametercheckboxfilteroption">ParameterCheckboxFilterOption</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>ParameterColorFilterOption</strong></td>
<td valign="top"><a href="#parametercolorfilteroption">ParameterColorFilterOption</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>ParameterSliderFilterOption</strong></td>
<td valign="top"><a href="#parametersliderfilteroption">ParameterSliderFilterOption</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>payment</strong></td>
<td valign="top"><a href="#payment">Payment</a></td>
<td>

Returns payment filtered using UUID

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">uuid</td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>payments</strong></td>
<td valign="top">[<a href="#payment">Payment</a>!]!</td>
<td>

Returns complete list of payment methods

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>personalDataPage</strong></td>
<td valign="top"><a href="#personaldatapage">PersonalDataPage</a></td>
<td>

Return personal data page content and URL

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>privacyPolicyArticle</strong></td>
<td valign="top"><a href="#articlesite">ArticleSite</a></td>
<td>

Returns privacy policy article

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>product</strong></td>
<td valign="top"><a href="#product">Product</a></td>
<td>

Returns product filtered using UUID or URL slug

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">urlSlug</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">uuid</td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>products</strong></td>
<td valign="top"><a href="#productconnection">ProductConnection</a>!</td>
<td>

Returns list of ordered products that can be paginated using `first`, `last`, `before` and `after` keywords

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">after</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">before</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">filter</td>
<td valign="top"><a href="#productfilter">ProductFilter</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">first</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">last</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">orderingMode</td>
<td valign="top"><a href="#productorderingmodeenum">ProductOrderingModeEnum</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">search</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>productsByCatnums</strong></td>
<td valign="top">[<a href="#product">Product</a>!]!</td>
<td>

Returns list of products by catalog numbers

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">catnums</td>
<td valign="top">[<a href="#string">String</a>!]!</td>
<td>

Array of product catalog numbers

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>promotedCategories</strong></td>
<td valign="top">[<a href="#category">Category</a>!]!</td>
<td>

Returns promoted categories

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>promotedProducts</strong></td>
<td valign="top">[<a href="#product">Product</a>!]!</td>
<td>

Returns promoted products

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>RegularCustomerUser</strong></td>
<td valign="top"><a href="#regularcustomeruser">RegularCustomerUser</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>RegularProduct</strong></td>
<td valign="top"><a href="#regularproduct">RegularProduct</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoPage</strong></td>
<td valign="top"><a href="#seopage">SeoPage</a></td>
<td>

Returns SEO settings for a specific page based on the url slug of that page

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">pageSlug</td>
<td valign="top"><a href="#string">String</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>settings</strong></td>
<td valign="top"><a href="#settings">Settings</a></td>
<td>

Returns current setting

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>sliderItems</strong></td>
<td valign="top">[<a href="#slideritem">SliderItem</a>!]!</td>
<td>

Returns a complete list of the slider items

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>slug</strong></td>
<td valign="top"><a href="#slug">Slug</a></td>
<td>

Returns entity by slug

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">slug</td>
<td valign="top"><a href="#string">String</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>store</strong></td>
<td valign="top"><a href="#store">Store</a></td>
<td>

Returns store filtered using UUID or URL slug

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">urlSlug</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">uuid</td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>stores</strong></td>
<td valign="top"><a href="#storeconnection">StoreConnection</a>!</td>
<td>

Returns list of stores that can be paginated using `first`, `last`, `before` and `after` keywords

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">after</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">before</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">first</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">last</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>termsAndConditionsArticle</strong></td>
<td valign="top"><a href="#articlesite">ArticleSite</a></td>
<td>

Returns Terms and Conditions article

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>transport</strong></td>
<td valign="top"><a href="#transport">Transport</a></td>
<td>

Returns complete list of transport methods

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">uuid</td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>transports</strong></td>
<td valign="top">[<a href="#transport">Transport</a>!]!</td>
<td>

Returns available transport methods based on the current cart state

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">cartUuid</td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>Variant</strong></td>
<td valign="top"><a href="#variant">Variant</a></td>
<td></td>
</tr>
</tbody>
</table>

## Mutation
<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>addProductToComparison</strong></td>
<td valign="top"><a href="#comparison">Comparison</a>!</td>
<td>

Add product to Comparison and create if not exists.

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">comparisonUuid</td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">productUuid</td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>AddToCart</strong></td>
<td valign="top"><a href="#addtocartresult">AddToCartResult</a>!</td>
<td>

Add product to cart for future checkout

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">input</td>
<td valign="top"><a href="#addtocartinput">AddToCartInput</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>ApplyPromoCodeToCart</strong></td>
<td valign="top"><a href="#cart">Cart</a>!</td>
<td>

Apply new promo code for the future checkout

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">input</td>
<td valign="top"><a href="#applypromocodetocartinput">ApplyPromoCodeToCartInput</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>ChangePassword</strong></td>
<td valign="top"><a href="#customeruser">CustomerUser</a>!</td>
<td>

Changes customer user password

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">input</td>
<td valign="top"><a href="#changepasswordinput">ChangePasswordInput</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>ChangePaymentInCart</strong></td>
<td valign="top"><a href="#cart">Cart</a>!</td>
<td>

Add a payment to the cart, or remove a payment from the cart

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">input</td>
<td valign="top"><a href="#changepaymentincartinput">ChangePaymentInCartInput</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>ChangePersonalData</strong></td>
<td valign="top"><a href="#customeruser">CustomerUser</a>!</td>
<td>

Changes customer user personal data

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">input</td>
<td valign="top"><a href="#changepersonaldatainput">ChangePersonalDataInput</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>ChangeTransportInCart</strong></td>
<td valign="top"><a href="#cart">Cart</a>!</td>
<td>

Add a transport to the cart, or remove a transport from the cart

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">input</td>
<td valign="top"><a href="#changetransportincartinput">ChangeTransportInCartInput</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>CheckPaymentStatus</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

check payment status of order after callback from payment service

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">orderUuid</td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>cleanComparison</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Remove all products from Comparison and remove it.

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">comparisonUuid</td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>Contact</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Send message to the site owner

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">input</td>
<td valign="top"><a href="#contactinput">ContactInput</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>CreateOrder</strong></td>
<td valign="top"><a href="#createorderresult">CreateOrderResult</a>!</td>
<td>

Creates complete order with products and addresses

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">input</td>
<td valign="top"><a href="#orderinput">OrderInput</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>DeleteDeliveryAddress</strong></td>
<td valign="top">[<a href="#deliveryaddress">DeliveryAddress</a>!]!</td>
<td>

Delete delivery address by Uuid

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">deliveryAddressUuid</td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>EditDeliveryAddress</strong></td>
<td valign="top">[<a href="#deliveryaddress">DeliveryAddress</a>!]!</td>
<td>

Edit delivery address by Uuid

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">input</td>
<td valign="top"><a href="#deliveryaddressinput">DeliveryAddressInput</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>Login</strong></td>
<td valign="top"><a href="#loginresult">LoginResult</a>!</td>
<td>

Login customer user

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">input</td>
<td valign="top"><a href="#logininput">LoginInput</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>Logout</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Logout user

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>NewsletterSubscribe</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Subscribe for e-mail newsletter

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">input</td>
<td valign="top"><a href="#newslettersubscriptiondatainput">NewsletterSubscriptionDataInput</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>PayOrder</strong></td>
<td valign="top"><a href="#paymentsetupcreationdata">PaymentSetupCreationData</a>!</td>
<td>

Pay order(create payment transaction in payment gateway) and get payment setup
data for redirect or creating JS payment gateway layer

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">orderUuid</td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>RecoverPassword</strong></td>
<td valign="top"><a href="#loginresult">LoginResult</a>!</td>
<td>

Recover password using hash required from RequestPasswordRecovery

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">input</td>
<td valign="top"><a href="#recoverpasswordinput">RecoverPasswordInput</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>RefreshTokens</strong></td>
<td valign="top"><a href="#token">Token</a>!</td>
<td>

Refreshes access and refresh tokens

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">input</td>
<td valign="top"><a href="#refreshtokeninput">RefreshTokenInput</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>Register</strong></td>
<td valign="top"><a href="#loginresult">LoginResult</a>!</td>
<td>

Register new customer user

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">input</td>
<td valign="top"><a href="#registrationdatainput">RegistrationDataInput</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>RemoveFromCart</strong></td>
<td valign="top"><a href="#cart">Cart</a>!</td>
<td>

Remove product from cart

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">input</td>
<td valign="top"><a href="#removefromcartinput">RemoveFromCartInput</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>removeProductFromComparison</strong></td>
<td valign="top"><a href="#comparison">Comparison</a></td>
<td>

Remove product from Comparison and if is Comparison empty remove it.

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">comparisonUuid</td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">productUuid</td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>RemovePromoCodeFromCart</strong></td>
<td valign="top"><a href="#cart">Cart</a>!</td>
<td>

Remove already used promo code from cart

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">input</td>
<td valign="top"><a href="#removepromocodefromcartinput">RemovePromoCodeFromCartInput</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>RequestPasswordRecovery</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Request password recovery - email with hash will be sent

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">email</td>
<td valign="top"><a href="#string">String</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>RequestPersonalDataAccess</strong></td>
<td valign="top"><a href="#personaldatapage">PersonalDataPage</a>!</td>
<td>

Request access to personal data

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">input</td>
<td valign="top"><a href="#personaldataaccessrequestinput">PersonalDataAccessRequestInput</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>SetDefaultDeliveryAddress</strong></td>
<td valign="top"><a href="#customeruser">CustomerUser</a>!</td>
<td>

Set default delivery address by Uuid

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">deliveryAddressUuid</td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

Set delivery address by Uuid

</td>
</tr>
</tbody>
</table>

## Objects

### AddProductResult

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>addedQuantity</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>cartItem</strong></td>
<td valign="top"><a href="#cartitem">CartItem</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isNew</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>notOnStockQuantity</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td></td>
</tr>
</tbody>
</table>

### AddToCartResult

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>addProductResult</strong></td>
<td valign="top"><a href="#addproductresult">AddProductResult</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>cart</strong></td>
<td valign="top"><a href="#cart">Cart</a>!</td>
<td></td>
</tr>
</tbody>
</table>

### AdditionalSize

Represents a singe additional image size

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>height</strong></td>
<td valign="top"><a href="#int">Int</a></td>
<td>

Height in pixels defined in images.yaml

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>media</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Recommended media query defined in images.yaml

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>url</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

URL address of image

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>width</strong></td>
<td valign="top"><a href="#int">Int</a></td>
<td>

Width in pixels defined in images.yaml

</td>
</tr>
</tbody>
</table>

### AdvertCode

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>categories</strong></td>
<td valign="top">[<a href="#category">Category</a>!]!</td>
<td>

Restricted categories of the advert (the advert is shown in these categories only)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>code</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Advert code

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Name of advert

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>positionName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Position of advert

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>type</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Type of advert

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### AdvertImage

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>categories</strong></td>
<td valign="top">[<a href="#category">Category</a>!]!</td>
<td>

Restricted categories of the advert (the advert is shown in these categories only)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>images</strong></td>
<td valign="top">[<a href="#image">Image</a>!]!</td>
<td>

Advert images

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">sizes</td>
<td valign="top">[<a href="#string">String</a>!]</td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>link</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Advert link

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>mainImage</strong></td>
<td valign="top"><a href="#image">Image</a></td>
<td>

Adverts first image by params

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Name of advert

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>positionName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Position of advert

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>type</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Type of advert

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### AdvertPosition

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>description</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Desription of advert position

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>positionName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Position of advert

</td>
</tr>
</tbody>
</table>

### ArticleConnection

A connection to a list of items.

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>edges</strong></td>
<td valign="top">[<a href="#articleedge">ArticleEdge</a>]</td>
<td>

Information to aid in pagination.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>pageInfo</strong></td>
<td valign="top"><a href="#pageinfo">PageInfo</a>!</td>
<td>

Information to aid in pagination.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>totalCount</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Total number of articles

</td>
</tr>
</tbody>
</table>

### ArticleEdge

An edge in a connection.

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>cursor</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

A cursor for use in pagination.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>node</strong></td>
<td valign="top"><a href="#notblogarticleinterface">NotBlogArticleInterface</a></td>
<td>

The item at the end of the edge.

</td>
</tr>
</tbody>
</table>

### ArticleLink

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>createdAt</strong></td>
<td valign="top"><a href="#datetime">DateTime</a>!</td>
<td>

Creation date time of the article link

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>external</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

If the the article should be open in a new tab

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Name of article link, used as anchor text

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>placement</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Placement of the article link

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>url</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Destination url of article link

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID of the article link

</td>
</tr>
</tbody>
</table>

### ArticleSite

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>breadcrumb</strong></td>
<td valign="top">[<a href="#link">Link</a>!]!</td>
<td>

Hierarchy of the current element in relation to the structure

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>createdAt</strong></td>
<td valign="top"><a href="#datetime">DateTime</a>!</td>
<td>

Date and time of the article creation

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>external</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

If the the article should be open in a new tab

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Name of article

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>placement</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Placement of article

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoH1</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Seo first level heading of article

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoMetaDescription</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Seo meta description of article

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoTitle</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Seo title of article

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>slug</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Article URL slug

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>text</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Text of article

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### Availability

Represents an availability

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Localized availability name (domain dependent)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>status</strong></td>
<td valign="top"><a href="#availabilitystatusenum">AvailabilityStatusEnum</a>!</td>
<td>

Availability status in a format suitable for usage in the code

</td>
</tr>
</tbody>
</table>

### BlogArticle

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>blogCategories</strong></td>
<td valign="top">[<a href="#blogcategory">BlogCategory</a>!]!</td>
<td>

The list of the blog article blog categories

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>breadcrumb</strong></td>
<td valign="top">[<a href="#link">Link</a>!]!</td>
<td>

Hierarchy of the current element in relation to the structure

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>createdAt</strong></td>
<td valign="top"><a href="#datetime">DateTime</a>!</td>
<td>

Date and time of the blog article creation

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>id</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

ID of category

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>images</strong></td>
<td valign="top">[<a href="#image">Image</a>!]!</td>
<td>

Blog article images

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">sizes</td>
<td valign="top">[<a href="#string">String</a>!]</td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>link</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The blog article absolute URL

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>mainImage</strong></td>
<td valign="top"><a href="#image">Image</a></td>
<td>

Blog article image by params

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The blog article title

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>perex</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The blog article perex

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>publishDate</strong></td>
<td valign="top"><a href="#datetime">DateTime</a>!</td>
<td>

Date and time of the blog article publishing

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoH1</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The blog article SEO H1 heading

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoMetaDescription</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The blog article SEO meta description

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoTitle</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The blog article SEO title

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>slug</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The blog article URL slug

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>text</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The blog article text

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

The blog article UUID

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>visibleOnHomepage</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Indicates whether the blog article is displayed on homepage

</td>
</tr>
</tbody>
</table>

### BlogArticleConnection

A connection to a list of items.

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>edges</strong></td>
<td valign="top">[<a href="#blogarticleedge">BlogArticleEdge</a>]</td>
<td>

Information to aid in pagination.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>pageInfo</strong></td>
<td valign="top"><a href="#pageinfo">PageInfo</a>!</td>
<td>

Information to aid in pagination.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>totalCount</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Total number of the blog articles

</td>
</tr>
</tbody>
</table>

### BlogArticleEdge

An edge in a connection.

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>cursor</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

A cursor for use in pagination.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>node</strong></td>
<td valign="top"><a href="#blogarticle">BlogArticle</a></td>
<td>

The item at the end of the edge.

</td>
</tr>
</tbody>
</table>

### BlogCategory

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>articlesTotalCount</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Total count of blog articles in this category

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>blogArticles</strong></td>
<td valign="top"><a href="#blogarticleconnection">BlogArticleConnection</a>!</td>
<td>

Paginated blog articles of the given blog category

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">after</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">before</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">first</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">last</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">onlyHomepageArticles</td>
<td valign="top"><a href="#boolean">Boolean</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>blogCategoriesTree</strong></td>
<td valign="top">[<a href="#blogcategory">BlogCategory</a>!]!</td>
<td>

Tho whole blog categories tree (used for blog navigation rendering)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>breadcrumb</strong></td>
<td valign="top">[<a href="#link">Link</a>!]!</td>
<td>

Hierarchy of the current element in relation to the structure

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>children</strong></td>
<td valign="top">[<a href="#blogcategory">BlogCategory</a>!]!</td>
<td>

The blog category children

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>description</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The blog category description

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>link</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The blog category absolute URL

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The blog category name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>parent</strong></td>
<td valign="top"><a href="#blogcategory">BlogCategory</a></td>
<td>

The blog category parent

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoH1</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The blog category SEO H1 heading

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoMetaDescription</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The blog category SEO meta description

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoTitle</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The blog category SEO title

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>slug</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The blog category URL slug

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

The blog category UUID

</td>
</tr>
</tbody>
</table>

### Brand

Represents a brand

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>breadcrumb</strong></td>
<td valign="top">[<a href="#link">Link</a>!]!</td>
<td>

Hierarchy of the current element in relation to the structure

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>description</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Brand description

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>id</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

ID of category

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>images</strong></td>
<td valign="top">[<a href="#image">Image</a>!]!</td>
<td>

Brand images

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">sizes</td>
<td valign="top">[<a href="#string">String</a>!]</td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>link</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Brand main URL

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>mainImage</strong></td>
<td valign="top"><a href="#image">Image</a></td>
<td>

Brand image by params

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Brand name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>products</strong></td>
<td valign="top"><a href="#productconnection">ProductConnection</a>!</td>
<td>

Paginated and ordered products of brand

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">after</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">before</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">filter</td>
<td valign="top"><a href="#productfilter">ProductFilter</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">first</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">last</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">orderingMode</td>
<td valign="top"><a href="#productorderingmodeenum">ProductOrderingModeEnum</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">search</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoH1</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Brand SEO H1

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoMetaDescription</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Brand SEO meta description

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoTitle</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Brand SEO title

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>slug</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Brand URL slug

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### BrandFilterOption

Brand filter option

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>brand</strong></td>
<td valign="top"><a href="#brand">Brand</a>!</td>
<td>

Brand

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>count</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Count of products that will be filtered if this filter option is applied.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isAbsolute</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

If true than count parameter is number of products that will be displayed if
this filter option is applied, if false count parameter is number of products
that will be added to current products result.

</td>
</tr>
</tbody>
</table>

### Cart

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>items</strong></td>
<td valign="top">[<a href="#cartitem">CartItem</a>!]!</td>
<td>

All items in the cart

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>modifications</strong></td>
<td valign="top"><a href="#cartmodificationsresult">CartModificationsResult</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>payment</strong></td>
<td valign="top"><a href="#payment">Payment</a></td>
<td>

Selected payment if payment provided

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>paymentGoPayBankSwift</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Selected bank swift code of goPay payment bank transfer

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>promoCode</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Applied promo code if provided

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>remainingAmountWithVatForFreeTransport</strong></td>
<td valign="top"><a href="#money">Money</a></td>
<td>

Remaining amount for free transport and payment; null = transport cannot be free

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>selectedPickupPlaceIdentifier</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Selected pickup place identifier if provided

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>totalDiscountPrice</strong></td>
<td valign="top"><a href="#price">Price</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>totalItemsPrice</strong></td>
<td valign="top"><a href="#price">Price</a>!</td>
<td>

Total items price (excluding transport and payment)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>totalPrice</strong></td>
<td valign="top"><a href="#price">Price</a>!</td>
<td>

Total price including transport and payment

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>totalPriceWithoutDiscountTransportAndPayment</strong></td>
<td valign="top"><a href="#price">Price</a>!</td>
<td>

Total price (exluding discount, transport and payment)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>transport</strong></td>
<td valign="top"><a href="#transport">Transport</a></td>
<td>

Selected transport if transport provided

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td>

UUID of the cart, null for authenticated user

</td>
</tr>
</tbody>
</table>

### CartItem

Represent one item in the cart

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>product</strong></td>
<td valign="top"><a href="#product">Product</a>!</td>
<td>

Product in the cart

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>quantity</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Quantity of items in the cart

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

Cart item UUID

</td>
</tr>
</tbody>
</table>

### CartItemModificationsResult

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>cartItemsWithChangedQuantity</strong></td>
<td valign="top">[<a href="#cartitem">CartItem</a>!]!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>cartItemsWithModifiedPrice</strong></td>
<td valign="top">[<a href="#cartitem">CartItem</a>!]!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>noLongerAvailableCartItemsDueToQuantity</strong></td>
<td valign="top">[<a href="#cartitem">CartItem</a>!]!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>noLongerListableCartItems</strong></td>
<td valign="top">[<a href="#cartitem">CartItem</a>!]!</td>
<td></td>
</tr>
</tbody>
</table>

### CartModificationsResult

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>itemModifications</strong></td>
<td valign="top"><a href="#cartitemmodificationsresult">CartItemModificationsResult</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>paymentModifications</strong></td>
<td valign="top"><a href="#cartpaymentmodificationsresult">CartPaymentModificationsResult</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>promoCodeModifications</strong></td>
<td valign="top"><a href="#cartpromocodemodificationsresult">CartPromoCodeModificationsResult</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>someProductWasRemovedFromEshop</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>transportModifications</strong></td>
<td valign="top"><a href="#carttransportmodificationsresult">CartTransportModificationsResult</a>!</td>
<td></td>
</tr>
</tbody>
</table>

### CartPaymentModificationsResult

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>paymentPriceChanged</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>paymentUnavailable</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
</tbody>
</table>

### CartPromoCodeModificationsResult

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>noLongerApplicablePromoCode</strong></td>
<td valign="top">[<a href="#string">String</a>!]!</td>
<td></td>
</tr>
</tbody>
</table>

### CartTransportModificationsResult

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>personalPickupStoreUnavailable</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>transportPriceChanged</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>transportUnavailable</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>transportWeightLimitExceeded</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
</tbody>
</table>

### Category

Represents a category

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>bestsellers</strong></td>
<td valign="top">[<a href="#product">Product</a>!]!</td>
<td>

Best selling products

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>breadcrumb</strong></td>
<td valign="top">[<a href="#link">Link</a>!]!</td>
<td>

Hierarchy of the current element in relation to the structure

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>categoryHierarchy</strong></td>
<td valign="top">[<a href="#categoryhierarchyitem">CategoryHierarchyItem</a>!]!</td>
<td>

All parent category names with their UUIDs

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>children</strong></td>
<td valign="top">[<a href="#category">Category</a>!]!</td>
<td>

Descendant categories

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>description</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Localized category description (domain dependent)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>id</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

ID of category

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>images</strong></td>
<td valign="top">[<a href="#image">Image</a>!]!</td>
<td>

Category images

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">sizes</td>
<td valign="top">[<a href="#string">String</a>!]</td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>linkedCategories</strong></td>
<td valign="top">[<a href="#category">Category</a>!]!</td>
<td>

A list of categories linked to the given category

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>mainImage</strong></td>
<td valign="top"><a href="#image">Image</a></td>
<td>

Category image by params

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Localized category name (domain dependent)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>originalCategorySlug</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Original category URL slug (for CategorySeoMixes slug of assigned category is
returned, null is returned for regular category)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>parent</strong></td>
<td valign="top"><a href="#category">Category</a></td>
<td>

Ancestor category

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>products</strong></td>
<td valign="top"><a href="#productconnection">ProductConnection</a>!</td>
<td>

Paginated and ordered products of category

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">after</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">before</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">filter</td>
<td valign="top"><a href="#productfilter">ProductFilter</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">first</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">last</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">orderingMode</td>
<td valign="top"><a href="#productorderingmodeenum">ProductOrderingModeEnum</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">search</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>readyCategorySeoMixLinks</strong></td>
<td valign="top">[<a href="#link">Link</a>!]!</td>
<td>

An array of links of prepared category SEO mixes of a given category

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoH1</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Seo first level heading of category

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoMetaDescription</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Seo meta description of category

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoTitle</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Seo title of category

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>slug</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Category URL slug

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### CategoryConnection

A connection to a list of items.

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>edges</strong></td>
<td valign="top">[<a href="#categoryedge">CategoryEdge</a>]</td>
<td>

Information to aid in pagination.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>pageInfo</strong></td>
<td valign="top"><a href="#pageinfo">PageInfo</a>!</td>
<td>

Information to aid in pagination.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>totalCount</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Total number of categories

</td>
</tr>
</tbody>
</table>

### CategoryEdge

An edge in a connection.

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>cursor</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

A cursor for use in pagination.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>node</strong></td>
<td valign="top"><a href="#category">Category</a></td>
<td>

The item at the end of the edge.

</td>
</tr>
</tbody>
</table>

### CategoryHierarchyItem

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Localized category name (domain dependent)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### CompanyCustomerUser

Represents an currently logged customer user

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>city</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address city name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>companyName</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The customer’s company name (only when customer is a company)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>companyNumber</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The customer’s company identification number (only when customer is a company)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>companyTaxNumber</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The customer’s company tax number (only when customer is a company)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>country</strong></td>
<td valign="top"><a href="#country">Country</a>!</td>
<td>

Billing address country

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>defaultDeliveryAddress</strong></td>
<td valign="top"><a href="#deliveryaddress">DeliveryAddress</a></td>
<td>

Default customer delivery addresses

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryAddresses</strong></td>
<td valign="top">[<a href="#deliveryaddress">DeliveryAddress</a>!]!</td>
<td>

List of delivery addresses

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>email</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Email address

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>firstName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

First name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>lastName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Last name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>newsletterSubscription</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Whether customer user receives newsletters or not

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>postcode</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address zip code

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>pricingGroup</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The name of the customer pricing group

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>street</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address street name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>telephone</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Phone number

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### Comparison

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>products</strong></td>
<td valign="top">[<a href="#product">Product</a>!]!</td>
<td>

List of compared products

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

Comparison identifier

</td>
</tr>
</tbody>
</table>

### Country

Represents country

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>code</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Country code in ISO 3166-1 alpha-2

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Localized country name

</td>
</tr>
</tbody>
</table>

### CreateOrderResult

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>cart</strong></td>
<td valign="top"><a href="#cart">Cart</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>order</strong></td>
<td valign="top"><a href="#order">Order</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>orderCreated</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
</tbody>
</table>

### DeliveryAddress

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>city</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Delivery address city name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>companyName</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Delivery address company name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>country</strong></td>
<td valign="top"><a href="#country">Country</a></td>
<td>

Delivery address country

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>firstName</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Delivery address firstname

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>lastName</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Delivery address lastname

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>postcode</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Delivery address zip code

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>street</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Delivery address street name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>telephone</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Delivery address telephone

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### File

Represents a downloadable file

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>anchorText</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Clickable text for a hyperlink

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>url</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Url to download the file

</td>
</tr>
</tbody>
</table>

### Flag

Represents a flag

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>breadcrumb</strong></td>
<td valign="top">[<a href="#link">Link</a>!]!</td>
<td>

Hierarchy of the current element in relation to the structure

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>categories</strong></td>
<td valign="top">[<a href="#category">Category</a>!]!</td>
<td>

Categories containing at least one product with flag

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">productFilter</td>
<td valign="top"><a href="#productfilter">ProductFilter</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Localized flag name (domain dependent)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>products</strong></td>
<td valign="top"><a href="#productconnection">ProductConnection</a>!</td>
<td>

Paginated and ordered products of flag

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">after</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">before</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">filter</td>
<td valign="top"><a href="#productfilter">ProductFilter</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">first</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">last</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">orderingMode</td>
<td valign="top"><a href="#productorderingmodeenum">ProductOrderingModeEnum</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">search</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>rgbColor</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Flag color in rgb format

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>slug</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

URL slug of flag

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### FlagFilterOption

Flag filter option

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>count</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Count of products that will be filtered if this filter option is applied.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>flag</strong></td>
<td valign="top"><a href="#flag">Flag</a>!</td>
<td>

Flag

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isAbsolute</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

If true than count parameter is number of products that will be displayed if
this filter option is applied, if false count parameter is number of products
that will be added to current products result.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isSelected</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Indicator whether the option is already selected (used for "ready category seo mixes")

</td>
</tr>
</tbody>
</table>

### GoPayBankSwift

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>imageLargeUrl</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

large image url

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>imageNormalUrl</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

normal image url

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isOnline</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Bank name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>swift</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Swift code

</td>
</tr>
</tbody>
</table>

### GoPayCreatePaymentSetup

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>embedJs</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

url of gopay embedJs file

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>gatewayUrl</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

redirect URL to payment gateway

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>goPayId</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

payment transaction identifier

</td>
</tr>
</tbody>
</table>

### GoPayPaymentMethod

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>identifier</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Identifier of payment method

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>imageLargeUrl</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

URL to large size image of payment method

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>imageNormalUrl</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

URL to normal size image of payment method

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Name of payment method

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>paymentGroup</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Group of payment methods

</td>
</tr>
</tbody>
</table>

### Image

Represents an image

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Image name for ALT attribute

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>position</strong></td>
<td valign="top"><a href="#int">Int</a></td>
<td>

Position of image in list

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>sizes</strong></td>
<td valign="top">[<a href="#imagesize">ImageSize</a>!]!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>type</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Image type

</td>
</tr>
</tbody>
</table>

### ImageSize

Represents a single image size

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>additionalSizes</strong></td>
<td valign="top">[<a href="#additionalsize">AdditionalSize</a>!]!</td>
<td>

Additional sizes for different screen types

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>height</strong></td>
<td valign="top"><a href="#int">Int</a></td>
<td>

Height in pixels defined in images.yaml

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>size</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Image size defined in images.yaml

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>url</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

URL address of image

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>width</strong></td>
<td valign="top"><a href="#int">Int</a></td>
<td>

Width in pixels defined in images.yaml

</td>
</tr>
</tbody>
</table>

### LanguageConstant

Represents a single user translation of language constant

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>key</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Translation key

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>translation</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

User translation

</td>
</tr>
</tbody>
</table>

### Link

Represents an internal link

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Clickable text for a hyperlink

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>slug</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Target URL slug

</td>
</tr>
</tbody>
</table>

### LoginResult

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>showCartMergeInfo</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>tokens</strong></td>
<td valign="top"><a href="#token">Token</a>!</td>
<td></td>
</tr>
</tbody>
</table>

### MainVariant

Represents a product

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>accessories</strong></td>
<td valign="top">[<a href="#product">Product</a>!]!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>availability</strong></td>
<td valign="top"><a href="#availability">Availability</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>availableStoresCount</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Number of the stores where the product is available

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>brand</strong></td>
<td valign="top"><a href="#brand">Brand</a></td>
<td>

Brand of product

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>breadcrumb</strong></td>
<td valign="top">[<a href="#link">Link</a>!]!</td>
<td>

Hierarchy of the current element in relation to the structure

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>catalogNumber</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Product catalog number

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>categories</strong></td>
<td valign="top">[<a href="#category">Category</a>!]!</td>
<td>

List of categories

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>description</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>ean</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

EAN

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>exposedStoresCount</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Number of the stores where the product is exposed

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>files</strong></td>
<td valign="top">[<a href="#file">File</a>!]!</td>
<td>

List of downloadable files

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>flags</strong></td>
<td valign="top">[<a href="#flag">Flag</a>!]!</td>
<td>

List of flags

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>fullName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The full name of the product, which consists of a prefix, name, and a suffix

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>hasPreorder</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Distinguishes if the product can be pre-ordered

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>id</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Product id

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>images</strong></td>
<td valign="top">[<a href="#image">Image</a>!]!</td>
<td>

Product images

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">sizes</td>
<td valign="top">[<a href="#string">String</a>!]</td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isMainVariant</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isSellingDenied</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isUsingStock</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>link</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Product link

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>mainImage</strong></td>
<td valign="top"><a href="#image">Image</a></td>
<td>

Product image by params

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Localized product name (domain dependent)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>namePrefix</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Name prefix

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>nameSuffix</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Name suffix

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>orderingPriority</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>parameters</strong></td>
<td valign="top">[<a href="#parameter">Parameter</a>!]!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>partNumber</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Product part number

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>price</strong></td>
<td valign="top"><a href="#productprice">ProductPrice</a>!</td>
<td>

Product price

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>productVideos</strong></td>
<td valign="top">[<a href="#videotoken">VideoToken</a>!]!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>relatedProducts</strong></td>
<td valign="top">[<a href="#product">Product</a>!]!</td>
<td>

List of related products

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoH1</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Seo first level heading of product

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoMetaDescription</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Seo meta description of product

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoTitle</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Seo title of product

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>shortDescription</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Localized product short description (domain dependent)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>slug</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Product URL slug

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>stockQuantity</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Count of quantity on stock

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>storeAvailabilities</strong></td>
<td valign="top">[<a href="#storeavailability">StoreAvailability</a>!]!</td>
<td>

List of availabilities in individual stores

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>unit</strong></td>
<td valign="top"><a href="#unit">Unit</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>usps</strong></td>
<td valign="top">[<a href="#string">String</a>!]!</td>
<td>

List of product's unique selling propositions

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>variants</strong></td>
<td valign="top">[<a href="#variant">Variant</a>!]!</td>
<td></td>
</tr>
</tbody>
</table>

### NavigationItem

Represents a navigation structure item

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>categoriesByColumns</strong></td>
<td valign="top">[<a href="#navigationitemcategoriesbycolumns">NavigationItemCategoriesByColumns</a>!]!</td>
<td>

Categories separated into columns

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>link</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Target URL

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Navigation item name

</td>
</tr>
</tbody>
</table>

### NavigationItemCategoriesByColumns

Represents a single column inside the navigation item

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>categories</strong></td>
<td valign="top">[<a href="#category">Category</a>!]!</td>
<td>

Categories

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>columnNumber</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Column number

</td>
</tr>
</tbody>
</table>

### NewsletterSubscriber

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>createdAt</strong></td>
<td valign="top"><a href="#datetime">DateTime</a>!</td>
<td>

Date and time of subscription

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>email</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Subscribed email address

</td>
</tr>
</tbody>
</table>

### NotificationBar

Represents a notification supposed to be displayed on all pages

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>images</strong></td>
<td valign="top">[<a href="#image">Image</a>!]!</td>
<td>

Notification bar images

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">sizes</td>
<td valign="top">[<a href="#string">String</a>!]</td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>mainImage</strong></td>
<td valign="top"><a href="#image">Image</a></td>
<td>

Notification bar image by params

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>rgbColor</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Color of the notification

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>text</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Message of the notification

</td>
</tr>
</tbody>
</table>

### Order

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>city</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address city name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>companyName</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The customer’s company name (only when ordered on the company behalf)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>companyNumber</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The customer’s company identification number (only when ordered on the company behalf)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>companyTaxNumber</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The customer’s company tax number (only when ordered on the company behalf)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>country</strong></td>
<td valign="top"><a href="#country">Country</a>!</td>
<td>

Billing address country

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>creationDate</strong></td>
<td valign="top"><a href="#datetime">DateTime</a>!</td>
<td>

Date and time when the order was created

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryCity</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

City name for delivery

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryCompanyName</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Company name for delivery

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryCountry</strong></td>
<td valign="top"><a href="#country">Country</a></td>
<td>

Country for delivery

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryFirstName</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

First name of the contact person for delivery

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryLastName</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Last name of the contact person for delivery

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryPostcode</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Zip code for delivery

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryStreet</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Street name for delivery

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryTelephone</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Contact telephone number for delivery

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>differentDeliveryAddress</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Indicates whether the billing address is other than a delivery address

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>email</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The customer's email address

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>firstName</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The customer's first name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>items</strong></td>
<td valign="top">[<a href="#orderitem">OrderItem</a>!]!</td>
<td>

All items in the order including payment and transport

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>lastName</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The customer's last name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>note</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Other information related to the order

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>number</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Unique order number

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>payment</strong></td>
<td valign="top"><a href="#payment">Payment</a>!</td>
<td>

Payment method applied to the order

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>pickupPlaceIdentifier</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Selected pickup place identifier

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>postcode</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address zip code

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>productItems</strong></td>
<td valign="top">[<a href="#orderitem">OrderItem</a>!]!</td>
<td>

All product items in the order

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>promoCode</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Promo code (coupon) used in the order

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>status</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Current status of the order

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>street</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address street name 

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>telephone</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The customer's telephone number

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>totalPrice</strong></td>
<td valign="top"><a href="#price">Price</a>!</td>
<td>

Total price of the order including transport and payment prices

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>trackingNumber</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The order tracking number

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>trackingUrl</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The order tracking link

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>transport</strong></td>
<td valign="top"><a href="#transport">Transport</a>!</td>
<td>

Transport method applied to the order

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>urlHash</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Unique url hash that can be used to 

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### OrderConnection

A connection to a list of items.

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>edges</strong></td>
<td valign="top">[<a href="#orderedge">OrderEdge</a>]</td>
<td>

Information to aid in pagination.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>pageInfo</strong></td>
<td valign="top"><a href="#pageinfo">PageInfo</a>!</td>
<td>

Information to aid in pagination.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>totalCount</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Total number of orders

</td>
</tr>
</tbody>
</table>

### OrderEdge

An edge in a connection.

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>cursor</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

A cursor for use in pagination.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>node</strong></td>
<td valign="top"><a href="#order">Order</a></td>
<td>

The item at the end of the edge.

</td>
</tr>
</tbody>
</table>

### OrderItem

Represent one item in the order

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Name of the order item

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>quantity</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Quantity of order items in the order

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>totalPrice</strong></td>
<td valign="top"><a href="#price">Price</a>!</td>
<td>

Total price for the quantity of order item

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>unit</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Unit of measurement used for the order item

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>unitPrice</strong></td>
<td valign="top"><a href="#price">Price</a>!</td>
<td>

Order item price per unit

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>vatRate</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Applied VAT rate percentage applied to the order item

</td>
</tr>
</tbody>
</table>

### PageInfo

Information about pagination in a connection.

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>endCursor</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

When paginating forwards, the cursor to continue.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>hasNextPage</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

When paginating forwards, are there more items?

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>hasPreviousPage</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

When paginating backwards, are there more items?

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>startCursor</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

When paginating backwards, the cursor to continue.

</td>
</tr>
</tbody>
</table>

### Parameter

Represents a parameter

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>group</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Parameter group to which the parameter is assigned

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Parameter name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>unit</strong></td>
<td valign="top"><a href="#unit">Unit</a></td>
<td>

Unit of the parameter

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>values</strong></td>
<td valign="top">[<a href="#parametervalue">ParameterValue</a>!]!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>visible</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
</tbody>
</table>

### ParameterCheckboxFilterOption

Parameter filter option

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>isCollapsed</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Indicator whether the parameter should be collapsed based on the current category setting

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The parameter name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>unit</strong></td>
<td valign="top"><a href="#unit">Unit</a></td>
<td>

The parameter unit

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

The parameter UUID

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>values</strong></td>
<td valign="top">[<a href="#parametervaluefilteroption">ParameterValueFilterOption</a>!]!</td>
<td>

Filter options of parameter values

</td>
</tr>
</tbody>
</table>

### ParameterColorFilterOption

Parameter filter option

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>isCollapsed</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Indicator whether the parameter should be collapsed based on the current category setting

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The parameter name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>unit</strong></td>
<td valign="top"><a href="#unit">Unit</a></td>
<td>

The parameter unit

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

The parameter UUID

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>values</strong></td>
<td valign="top">[<a href="#parametervaluecolorfilteroption">ParameterValueColorFilterOption</a>!]!</td>
<td>

Filter options of parameter values

</td>
</tr>
</tbody>
</table>

### ParameterSliderFilterOption

Parameter filter option

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>isCollapsed</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Indicator whether the parameter should be collapsed based on the current category setting

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>maximalValue</strong></td>
<td valign="top"><a href="#float">Float</a>!</td>
<td>

The parameter maximal value

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>minimalValue</strong></td>
<td valign="top"><a href="#float">Float</a>!</td>
<td>

The parameter minimal value

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The parameter name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>selectedValue</strong></td>
<td valign="top"><a href="#float">Float</a></td>
<td>

The pre-selected value (used for "ready category seo mixes")

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>unit</strong></td>
<td valign="top"><a href="#unit">Unit</a></td>
<td>

The parameter unit

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

The parameter UUID

</td>
</tr>
</tbody>
</table>

### ParameterValue

Represents a parameter value

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>text</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Parameter value

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### ParameterValueColorFilterOption

Parameter value filter option

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>count</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Count of products that will be filtered if this filter option is applied.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isAbsolute</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

If true than count parameter is number of products that will be displayed if
this filter option is applied, if false count parameter is number of products
that will be added to current products result.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isSelected</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Indicator whether the option is already selected (used for "ready category seo mixes")

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>rgbHex</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

RGB hex of color parameter

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>text</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Parameter value

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### ParameterValueFilterOption

Parameter value filter option

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>count</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Count of products that will be filtered if this filter option is applied.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isAbsolute</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

If true than count parameter is number of products that will be displayed if
this filter option is applied, if false count parameter is number of products
that will be added to current products result.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isSelected</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Indicator whether the option is already selected (used for "ready category seo mixes")

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>text</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Parameter value

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### Payment

Represents a payment

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>description</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Localized payment description (domain dependent)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>goPayPaymentMethod</strong></td>
<td valign="top"><a href="#gopaypaymentmethod">GoPayPaymentMethod</a></td>
<td>

Additional data for GoPay payment

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>images</strong></td>
<td valign="top">[<a href="#image">Image</a>!]!</td>
<td>

Payment images

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">sizes</td>
<td valign="top">[<a href="#string">String</a>!]</td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>instruction</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Localized payment instruction (domain dependent)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>mainImage</strong></td>
<td valign="top"><a href="#image">Image</a></td>
<td>

Payment image by params

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Payment name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>position</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Payment position

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>price</strong></td>
<td valign="top"><a href="#price">Price</a>!</td>
<td>

Payment price

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">cartUuid</td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>transports</strong></td>
<td valign="top">[<a href="#transport">Transport</a>!]!</td>
<td>

List of assigned transports

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>type</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Type of payment

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### PaymentSetupCreationData

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>goPayCreatePaymentSetup</strong></td>
<td valign="top"><a href="#gopaycreatepaymentsetup">GoPayCreatePaymentSetup</a></td>
<td>

Identifiers of GoPay payment method

</td>
</tr>
</tbody>
</table>

### PersonalData

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>customerUser</strong></td>
<td valign="top"><a href="#customeruser">CustomerUser</a></td>
<td>

Customer user data

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>exportLink</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

A link for downloading the personal data in an XML file

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>newsletterSubscriber</strong></td>
<td valign="top"><a href="#newslettersubscriber">NewsletterSubscriber</a></td>
<td>

Newsletter subscription

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>orders</strong></td>
<td valign="top">[<a href="#order">Order</a>!]!</td>
<td>

Customer orders

</td>
</tr>
</tbody>
</table>

### PersonalDataPage

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>displaySiteContent</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The HTML content of the site where a customer can request displaying his personal data

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>displaySiteSlug</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

URL slug of display site

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>exportSiteContent</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The HTML content of the site where a customer can request exporting his personal data

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>exportSiteSlug</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

URL slug of export site

</td>
</tr>
</tbody>
</table>

### Price

Represents the price

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>priceWithoutVat</strong></td>
<td valign="top"><a href="#money">Money</a>!</td>
<td>

Price without VAT

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>priceWithVat</strong></td>
<td valign="top"><a href="#money">Money</a>!</td>
<td>

Price with VAT

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>vatAmount</strong></td>
<td valign="top"><a href="#money">Money</a>!</td>
<td>

Total value of VAT

</td>
</tr>
</tbody>
</table>

### PricingSetting

Represents setting of pricing

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>defaultCurrencyCode</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Code of the default currency used on the current domain

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>minimumFractionDigits</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Minimum number of decimal places for the price on the current domain

</td>
</tr>
</tbody>
</table>

### ProductConnection

A connection to a list of items.

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>defaultOrderingMode</strong></td>
<td valign="top"><a href="#productorderingmodeenum">ProductOrderingModeEnum</a></td>
<td>

The default ordering mode that is set for the given connection (e.g. in a
category, search page, or ready category SEO mix)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>edges</strong></td>
<td valign="top">[<a href="#productedge">ProductEdge</a>]</td>
<td>

Information to aid in pagination.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>orderingMode</strong></td>
<td valign="top"><a href="#productorderingmodeenum">ProductOrderingModeEnum</a>!</td>
<td>

The current ordering mode

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>pageInfo</strong></td>
<td valign="top"><a href="#pageinfo">PageInfo</a>!</td>
<td>

Information to aid in pagination.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>productFilterOptions</strong></td>
<td valign="top"><a href="#productfilteroptions">ProductFilterOptions</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>totalCount</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Total number of products

</td>
</tr>
</tbody>
</table>

### ProductEdge

An edge in a connection.

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>cursor</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

A cursor for use in pagination.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>node</strong></td>
<td valign="top"><a href="#product">Product</a></td>
<td>

The item at the end of the edge.

</td>
</tr>
</tbody>
</table>

### ProductFilterOptions

Represents a product filter options

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>brands</strong></td>
<td valign="top">[<a href="#brandfilteroption">BrandFilterOption</a>!]</td>
<td>

Brands filter options

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>flags</strong></td>
<td valign="top">[<a href="#flagfilteroption">FlagFilterOption</a>!]</td>
<td>

Flags filter options

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>inStock</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Number of products in stock that will be filtered

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>maximalPrice</strong></td>
<td valign="top"><a href="#money">Money</a>!</td>
<td>

Maximal price of products for filtering

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>minimalPrice</strong></td>
<td valign="top"><a href="#money">Money</a>!</td>
<td>

Minimal price of products for filtering

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>parameters</strong></td>
<td valign="top">[<a href="#parameterfilteroptioninterface">ParameterFilterOptionInterface</a>!]</td>
<td>

Parameter filter options

</td>
</tr>
</tbody>
</table>

### ProductPrice

Represents the price of the product

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>isPriceFrom</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Determines whether it's a final price or starting price

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>priceWithoutVat</strong></td>
<td valign="top"><a href="#money">Money</a>!</td>
<td>

Price without VAT

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>priceWithVat</strong></td>
<td valign="top"><a href="#money">Money</a>!</td>
<td>

Price with VAT

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>vatAmount</strong></td>
<td valign="top"><a href="#money">Money</a>!</td>
<td>

Total value of VAT

</td>
</tr>
</tbody>
</table>

### RegularCustomerUser

Represents an currently logged customer user

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>city</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address city name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>country</strong></td>
<td valign="top"><a href="#country">Country</a>!</td>
<td>

Billing address country

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>defaultDeliveryAddress</strong></td>
<td valign="top"><a href="#deliveryaddress">DeliveryAddress</a></td>
<td>

Default customer delivery addresses

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryAddresses</strong></td>
<td valign="top">[<a href="#deliveryaddress">DeliveryAddress</a>!]!</td>
<td>

List of delivery addresses

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>email</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Email address

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>firstName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

First name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>lastName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Last name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>newsletterSubscription</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Whether customer user receives newsletters or not

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>postcode</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address zip code

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>pricingGroup</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The name of the customer pricing group

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>street</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address street name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>telephone</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Phone number

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### RegularProduct

Represents a product

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>accessories</strong></td>
<td valign="top">[<a href="#product">Product</a>!]!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>availability</strong></td>
<td valign="top"><a href="#availability">Availability</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>availableStoresCount</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Number of the stores where the product is available

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>brand</strong></td>
<td valign="top"><a href="#brand">Brand</a></td>
<td>

Brand of product

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>breadcrumb</strong></td>
<td valign="top">[<a href="#link">Link</a>!]!</td>
<td>

Hierarchy of the current element in relation to the structure

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>catalogNumber</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Product catalog number

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>categories</strong></td>
<td valign="top">[<a href="#category">Category</a>!]!</td>
<td>

List of categories

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>description</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>ean</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

EAN

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>exposedStoresCount</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Number of the stores where the product is exposed

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>files</strong></td>
<td valign="top">[<a href="#file">File</a>!]!</td>
<td>

List of downloadable files

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>flags</strong></td>
<td valign="top">[<a href="#flag">Flag</a>!]!</td>
<td>

List of flags

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>fullName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The full name of the product, which consists of a prefix, name, and a suffix

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>hasPreorder</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Distinguishes if the product can be pre-ordered

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>id</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Product id

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>images</strong></td>
<td valign="top">[<a href="#image">Image</a>!]!</td>
<td>

Product images

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">sizes</td>
<td valign="top">[<a href="#string">String</a>!]</td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isMainVariant</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isSellingDenied</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isUsingStock</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>link</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Product link

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>mainImage</strong></td>
<td valign="top"><a href="#image">Image</a></td>
<td>

Product image by params

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Localized product name (domain dependent)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>namePrefix</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Name prefix

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>nameSuffix</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Name suffix

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>orderingPriority</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>parameters</strong></td>
<td valign="top">[<a href="#parameter">Parameter</a>!]!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>partNumber</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Product part number

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>price</strong></td>
<td valign="top"><a href="#productprice">ProductPrice</a>!</td>
<td>

Product price

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>productVideos</strong></td>
<td valign="top">[<a href="#videotoken">VideoToken</a>!]!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>relatedProducts</strong></td>
<td valign="top">[<a href="#product">Product</a>!]!</td>
<td>

List of related products

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoH1</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Seo first level heading of product

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoMetaDescription</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Seo meta description of product

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoTitle</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Seo title of product

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>shortDescription</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Localized product short description (domain dependent)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>slug</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Product URL slug

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>stockQuantity</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Count of quantity on stock

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>storeAvailabilities</strong></td>
<td valign="top">[<a href="#storeavailability">StoreAvailability</a>!]!</td>
<td>

List of availabilities in individual stores

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>unit</strong></td>
<td valign="top"><a href="#unit">Unit</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>usps</strong></td>
<td valign="top">[<a href="#string">String</a>!]!</td>
<td>

List of product's unique selling propositions

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### SeoPage

Represents SEO settings for specific page

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>canonicalUrl</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Page's canonical link

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>metaDescription</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Description for meta tag

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>ogDescription</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Description for og:description meta tag

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>ogImage</strong></td>
<td valign="top"><a href="#image">Image</a></td>
<td>

Image for og image meta tag by params

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>ogTitle</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Title for og:title meta tag

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>title</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Document's title that is shown in a browser's title

</td>
</tr>
</tbody>
</table>

### SeoSetting

Represents setting of SEO

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>metaDescription</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Description of the content of a web page

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>robotsTxtContent</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Robots.txt's file content

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>title</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Document's title that is shown in a browser's title

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>titleAddOn</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Complement to title

</td>
</tr>
</tbody>
</table>

### Settings

Represents settings of the current domain

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>contactFormMainText</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Main text for contact form

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>pricing</strong></td>
<td valign="top"><a href="#pricingsetting">PricingSetting</a>!</td>
<td>

Settings related to pricing

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seo</strong></td>
<td valign="top"><a href="#seosetting">SeoSetting</a>!</td>
<td>

Settings related to SEO

</td>
</tr>
</tbody>
</table>

### SliderItem

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>extendedText</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Text below slider

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>extendedTextLink</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Target link of text below slider

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>gtmCreative</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

GTM creative

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>gtmId</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

GTM ID

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>images</strong></td>
<td valign="top">[<a href="#image">Image</a>!]!</td>
<td>

Slider item images

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">sizes</td>
<td valign="top">[<a href="#string">String</a>!]</td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>link</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Target link

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>mainImage</strong></td>
<td valign="top"><a href="#image">Image</a></td>
<td>

Slider item image by params

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Slider name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### Store

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>breadcrumb</strong></td>
<td valign="top">[<a href="#link">Link</a>!]!</td>
<td>

Hierarchy of the current element in relation to the structure

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>city</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Store address city

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>contactInfo</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>country</strong></td>
<td valign="top"><a href="#country">Country</a>!</td>
<td>

Store address country

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>description</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Store description

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>images</strong></td>
<td valign="top">[<a href="#image">Image</a>!]!</td>
<td>

Store images

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">sizes</td>
<td valign="top">[<a href="#string">String</a>!]</td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isDefault</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Is set as default store

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>locationLatitude</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Store location latitude

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>locationLongitude</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Store location longitude

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Store name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>openingHours</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Store opening hours

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>openingHoursHtml</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Store opening hours, newlines are rendered as HTML breakline

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>postcode</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Store address postcode

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>slug</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Store URL slug

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>specialMessage</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>street</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Store address street

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### StoreAvailability

Represents an availability in an individual store

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>availabilityInformation</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Detailed information about availability

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>availabilityStatus</strong></td>
<td valign="top"><a href="#availabilitystatusenum">AvailabilityStatusEnum</a>!</td>
<td>

Availability status in a format suitable for usage in the code

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>exposed</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Is product exposed on this store

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>store</strong></td>
<td valign="top"><a href="#store">Store</a></td>
<td>

Store

</td>
</tr>
</tbody>
</table>

### StoreConnection

A connection to a list of items.

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>edges</strong></td>
<td valign="top">[<a href="#storeedge">StoreEdge</a>]</td>
<td>

Information to aid in pagination.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>pageInfo</strong></td>
<td valign="top"><a href="#pageinfo">PageInfo</a>!</td>
<td>

Information to aid in pagination.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>totalCount</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Total number of stores

</td>
</tr>
</tbody>
</table>

### StoreEdge

An edge in a connection.

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>cursor</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

A cursor for use in pagination.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>node</strong></td>
<td valign="top"><a href="#store">Store</a></td>
<td>

The item at the end of the edge.

</td>
</tr>
</tbody>
</table>

### Token

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>accessToken</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>refreshToken</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td></td>
</tr>
</tbody>
</table>

### Transport

Represents a transport

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>daysUntilDelivery</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Number of days until goods are delivered

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>description</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Localized transport description (domain dependent)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>images</strong></td>
<td valign="top">[<a href="#image">Image</a>!]!</td>
<td>

Transport images

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">sizes</td>
<td valign="top">[<a href="#string">String</a>!]</td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>instruction</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Localized transport instruction (domain dependent)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isPersonalPickup</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Pointer telling if the transport is of type personal pickup

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>mainImage</strong></td>
<td valign="top"><a href="#image">Image</a></td>
<td>

Transport image by params

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Transport name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>payments</strong></td>
<td valign="top">[<a href="#payment">Payment</a>!]!</td>
<td>

List of assigned payments

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>position</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Transport position

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>price</strong></td>
<td valign="top"><a href="#price">Price</a>!</td>
<td>

Transport price

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">cartUuid</td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>stores</strong></td>
<td valign="top"><a href="#storeconnection">StoreConnection</a></td>
<td>

Stores available for personal pickup

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>transportType</strong></td>
<td valign="top"><a href="#transporttype">TransportType</a>!</td>
<td>

Type of transport

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### TransportType

Represents a transport type

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>code</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Code of transport

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Name of transport type

</td>
</tr>
</tbody>
</table>

### Unit

Represents a unit

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Localized unit name (domain dependent)

</td>
</tr>
</tbody>
</table>

### Variant

Represents a product

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>accessories</strong></td>
<td valign="top">[<a href="#product">Product</a>!]!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>availability</strong></td>
<td valign="top"><a href="#availability">Availability</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>availableStoresCount</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Number of the stores where the product is available

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>brand</strong></td>
<td valign="top"><a href="#brand">Brand</a></td>
<td>

Brand of product

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>breadcrumb</strong></td>
<td valign="top">[<a href="#link">Link</a>!]!</td>
<td>

Hierarchy of the current element in relation to the structure

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>catalogNumber</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Product catalog number

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>categories</strong></td>
<td valign="top">[<a href="#category">Category</a>!]!</td>
<td>

List of categories

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>description</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>ean</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

EAN

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>exposedStoresCount</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Number of the stores where the product is exposed

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>files</strong></td>
<td valign="top">[<a href="#file">File</a>!]!</td>
<td>

List of downloadable files

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>flags</strong></td>
<td valign="top">[<a href="#flag">Flag</a>!]!</td>
<td>

List of flags

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>fullName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The full name of the product, which consists of a prefix, name, and a suffix

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>hasPreorder</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Distinguishes if the product can be pre-ordered

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>id</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Product id

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>images</strong></td>
<td valign="top">[<a href="#image">Image</a>!]!</td>
<td>

Product images

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">sizes</td>
<td valign="top">[<a href="#string">String</a>!]</td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isMainVariant</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isSellingDenied</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isUsingStock</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>link</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Product link

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>mainImage</strong></td>
<td valign="top"><a href="#image">Image</a></td>
<td>

Product image by params

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>mainVariant</strong></td>
<td valign="top"><a href="#mainvariant">MainVariant</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Localized product name (domain dependent)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>namePrefix</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Name prefix

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>nameSuffix</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Name suffix

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>orderingPriority</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>parameters</strong></td>
<td valign="top">[<a href="#parameter">Parameter</a>!]!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>partNumber</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Product part number

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>price</strong></td>
<td valign="top"><a href="#productprice">ProductPrice</a>!</td>
<td>

Product price

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>productVideos</strong></td>
<td valign="top">[<a href="#videotoken">VideoToken</a>!]!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>relatedProducts</strong></td>
<td valign="top">[<a href="#product">Product</a>!]!</td>
<td>

List of related products

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoH1</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Seo first level heading of product

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoMetaDescription</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Seo meta description of product

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoTitle</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Seo title of product

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>shortDescription</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Localized product short description (domain dependent)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>slug</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Product URL slug

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>stockQuantity</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Count of quantity on stock

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>storeAvailabilities</strong></td>
<td valign="top">[<a href="#storeavailability">StoreAvailability</a>!]!</td>
<td>

List of availabilities in individual stores

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>unit</strong></td>
<td valign="top"><a href="#unit">Unit</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>usps</strong></td>
<td valign="top">[<a href="#string">String</a>!]!</td>
<td>

List of product's unique selling propositions

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### VideoToken

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>description</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>token</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td></td>
</tr>
</tbody>
</table>

## Inputs

### AddToCartInput

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>cartUuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td>

Cart identifier, new cart will be created if not provided and customer is not logged in

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isAbsoluteQuantity</strong></td>
<td valign="top"><a href="#boolean">Boolean</a></td>
<td>

True if quantity should be set no matter the current state of the cart. False
if quantity should be added to the already existing same item in the cart

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>productUuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

Product UUID

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>quantity</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Item quantity

</td>
</tr>
</tbody>
</table>

### ApplyPromoCodeToCartInput

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>cartUuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td>

Cart identifier or null if customer is logged in

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>promoCode</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Promo code to be used after checkout

</td>
</tr>
</tbody>
</table>

### CartInput

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>cartUuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td>

Cart identifier, new cart will be created if not provided and customer is not logged in

</td>
</tr>
</tbody>
</table>

### ChangePasswordInput

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>email</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Customer user email.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>newPassword</strong></td>
<td valign="top"><a href="#password">Password</a>!</td>
<td>

New customer user password.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>oldPassword</strong></td>
<td valign="top"><a href="#password">Password</a>!</td>
<td>

Current customer user password.

</td>
</tr>
</tbody>
</table>

### ChangePaymentInCartInput

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>cartUuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td>

Cart identifier or null if customer is logged in

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>paymentGoPayBankSwift</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Selected bank swift code of goPay payment bank transfer

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>paymentUuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td>

UUID of a payment that should be added to the cart. If this is set to null, the payment is removed from the cart

</td>
</tr>
</tbody>
</table>

### ChangePersonalDataInput

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>city</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address city name (will be on the tax invoice)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>companyCustomer</strong></td>
<td valign="top"><a href="#boolean">Boolean</a></td>
<td>

Determines whether the customer is a company or not.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>companyName</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The customer’s company name (required when companyCustomer is true)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>companyNumber</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The customer’s company identification number (required when companyCustomer is true)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>companyTaxNumber</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The customer’s company tax number (required when companyCustomer is true)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>country</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address country code in ISO 3166-1 alpha-2 (Country will be on the tax invoice)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>firstName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Customer user first name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>lastName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Customer user last name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>newsletterSubscription</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Whether customer user should receive newsletters or not

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>postcode</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address zip code (will be on the tax invoice)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>street</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address street name (will be on the tax invoice)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>telephone</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The customer's telephone number

</td>
</tr>
</tbody>
</table>

### ChangeTransportInCartInput

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>cartUuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td>

Cart identifier or null if customer is logged in

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>pickupPlaceIdentifier</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The identifier of selected personal pickup place

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>transportUuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td>

UUID of a transport that should be added to the cart. If this is set to null, the transport is removed from the cart

</td>
</tr>
</tbody>
</table>

### ContactInput

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>email</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Email address of the sender

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>message</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Message sent to recipient

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Name of the sender

</td>
</tr>
</tbody>
</table>

### DeliveryAddressInput

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>city</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Delivery address city name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>companyName</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Delivery address company name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>country</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Delivery address country

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>firstName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Delivery address first name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>lastName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Delivery address last name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>postcode</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Delivery address zip code

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>street</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Delivery address street name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>telephone</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Delivery address telephone

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### LoginInput

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>cartUuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td>

Uuid of the cart that should be merged to the cart of the user

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>email</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The user email.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>password</strong></td>
<td valign="top"><a href="#password">Password</a>!</td>
<td>

The user password.

</td>
</tr>
</tbody>
</table>

### NewsletterSubscriptionDataInput

Represents the main input object to subscribe for e-mail newsletter

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>email</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td></td>
</tr>
</tbody>
</table>

### OrderInput

Represents the main input object to create orders

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>cartUuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td>

Cart identifier used for getting carts of not logged customers

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>city</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address city name (will be on the tax invoice)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>companyName</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The customer’s company name (required when onCompanyBehalf is true)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>companyNumber</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The customer’s company identification number (required when onCompanyBehalf is true)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>companyTaxNumber</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The customer’s company tax number (required when onCompanyBehalf is true)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>country</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address country code in ISO 3166-1 alpha-2 (Country will be on the tax invoice)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryAddressUuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td>

Delivery address identifier

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryCity</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

City name for delivery (required when differentDeliveryAddress is true)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryCompanyName</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Company name for delivery

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryCountry</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Country code in ISO 3166-1 alpha-2 for delivery (required when differentDeliveryAddress is true)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryFirstName</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

First name of the contact person for delivery (required when differentDeliveryAddress is true)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryLastName</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Last name of the contact person for delivery (required when differentDeliveryAddress is true)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryPostcode</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Zip code for delivery (required when differentDeliveryAddress is true)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryStreet</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Street name for delivery (required when differentDeliveryAddress is true)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryTelephone</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Contact telephone number for delivery

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>differentDeliveryAddress</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Determines whether to deliver products to a different address than the billing one

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>email</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The customer's email address

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>firstName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The customer's first name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>lastName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The customer's last name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>newsletterSubscription</strong></td>
<td valign="top"><a href="#boolean">Boolean</a></td>
<td>

Allows user to subscribe/unsubscribe newsletter.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>note</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Other information related to the order

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>onCompanyBehalf</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Determines whether the order is made on the company behalf.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>payment</strong></td>
<td valign="top"><a href="#paymentinput">PaymentInput</a></td>
<td>

Deprecated, this field is not used, the payment is taken from the server cart instead.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>postcode</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address zip code (will be on the tax invoice)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>products</strong></td>
<td valign="top">[<a href="#orderproductinput">OrderProductInput</a>!]</td>
<td>

Deprecated, this field is not used, the products are taken from the server cart instead.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>street</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address street name (will be on the tax invoice)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>telephone</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The customer's phone number

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>transport</strong></td>
<td valign="top"><a href="#transportinput">TransportInput</a></td>
<td>

Deprecated, this field is not used, the transport is taken from the server cart instead.

</td>
</tr>
</tbody>
</table>

### OrderProductInput

Represents a product in order

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>quantity</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Quantity of products

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>unitPrice</strong></td>
<td valign="top"><a href="#priceinput">PriceInput</a>!</td>
<td>

Product price per unit

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### ParameterFilter

Represents a parameter filter

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>maximalValue</strong></td>
<td valign="top"><a href="#float">Float</a></td>
<td>

The parameter maximal value (for parameters with "slider" type)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>minimalValue</strong></td>
<td valign="top"><a href="#float">Float</a></td>
<td>

The parameter minimal value (for parameters with "slider" type)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>parameter</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

Uuid of filtered parameter

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>values</strong></td>
<td valign="top">[<a href="#uuid">Uuid</a>!]!</td>
<td>

Array of uuids representing parameter values to be filtered by

</td>
</tr>
</tbody>
</table>

### PaymentInput

Represents a payment in order

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>price</strong></td>
<td valign="top"><a href="#priceinput">PriceInput</a>!</td>
<td>

Price for payment

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### PersonalDataAccessRequestInput

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>email</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The customer's email address

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>type</strong></td>
<td valign="top"><a href="#personaldataaccessrequesttypeenum">PersonalDataAccessRequestTypeEnum</a></td>
<td>

One of two possible types for personal data access request - display or export

</td>
</tr>
</tbody>
</table>

### PriceInput

Represents the price

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>priceWithoutVat</strong></td>
<td valign="top"><a href="#money">Money</a>!</td>
<td>

Price without VAT

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>priceWithVat</strong></td>
<td valign="top"><a href="#money">Money</a>!</td>
<td>

Price with VAT

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>vatAmount</strong></td>
<td valign="top"><a href="#money">Money</a>!</td>
<td>

Total value of VAT

</td>
</tr>
</tbody>
</table>

### ProductFilter

Represents a product filter

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>brands</strong></td>
<td valign="top">[<a href="#uuid">Uuid</a>!]</td>
<td>

Array of uuids of brands filter

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>flags</strong></td>
<td valign="top">[<a href="#uuid">Uuid</a>!]</td>
<td>

Array of uuids of flags filter

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>maximalPrice</strong></td>
<td valign="top"><a href="#money">Money</a></td>
<td>

Maximal price filter

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>minimalPrice</strong></td>
<td valign="top"><a href="#money">Money</a></td>
<td>

Minimal price filter

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>onlyInStock</strong></td>
<td valign="top"><a href="#boolean">Boolean</a></td>
<td>

Only in stock filter

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>parameters</strong></td>
<td valign="top">[<a href="#parameterfilter">ParameterFilter</a>!]</td>
<td>

Parameter filter

</td>
</tr>
</tbody>
</table>

### RecoverPasswordInput

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>email</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Customer user email.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>hash</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Hash

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>newPassword</strong></td>
<td valign="top"><a href="#password">Password</a>!</td>
<td>

New customer user password.

</td>
</tr>
</tbody>
</table>

### RefreshTokenInput

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>refreshToken</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The refresh token.

</td>
</tr>
</tbody>
</table>

### RegistrationDataInput

Represents the main input object to register customer user

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>cartUuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td>

Uuid of the cart that should be merged to the cart of the newly registered user

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>city</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address city name (will be on the tax invoice)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>companyCustomer</strong></td>
<td valign="top"><a href="#boolean">Boolean</a></td>
<td>

Determines whether the customer is a company or not.

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>companyName</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The customer’s company name (required when companyCustomer is true)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>companyNumber</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The customer’s company identification number (required when companyCustomer is true)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>companyTaxNumber</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

The customer’s company tax number (required when companyCustomer is true)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>country</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address country code in ISO 3166-1 alpha-2 (Country will be on the tax invoice)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>email</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The customer's email address

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>firstName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Customer user first name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>lastName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Customer user last name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>lastOrderUuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td>

Uuid of the last order that should be paired with the newly registered user

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>newsletterSubscription</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Whether customer user should receive newsletters or not

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>password</strong></td>
<td valign="top"><a href="#password">Password</a>!</td>
<td>

Customer user password

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>postcode</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address zip code (will be on the tax invoice)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>street</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address street name (will be on the tax invoice)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>telephone</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The customer's telephone number

</td>
</tr>
</tbody>
</table>

### RemoveFromCartInput

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>cartItemUuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

Cart item UUID

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>cartUuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td>

Cart identifier, new cart will be created if not provided and customer is not logged in

</td>
</tr>
</tbody>
</table>

### RemovePromoCodeFromCartInput

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>cartUuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td>

Cart identifier or null if customer is logged in

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>promoCode</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Promo code to be removed

</td>
</tr>
</tbody>
</table>

### TransportInput

Represents a transport in order

<table>
<thead>
<tr>
<th colspan="2" align="left">Field</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>price</strong></td>
<td valign="top"><a href="#priceinput">PriceInput</a>!</td>
<td>

Price for transport

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

## Enums

### ArticlePlacementTypeEnum

Possible placements of an article (used as an input for 'articles' query)

<table>
<thead>
<th align="left">Value</th>
<th align="left">Description</th>
</thead>
<tbody>
<tr>
<td valign="top"><strong>footer1</strong></td>
<td>

Articles in 1st footer column

</td>
</tr>
<tr>
<td valign="top"><strong>footer2</strong></td>
<td>

Articles in 2nd footer column

</td>
</tr>
<tr>
<td valign="top"><strong>footer3</strong></td>
<td>

Articles in 3rd footer column

</td>
</tr>
<tr>
<td valign="top"><strong>footer4</strong></td>
<td>

Articles in 4th footer column

</td>
</tr>
<tr>
<td valign="top"><strong>topMenu</strong></td>
<td>

Articles in top menu

</td>
</tr>
<tr>
<td valign="top"><strong>none</strong></td>
<td>

Articles without specific placement

</td>
</tr>
</tbody>
</table>

### AvailabilityStatusEnum

Product Availability statuses

<table>
<thead>
<th align="left">Value</th>
<th align="left">Description</th>
</thead>
<tbody>
<tr>
<td valign="top"><strong>InStock</strong></td>
<td>

Product availability status in stock

</td>
</tr>
<tr>
<td valign="top"><strong>OutOfStock</strong></td>
<td>

Product availability status out of stock

</td>
</tr>
</tbody>
</table>

### PersonalDataAccessRequestTypeEnum

One of two possible types for personal data access request

<table>
<thead>
<th align="left">Value</th>
<th align="left">Description</th>
</thead>
<tbody>
<tr>
<td valign="top"><strong>display</strong></td>
<td>

Display data

</td>
</tr>
<tr>
<td valign="top"><strong>export</strong></td>
<td>

Export data

</td>
</tr>
</tbody>
</table>

### ProductOrderingModeEnum

One of possible ordering modes for product

<table>
<thead>
<th align="left">Value</th>
<th align="left">Description</th>
</thead>
<tbody>
<tr>
<td valign="top"><strong>PRIORITY</strong></td>
<td>

Order by priority

</td>
</tr>
<tr>
<td valign="top"><strong>PRICE_ASC</strong></td>
<td>

Order by price ascending

</td>
</tr>
<tr>
<td valign="top"><strong>PRICE_DESC</strong></td>
<td>

Order by price descending

</td>
</tr>
<tr>
<td valign="top"><strong>NAME_ASC</strong></td>
<td>

Order by name ascending

</td>
</tr>
<tr>
<td valign="top"><strong>NAME_DESC</strong></td>
<td>

Order by name descending

</td>
</tr>
<tr>
<td valign="top"><strong>RELEVANCE</strong></td>
<td>

Order by relevance

</td>
</tr>
</tbody>
</table>

## Scalars

### Boolean

The `Boolean` scalar type represents `true` or `false`.

### DateTime

Represents and encapsulates an ISO-8601 encoded UTC date-time value

### Float

The `Float` scalar type represents signed double-precision fractional values as specified by [IEEE 754](https://en.wikipedia.org/wiki/IEEE_floating_point).

### Int

The `Int` scalar type represents non-fractional signed whole numeric values. Int can represent values between -(2^31) and 2^31 - 1.

### Money

Represents and encapsulates monetary value

### Password

Represents and encapsulates a string for password

### String

The `String` scalar type represents textual data, represented as UTF-8 character sequences. The String type is most often used by GraphQL to represent free-form human-readable text.

### Uuid

Represents and encapsulates an ISO-8601 encoded UTC date-time value


## Interfaces


### Advert

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>categories</strong></td>
<td valign="top">[<a href="#category">Category</a>!]!</td>
<td>

Restricted categories of the advert (the advert is shown in these categories only)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Name of advert

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>positionName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Position of advert

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>type</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Type of advert

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### ArticleInterface

Represents entity that is considered to be an article on the eshop

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>breadcrumb</strong></td>
<td valign="top">[<a href="#link">Link</a>!]!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoH1</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoMetaDescription</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoTitle</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>slug</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>text</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td></td>
</tr>
</tbody>
</table>

### Breadcrumb

Represents entity able to return breadcrumb

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>breadcrumb</strong></td>
<td valign="top">[<a href="#link">Link</a>!]!</td>
<td>

Hierarchy of the current element in relation to the structure

</td>
</tr>
</tbody>
</table>

### CartInterface

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>items</strong></td>
<td valign="top">[<a href="#cartitem">CartItem</a>!]!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>modifications</strong></td>
<td valign="top"><a href="#cartmodificationsresult">CartModificationsResult</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>payment</strong></td>
<td valign="top"><a href="#payment">Payment</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>paymentGoPayBankSwift</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>promoCode</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>remainingAmountWithVatForFreeTransport</strong></td>
<td valign="top"><a href="#money">Money</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>selectedPickupPlaceIdentifier</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>totalDiscountPrice</strong></td>
<td valign="top"><a href="#price">Price</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>totalItemsPrice</strong></td>
<td valign="top"><a href="#price">Price</a>!</td>
<td>

Total items price (excluding transport and payment)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>totalPrice</strong></td>
<td valign="top"><a href="#price">Price</a>!</td>
<td>

Total price including transport and payment

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>transport</strong></td>
<td valign="top"><a href="#transport">Transport</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a></td>
<td></td>
</tr>
</tbody>
</table>

### CustomerUser

Represents an currently logged customer user

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>city</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address city name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>country</strong></td>
<td valign="top"><a href="#country">Country</a>!</td>
<td>

Billing address country

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>defaultDeliveryAddress</strong></td>
<td valign="top"><a href="#deliveryaddress">DeliveryAddress</a></td>
<td>

Default customer delivery addresses

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>deliveryAddresses</strong></td>
<td valign="top">[<a href="#deliveryaddress">DeliveryAddress</a>!]!</td>
<td>

List of delivery addresses

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>email</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Email address

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>firstName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

First name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>lastName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Last name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>newsletterSubscription</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Whether customer user receives newsletters or not

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>postcode</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address zip code

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>pricingGroup</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The name of the customer pricing group

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>street</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Billing address street name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>telephone</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Phone number

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### NotBlogArticleInterface

Represents an article that is not a blog article

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>createdAt</strong></td>
<td valign="top"><a href="#datetime">DateTime</a>!</td>
<td>

creation date time of the article

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>external</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

If the the article should be open in a new tab

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

name of article link

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>placement</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

placement of the article

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID of the article link

</td>
</tr>
</tbody>
</table>

### ParameterFilterOptionInterface

Represents parameter filter option

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>isCollapsed</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Indicator whether the parameter should be collapsed based on the current category setting

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The parameter name

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>unit</strong></td>
<td valign="top"><a href="#unit">Unit</a></td>
<td>

The parameter unit

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

The parameter UUID

</td>
</tr>
</tbody>
</table>

### PriceInterface

Represents the price

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>priceWithoutVat</strong></td>
<td valign="top"><a href="#money">Money</a>!</td>
<td>

Price without VAT

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>priceWithVat</strong></td>
<td valign="top"><a href="#money">Money</a>!</td>
<td>

Price with VAT

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>vatAmount</strong></td>
<td valign="top"><a href="#money">Money</a>!</td>
<td>

Total value of VAT

</td>
</tr>
</tbody>
</table>

### Product

Represents a product

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>accessories</strong></td>
<td valign="top">[<a href="#product">Product</a>!]!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>availability</strong></td>
<td valign="top"><a href="#availability">Availability</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>availableStoresCount</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Number of the stores where the product is available

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>brand</strong></td>
<td valign="top"><a href="#brand">Brand</a></td>
<td>

Brand of product

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>breadcrumb</strong></td>
<td valign="top">[<a href="#link">Link</a>!]!</td>
<td>

Hierarchy of the current element in relation to the structure

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>catalogNumber</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Product catalog number

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>categories</strong></td>
<td valign="top">[<a href="#category">Category</a>!]!</td>
<td>

List of categories

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>description</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>ean</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

EAN

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>exposedStoresCount</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Number of the stores where the product is exposed

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>files</strong></td>
<td valign="top">[<a href="#file">File</a>!]!</td>
<td>

List of downloadable files

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>flags</strong></td>
<td valign="top">[<a href="#flag">Flag</a>!]!</td>
<td>

List of flags

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>fullName</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

The full name of the product, which consists of a prefix, name, and a suffix

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>hasPreorder</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td>

Distinguishes if the product can be pre-ordered

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>id</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Product id

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>images</strong></td>
<td valign="top">[<a href="#image">Image</a>!]!</td>
<td>

Product images

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">sizes</td>
<td valign="top">[<a href="#string">String</a>!]</td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isMainVariant</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isSellingDenied</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>isUsingStock</strong></td>
<td valign="top"><a href="#boolean">Boolean</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>link</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Product link

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>mainImage</strong></td>
<td valign="top"><a href="#image">Image</a></td>
<td>

Product image by params

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">size</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">type</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Localized product name (domain dependent)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>namePrefix</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Name prefix

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>nameSuffix</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Name suffix

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>orderingPriority</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>parameters</strong></td>
<td valign="top">[<a href="#parameter">Parameter</a>!]!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>partNumber</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Product part number

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>price</strong></td>
<td valign="top"><a href="#productprice">ProductPrice</a>!</td>
<td>

Product price

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>productVideos</strong></td>
<td valign="top">[<a href="#videotoken">VideoToken</a>!]!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>relatedProducts</strong></td>
<td valign="top">[<a href="#product">Product</a>!]!</td>
<td>

List of related products

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoH1</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Seo first level heading of product

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoMetaDescription</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Seo meta description of product

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>seoTitle</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Seo title of product

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>shortDescription</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td>

Localized product short description (domain dependent)

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>slug</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td>

Product URL slug

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>stockQuantity</strong></td>
<td valign="top"><a href="#int">Int</a>!</td>
<td>

Count of quantity on stock

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>storeAvailabilities</strong></td>
<td valign="top">[<a href="#storeavailability">StoreAvailability</a>!]!</td>
<td>

List of availabilities in individual stores

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>unit</strong></td>
<td valign="top"><a href="#unit">Unit</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>usps</strong></td>
<td valign="top">[<a href="#string">String</a>!]!</td>
<td>

List of product's unique selling propositions

</td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>

### ProductListable

Paginated and ordered products

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>products</strong></td>
<td valign="top"><a href="#productconnection">ProductConnection</a>!</td>
<td>

Paginated and ordered products

</td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">after</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">before</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">filter</td>
<td valign="top"><a href="#productfilter">ProductFilter</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">first</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">last</td>
<td valign="top"><a href="#int">Int</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">orderingMode</td>
<td valign="top"><a href="#productorderingmodeenum">ProductOrderingModeEnum</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" align="right" valign="top">search</td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
</tbody>
</table>

### Slug

Represents entity retrievable by slug

<table>
<thead>
<tr>
<th align="left">Field</th>
<th align="right">Argument</th>
<th align="left">Type</th>
<th align="left">Description</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2" valign="top"><strong>name</strong></td>
<td valign="top"><a href="#string">String</a></td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>slug</strong></td>
<td valign="top"><a href="#string">String</a>!</td>
<td></td>
</tr>
<tr>
<td colspan="2" valign="top"><strong>uuid</strong></td>
<td valign="top"><a href="#uuid">Uuid</a>!</td>
<td>

UUID

</td>
</tr>
</tbody>
</table>
