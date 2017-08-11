# saint-csv-parser

A PHP Library that parses the SaintCoinach generated CSV files.


### Getting started

- Download the repo
- run `composer install` in the directory
- run a command


### Commands

To run the application use:

- `php cli <command> <command> ... <command>`

The different commands are:

- `content=<content>` The content to parse, this includes:
    - Quest
    
- `limit=300` Only parse the first X rows

- `format=<format>` The format to output the files, this includes:
    - wiki (default)


### Helpful notes

Whenever a parse is done an `<Content>.offsets.json` file is generated which provides index offset list to the various columns, useful for building mapping