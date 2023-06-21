# GTM Event Handlers

These methods are responsible for accepting parameters, build a GTM event object and push it to the data layer. However, they outsource both these tasks to 2 methods:

-   event factories (such as `getGtmPaymentFailEvent`)
-   `gtmSafePushEvent`, which:
    -   makes sure the event can be safely pushed to the data layer
    -   pushed the event to the data layer

Because of that, these methods are rather simple and do not require detailed documentation. Their only role is to handle events using other methods, sometimes serving as guards to check for nullability of some arguments.

For the description of **accepted parameters**, see corresponding event factory methods.
