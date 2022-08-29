This module provides a simple REST API with real-time exchange rates

Documentation: https://apilayer.com/marketplace/currency_data-api#documentation-tab

-----------------------

API_URL and API_TOKEN is stored in settings.php 

-----------------------

But we can either use docker-compose.yml to set the ENV variables

-----------------------

Or in the settings.php we can add it.

Also there are some contributed modules like dotENV that we can use to access ENV variables.

Below are the code for settings.php is used here:

@i.e.
$settings['api_url'] = 'https://api.apilayer.com/currency_data/live';
$settings['api_token'] = '{your_key}';
