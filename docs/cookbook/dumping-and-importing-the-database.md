# Dumping and Importing the Database
Sometimes, you may need to dump (i.e. export) and import the application database.
The typical use case is creating and restoring a database backup or transferring a database from one machine to another.

In Shopsys Platform, database dumps only consist of `public` schema.
This schema contains all the application data.
Other database objects are inside the `pg_catalog` schema (like collations or extensions), but those are not considered part of the application database and, therefore, are not included in database dumps.

## Dumping (exporting) database
The following command will create a SQL file with a database dump:
```sh
php bin/console shopsys:database:dump dump.sql
```

## Importing database into the current database
If you want to import the database into an existing application database, you first need to wipe all the data in the current database:
```sh
phing db-wipe-public-schema
```

!!! danger
    **This command wipes everything in the `public` database schema (i.e. you will lose all application data)!**

!!! hint
    In this step, you were using Phing target `db-wipe-public-schema`.  
    More information about what Phing targets are and how they work can be found in [Console Commands for Application Management (Phing Targets)](../introduction/console-commands-for-application-management-phing-targets.md)

Then you can import the dump:
```sh
psql --quiet --username=database_user --host=database_host target_database_name < dump.sql
```

Replace `database_user`, `database_host` and `target_database_name` with the correct values (from your `.env.local`).
The command will prompt you for the user's password.

## Importing database into a new database
First, set a new database name in `./env.local` file.
You can copy the environment variable name from `./env` file.

!!! note
    If you are not in the DEVELOPMENT environment, you must clear the cache via `php phing clean` for the changes to take effect.

After that, you can create the new database, including the required content of `pg_catalog` schema by executing:
```sh
php phing db-create
```

Then you can import the dump:
```sh
psql --quiet --username=database_user --host=database_host target_database_name < dump.sql
```

Replace `database_user`, `database_host` and `target_database_name` with the correct values (from your `.env.local`).
The command will prompt you for the user's password.
