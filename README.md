# SouthCoast | Helpers
A Collection of helper classes for PHP

Could be installed via composer:
```BASH
$ composer require southcoast/helpers:dev-master
```

Or by manualy downloading the .zip file.




## Enviroment 
Create a file called 'sc.env'.
This file should countain the following structure:

```JSON
{
    "dev": true,
    "machine": "<Machine ID/Developer ID>",
    "...": "Any other parameters you'd like to add to your enviroment"
}
```

In your main php file load the env file.

```PHP
$path_to_env = './sc.env';
Env::load($path_to_env);
```
