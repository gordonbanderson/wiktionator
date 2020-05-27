# Wiktionator

English Wiktionary word category lookup thing. Maybe useful for some applications, I mostly used it for silly twitter
bots. Can get a random word from a category and get category info for a given word.

Can operate either on the live API or a MySQL db (preliminary)

## Usage

### From API
To use it with the **live Wiktionary API**, just instantiate `TzLion\Wiktionator\ApiWiktionator` and call whichever
methods you need to.

### From database
You can also use a copy of the Wiktionary data in a MySQL database derived from one of the official DB dumps. This is
much faster than calling the API if your application needs to do a lot of lookups. 
But it's also extremely preliminary and you'll need to provide and host your own (large) DB.

#### Creating Local Database
1) Get dumps of relevant tables
```bash
wget https://dumps.wikimedia.org/enwiktionary/latest/enwiktionary-latest-categorylinks.sql.gz
wget https://dumps.wikimedia.org/enwiktionary/latest/enwiktionary-latest-category.sql.gz
wget https://dumps.wikimedia.org/enwiktionary/latest/enwiktionary-latest-page.sql.gz
```
2) Using a newly created clean database, pipe the gzip files to create the tables.  Note this takes a few hours.
```bash
zcat enwiktionary-latest-categorylinks.sql.gz | mysql -u 'youruser' --password=yourpassword your_database
zcat enwiktionary-latest-category.sql.gz | mysql -u 'youruser' --password=yourpassword your_database
zcat enwiktionary-latest-page.sql.gz | mysql -u 'youruser' --password=yourpassword your_database
```

#### Using PHP Code
Instantiate `TzLion\Wiktionator\DbWiktionator` with an array of connection details like this:

`new TzLion\Wiktionator\DbWiktionator(['hostname', 'username', 'password', 'database_name'])`

Currently your DB will need the following tables: `category`, `categorylinks`, `page`
from the [official dumps for enwiktionary](https://dumps.wikimedia.org/backup-index.html). You can also delete columns
not in use to save space.

### DB with fallback

If you want to try the DB first but fall back to the API if it can't be connected, you can call 
`TzLion\Wiktionator\Wiktionator::getInstance` with the DB connection details as detailed above. If you call it with
no connection details, you'll always get the API version.

## Methods

These should work the same on either the API or DB versions unless otherwise specified.

* `getWordPage` **API only!** Get the full Wiktionary page for the given word.
* `isWordInCategory` Check if the given word is in the given category.
* `getWordCategories` Get all the categories the given word is in.
* `getRandomWordInCategory` Get a random word in the given category.\
Note the randomness here is much better when using a database, since the API doesn't provide this functionality natively
so we have to kind of fake it.
* `wordExistsInLanguage` Check if the given word exists in the given language.
