# SouthCoast | Helpers

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/d07fb7f35120486cae0a04da67dd8bd2)](https://app.codacy.com/app/corne.dejong/southcoast-helpers?utm_source=github.com&utm_medium=referral&utm_content=cornejong/southcoast-helpers&utm_campaign=Badge_Grade_Dashboard)

A Collection of helper classes for PHP

Could be installed via composer:
```BASH
$ composer require southcoast/helpers:dev-master
```

Or by manually downloading the .zip file.

## Array Helper

### ```ArrayHelper::Map(array $map, array $array): array;```

```PHP
$map        array   The mapping 
$array      array   The original array where data should come from
Returns     array   The mapped array
```

This method allows you to map an existing array to a new one.
It has support for dot notation for use of multidimensional arrays. Both for the 'field' and the mapping keys

Accepted keys in the mapping array:
```PHP
[
    'field' => 'The key to the field from the original $array',
    'alt_field' => 'An alternative key in the original $array for when the primary field is not found or returns null',
    'value' => 'A static value, or mutation of the value, This will over ride the value of the primary field',
    'or' => 'A static value that should be used',
    'add' => 'If the field should be used or not, accepts true or false values'
]
```

Mapping Example:
```PHP
$map = [
    // The Key is the to be used key for the Array
    // The value of 'field' is the value origin
    'New_Name' => ['field' => 'old_name'],

    // Add 'value' to add custom value or value mutation
    'Email' => ['value' => 'Some Other Value'],
    
    // Add '.' separators for sub objects
    'Email.primary' => ['field' => 'email'],
    
    // Use '0' for arrays
    'Addresses.0.street' => ['field' => 'address_1_line_1'],
    
    // Get a value from a multidimensional source
    'isDefault' => ['field' => 'meta.system.default'],
    
    // Add the 'or' field to supply a value that will used if the value from the original array is not found or null
    'automated' => ['field' => 'system.automated', 'or' => 'nope, not automated']
    
    // Use the 'add' field to specify if this field should be added
    'someAwesomeField' => ['field' => 'getMyValue', 'add' => false] // Wont be added
    'someAwesomeField' => ['field' => 'getMyValue', 'add' => true] // Will be added
    
    // Add an alternative field to the mapping if the original field is missing or returned null
    'arbitraryKey' => ['field' => 'getItFromHere', 'alt_field' => 'or_from_here', 'or' => 'a default value']
];
```

Example: 
```PHP
$array = [
    'key_1' => 'value_1',
    'key_2' => 'value_2',
    'array' => [
        'a_key_1' => 'a_value_1',
        'a_key_2' => 'a_value_2',
        'a_key_3' => null
    ]
];

$map = [
    'field_one' => ['field' => 'array.a_key_1'],
    'field_two' => ['field' => 'key_1', 'add' => false],
    'field_three' => ['value' => 'new_value'],
    'field_four' => ['field' => 'array.a_key_5', 'alt_field' => 'key_2'],
    'field_five' => 'This Value',
    'array.a_field' => ['field' => 'array.a_key_3', 'or' => 'nope, no value'],
    'array.0' => ['field' => 'array.a_key_2']
];

$result = ArrayHelper::map($map, $array);

$result = [
    /* The key 'field_one' had the value of array['a_key_1'] */
    'field_one' => 'a_value_1',
    /* The key 'field_two' was not added, because 'add' was false */
    /* The key 'field_three' has a custom value */
    'field_three' => 'new_value',
    /* The key 'field_four' now bears the value of 'key_2' because 'array.a_key_5' could not be found */
    'field_four' => 'value_2',
    /* The key 'field_five' also carries a custom value */
    'field_five' => 'This Value',
    /* The key 'array' now contains an array */
    'array' => [
        /* With a key 'a_field' with the 'or' value because 'array.a_key_3' has a null value */
        'a_field' => 'nope, no value',
        /* Also a numeric key was added with the value of 'array.a_key_2' */
        0 => 'a_value_2'
    ]
]
```


## Environment 
Create a file called 'sc.env'.
This file should contain the following structure:

```JSON
{
    "dev": true,
    "machine": "<Machine ID/Developer ID>",
    "...": "Any other parameters you'd like to add to your environment"
}
```

In your main php file load the env file.

```PHP
$path_to_env = './sc.env';
Env::load($path_to_env);
```
