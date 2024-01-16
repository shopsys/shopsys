# Handling failed product recalculations

Products are recalculated in batches.
Because the batch is processed always as a whole, some products may fail to be recalculated, but it's not possible to determine which products failed.
If a batch fails, the whole batch will be retried (up to three times).
If the batch still fails even after the last retry, it will be marked as failed and sent to the failure transport.
For the complete documentation of the configuration, see the [Symfony Messenger documentation](https://symfony.com/doc/current/messenger.html#retries-failures).

This transport is defined in the `config/packages/messenger.yaml` file:

```yaml
framework:
    messenger:
        failure_transport: failed

        transports:
            failed: 'doctrine://default?queue_name=failed'
```

By default, the failure transport is configured to use the `doctrine` transport,
which means that the failed batches will be stored in the database – in the `messenger_messages` table.

To see the failed batches, you can use the `messenger:failed:show` command to see an output like this:

```shell
php ./bin/console messenger:failed:show

There are 3 messages pending in the failure transport.
 ---- --------------------------------------------------------------------------------- --------------------- ----------------------------------------------------------
  Id   Class                                                                             Failed at             Error
 ---- --------------------------------------------------------------------------------- --------------------- ----------------------------------------------------------
  36   Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationMessage   2023-12-27 13:43:15   Product visibility was not found for product with ID #1.
  37   Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationMessage   2023-12-27 13:43:15   Product visibility was not found for product with ID #1.
  38   Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationMessage   2023-12-27 13:43:15   Product visibility was not found for product with ID #1.
 ---- --------------------------------------------------------------------------------- --------------------- ----------------------------------------------------------

 // Run messenger:failed:show {id} --transport=failed -vv to see message details.
```

The batches will be here split into individual messages for each product.

You can see the details of a specific message using the `messenger:failed:show` command with the `--transport` and `--id` options and see similar output:

```shell
php ./bin/console  messenger:failed:show 36 --transport=failed
There are 3 messages pending in the failure transport.

Failed Message Details
======================

 ------------- ---------------------------------------------------------------------------------
  Class         Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityRegularMessage
  Message Id    36
  Failed at     2023-12-27 13:43:15
  Error         Product visibility was not found for product with ID #1.
  Error Code    0
  Error Class   Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException
  Transport     product_recalculation_priority_regular
 ------------- ---------------------------------------------------------------------------------

 Message history:
  * Message failed at 2023-12-27 13:43:08 and was redelivered
  * Message failed at 2023-12-27 13:43:09 and was redelivered
  * Message failed at 2023-12-27 13:43:11 and was redelivered
  * Message failed at 2023-12-27 13:43:15 and was redelivered

 Re-run command with -vv to see more message & error details.

 Run messenger:failed:retry 36 --transport=failed to retry this message.
 Run messenger:failed:remove 36 --transport=failed to delete it.
```

!!! note

    If you run the command with the `-vv` option, you will see the full stack trace of the error.

You can retry the failed message(s) using the `messenger:failed:retry` command.
At each failed message, you can retry it (by answering `yes`) or delete it (by answering `no`).

If you retry the message, it will be immediately processed again.
That way you can consume all possible products and only the ones that are really not possible to process will be left in the failure transport.

If you delete the message, it will be removed from the failure transport and may not be recovered.
