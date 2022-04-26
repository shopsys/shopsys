## Upgrade notes from SSFW team
- this file contains information about various breaking changes introduced by the SSFW team
- it is possible that some of the changes that are easy to implement, or are of a smaller nature may be skipped from these notes
- each change note contains 
  - information about the original US
  - information about the original MR
  - reason behind the changes (may be skipped for changes with obvious reasons, such as customer section)
  - most significant changes
  - tips on how to implement them
  - section about seemingly unconnected changes that was brought as a part of the MR (may be skipped if there were no such changes)

### Intorduction of the rules of hooks ES lint rule
- [FWCC-831](https://shopsys.atlassian.net/browse/FWCC-831)
- [FWCC-831 implemented rules of hooks](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/515) 
- the reasons these changes were introduced:
  - hooks are a complex topic to gather and it may be problematic for new developers working on JS SF
  - violation of the rules of hooks were becoming more and more often which could result in run-time errors
  - having a static check is going to improve the standards of the code
- most significant changes 
  - all connector methods were renamed from "getSomething" to "useSomething" (e.g. getOrderDetail to useOrderDetail)
  - usePagination hooks now uses useMemo hook internally
  - additional test package was added to allow for testing of hooks
  - useGetInternationalizedStaticUrls was renamed to getInternationalizedStaticUrls as it is not a hook and was moved from hooks folder to utils folder
- tips on how to implement these changes
  - make sure that every custom hook's name starts with a "use" prefix
    - custom hooks are all methods that call other hooks internally, and to which the rules of hooks apply
  - make sure that no hook is called conditionally (after or inside an if/else block, after early return)

### Customer section of the website
- [FWCC-439](https://shopsys.atlassian.net/browse/FWCC-439)
- [FWCC-439 - customer profile ](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/435)
- most significant changes
  - customer page was added with links to other parts of the customer section
  - edit profile page was added together with sections for
    - password change
    - delivery address modifiactions
    - personal data change
- other changes
  - PageGuard component was introduced
    - it helps with unauthorized access redirect on client
    - was created because in come cases router redirect in the component was throwing a runtime error, as it can only be used on the client-side
    - if you want this change, check for all the router.push redirects in your components, delete them, and use the page guard wrapper instead
    - the component can be easily nested to introduce multiple redirect rules
  - CustomerTypeEnum was unified to respect DRY
    - this meant changes in many places but will be more scalable and robust in the future
  - UserDataRefresher was refactored
    - because the refresher was failing to refresh the user data in the most important situation (when the user data were modified) it had to be refactored
    - now the user contact information are updated more often (as many update conditions were removed) but it works in all the known cases
    - to save some performance, the two flows that were previously put in one useEffect hook were now split into two, so changes to dependencies does not trigger code that does not rely on those dependencies
  - login/logout mechanism now uses simple handlers instead of hooks
    - because of a bug that did not allow the user to log out, the login/logout mechanism was refactored to simple handler methods
    - all changes happened only in useAuth hook and on the outside everything is the same
    - this approach should be easier to understand and to extend
  - isCompanyUser property was renamed to companyUser
    - this was done as the datapoint is called companyUser on the API
    - this should ease mapping and working with the property inside API calls/methods
  - ControllerRenderProps and TFunction typings were fixed
    - incorrect typings for ControllerRenderProps and TFunction were displaying annoying errors in some IDEs
    - even though this was not a compilation error, it was refactored so now the errors are not displayed, which should ease debugging

### Using picture tag instead of NextImage in Image component
- [FWCC-322](https://shopsys.atlassian.net/browse/FWCC-322)
- [FWCC-322 - Responsive picture element](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/538/)
- the reasons these changes were introduced:
  - we need simple generic way to display images in different sizes on different devices (as defined in images.yaml)
- most significant changes
  - the `ImageSizeFragment` now contains `additionalSizes` property with an array of all other sizes of the image
  - a new `ImageType` was introduced, this type contains array of all possible sizes of the image
  - all types that contain image now use `ImageType` instead of `ImageSizeType` (all possible sizes instead of only one)
  - the Image component now accepts `ImageType` instead of `ImageSizeType` and also new required parameter `type` which defines the specific size to be used (according to specification in images.yaml)
  - the Image component uses the html `<picture />` tag with sources instead of NextImage component

### Mobile banner for home page
- [FWCC-757](https://shopsys.atlassian.net/browse/FWCC-757)
- [FWCC-757 - Banner images - mobile vs desktop](https://gitlab.shopsys.cz/ss6-projects/ssfwcc/-/merge_requests/539/diffs)
    - the reasons these changes were introduced:
      - to allow us to use different images for desktop and mobile in home page slider
    - most significant changes
      - the definition of images in `images.yaml` has been changed to use only one (default) size for web and mobile devices and to implement the `additionalSizes` field
      - the BannerSlider component now uses the `useGetWindowSize` hook and the `getBannersSliderItemImage` method that decides, which image should be used
    - tips on how to implement them
      - you can change the breakpoint when mobile/desktop variant is to be used by changing the `desktopVariant` parameter of `getBannersSliderItemImage` method

### Transport and Payment cart mutations
- [FWCC-843](https://shopsys.atlassian.net/browse/FWCC-843)
- most significant changes
  - changes of transport and payment (together with related fields, such as personal pickup place identifier and GoPay SWIFT) are handled in separate mutations
  - cart slice of redux has been completely removed with cart UUID being moved to the user slice
  - all information about cart is now loaded from useCurrentCart hook
  - each mutation has a handler method that can be easily extended for future needs (GTM, logging)
  - cart state utility hooks and methods were removed as they were not needed anymore
- other changes
  - AddToCart result now doesn't iherit from Cart, but implements Cart as its property
  - URQL devtools exchange has been added
