# Php Uml Crafter

This package generates uml from php code.
PlantUML is required to see the diagram, you can either install the plugin in your code editor or use a online tool visualize it.

## Installation

You can install the package via composer:

```bash
composer require wongdoody/php-uml-crafter
```

## Usage in Laravel projects

You can publish the config file with:

```bash
php artisan vendor:publish --tag=php-uml-crafter-config
```

This is the content of the published config file for Laravel projects:

```php
return [
    'excludeFiles' => [
    ],

    'excludeDirectories' => [
        'bootstrap',
        'config',
        'database/migrations',
        'database',
        'lang',
        'node_modules',
        'public',
        'resources',
        'routes',
        'storage',
        'tests',
        'vendor',
    ],
];
```

Scan classes command
```bash
php artisan uml-crafter:scan
```

Generate a PlantUML file command
```bash
php artisan uml-crafter:generate
```

It is also possible to pass the following options in the command:

`--project-path`: Path to the root directory of your PHP framework project as string.

`--exclude-directories`: Directories to exclude from the scanning process, if any in comma separated list.

`--exclude-files`: Files to exclude from the scanning process, if any comma separated list.

`--save-path`: Path to save the generated UML diagrams as string.

`--include-traits`: --include-traits=true scan traits and interfaces

## Usage in other PHP Frameworks

For other PHP frameworks, you can still utilize the UML Crafter package to scan classes and generate UML diagrams. Below is a generic command that can be adjusted to fit the structure and requirements of different frameworks:

Scan classes
```bash
uml-crafter:generate --project-path=your_project_directory --exclude-directories=directories_to_exclude --exclude-files=files_to_exclude --save-path=path_to_save_scanned_data --include-traits=true
```

Replace the placeholders (your_project_directory, directory_to_exclude, file_to_exclude, path_to_save_diagrams) with appropriate values according to your PHP framework's directory structure and preferences.

`--project-path`: Path to the root directory of your PHP framework project as string.

`--exclude-directories`: Directories to exclude from the scanning process, if any comma separated list.

`--exclude-files`: Files to exclude from the scanning process, if any comma separated list.

`--save-path`: Path to save the generated UML diagrams as string.

Make sure to adjust the command parameters to match your specific PHP framework's structure and requirements.

## Styling

For the uml-crafter:generate command there is also an option to change style to light mode (as default is dark mode set).
Add flag below to the command:

`--light-mode`: Change style to light mode

Example: php artisan uml-crafter:generate --light-mode=true

## Credits

- [WongDoody](https://www.wongdoody.com/)

## License

GNU GENERAL PUBLIC LICENSE (GPL-3).
