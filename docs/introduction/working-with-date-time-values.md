# Working with date-time values

Shopsys Platform internally works with dates in UTC timezone.
That is for better portability and integration with other systems.
Also, it allows you to work with time values more freely.
It's easy to implement show dates that suites your needs, for example, each user has its own timezone.

## Configuration

What timezone will be used is controlled by the implementation of `DisplayTimeZoneProviderInterface`.
Default implementation `DisplayTimeZoneProvider` takes into account domain timezone setting from `config/domains.yaml` file and convert all the dates into this timezone.
`DisplayTimeZoneProvider` also provides the timezone for the admin that is set using `shopsys.admin_display_timezone` parameter.

## Display dates

### In the admin

All date values should be presented to the admin from Twig templates, where are three filters at your hand

-   `formatDate`
-   `formatTime`
-   `formatDateTime`

All filters are aware of `DisplayTimeZoneProvider` and internally convert the values to the desired admin display timezone when rendering date-times.

!!! note

    PHP does not have any `Date` object and even the dates are internally instance of `DateTime` class.

### On the storefront

The dates are sent to the storefront in UTC timezone from the frontend API.
The domain timezone is provided for the storefront via the `settings.displayTimezone` GraphQL query.
Custom `useFormatDate` hook is then used for proper date formatting while taking the domain timezone into account.
As a safety net, there is `publicRuntimeConfig.domains.fallbackTimezone` in the `next.config.js` file, which is used when the domain timezone is not available via API.

## Filling the dates

When the admin enters any date-time value, it should be in a currently used admin display timezone.

Shopsys Platform comes with two FormTypes ready to handle dates properly – [`DatePickerType`](./using-form-types.md#datepickertype) and [`DateTimeType`](./using-form-types.md#datetimetype).
Both of them are aware of `DisplayTimeZoneProvider` and convert the values to the desired admin display timezone when the input is submitted.

**Even when you need to store only the date, it should be persisted as a `DateTime` in the database.**

> Consider following. User in Phoenix (UTC-7) creates an article and set the date of creation to some date.
> This date should be visible near the article.  
> Due to limitations of PHP, the value is in variable of the `DateTime` type with zero time (midnight).
> Presenting such date back to the user results into date shift (one day back), because this "midnight DateTime" is converted to the display timezone.
> Storing the dates in the database as a DateTime type prevents it.

## Filling the dates programmatically

When storing dates in different way than using application forms (e.g., from 3rd party application), it is necessary to convert them into UTC timezone.
This can be done like this:

```php
$dateFormOtherSource = '2020-08-24 18:30:02';
$dateTime = new \DateTime($dateFormOtherSource, new \DateTimeZone('Europe/Prague'));
$dateTime->setTimezone(new \DateTimeZone('UTC'));
```

## Exceptions

### When to use Date

As described in previous paragraph, the best way to store date is to use `DateTime`.
There are exceptions to that, e.g. storing of historical data like birthdays, historical events etc.
We use this approach for storing internal days and holidays (represented by `Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay::$date`).
In this case, the holiday date is bound to a particular domain, and it is not necessary to convert it to the UTC timezone.

### Storing time values

A store opening and closing times (`Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRange`) are persisted in the database as string values, without any relation to a particular date.
The values represent the information like "On mondays, the store is open from 8:00 to 18:00" and this is not affected by a timezone as it is always considered as a local time of the particular store.
