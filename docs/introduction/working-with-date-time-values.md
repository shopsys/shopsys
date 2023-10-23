# Working with date-time values

Shopsys Platform internally works with dates in UTC timezone.
That is for better portability and integration with other systems.
Also, it allows you to work with time values more freely.
It's easy to implement show dates that suites your needs, for example, each user has its own timezone.

## Configuration

What timezone will be used is controlled by the implementation of `DisplayTimeZoneProviderInterface`.
Default implementation `DisplayTimeZoneProvider` takes into account parameter `shopsys.display_timezone` and convert all the dates into this timezone.
The default value is `Europe/Prague`.

## Display dates

All date values should be presented to the user from a Twig templates, where are three filters at your hand

- `formatDate`
- `formatTime`
- `formatDateTime`

All filters are aware of `DisplayTimeZoneProvider` and internally convert the values to the desired display timezone when rendering date-times.

!!! note
    PHP does not have any `Date` object and even the dates are internally instance of `DateTime` class.

## Filling the dates

When user enters any date-time value, it should be in a currently used display timezone.

Shopsys Platform comes with two FormTypes ready to handle dates properly – [`DatePickerType`](./using-form-types.md#datepickertype) and [`DateTimeType`](./using-form-types.md#datetimetype).
Both of them are aware of `DisplayTimeZoneProvider` and convert the values to the desired display timezone when user input is submitted.

**Even when you need to store only the date, it should be persisted as a `DateTime` in the database.**

> Consider following. User in Phoenix (UTC-7) creates an article and set the date of creation to some date.
This date should be visible near the article.  
Due to limitations of PHP, the value is in variable of the `DateTime` type with zero time (midnight).
Presenting such date back to the user results into date shift (one day back), because this "midnight DateTime" is converted to the display timezone.
Storing the dates in the database as a DateTime type prevents it.

## Filling the dates programmatically

When storing dates in different way than using application forms (e.g., from 3rd party application), it is necessary to convert them into UTC timezone.
This can be done like this:
```php
$dateFormOtherSource = '2020-08-24 18:30:02';
$dateTime = new \DateTime($dateFormOtherSource, new \DateTimeZone('Europe/Prague'));
$dateTime->setTimezone(new \DateTimeZone('UTC'));
```

## When to use Date

As described in previous paragraph, the best way to store date is to use `DateTime`.
There is one exception to that and it is storing of historical data like birthdays, historical events etc.
