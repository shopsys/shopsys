# Dump variables
Shopsys Framework uses Symfony with its VarDumper. 
In addition to the standard function `dump()`, we implement the function `d()`, which you can pass any number of attributes where each one is dumped.

Dumped variables are primarily available in the profiler. 

Since we do not want to dump variables into the JSON response when developing the frontend API, the dumps are directed to the Symfony dump server. (https://symfony.com/doc/current/components/var_dumper.html#the-dump-server)

You can find the debug settings in the file `config/packages/dev/debug.yaml`:

Start the server with the `server:dump` command and whenever you call the `d()`, the dumped data will not be displayed in the output but sent to that server, which outputs it to its own console or to an HTML file.
```
php bin/console server:dump
```
