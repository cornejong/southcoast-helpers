# SouthCoast | Helpers

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/d07fb7f35120486cae0a04da67dd8bd2)](https://app.codacy.com/app/corne.dejong/southcoast-helpers?utm_source=github.com&utm_medium=referral&utm_content=cornejong/southcoast-helpers&utm_campaign=Badge_Grade_Dashboard)

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
