# Drupal NewsAPI Integration Module

[![Drupal 10.x](https://img.shields.io/badge/Drupal-10.x-blue.svg)](https://www.drupal.org)
[![License: GPL v2](https://img.shields.io/badge/License-GPL_v2-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html)

---

### Project Description

This project is a custom **Drupal** module designed to integrate with the **NewsAPI** service. It demonstrates how to automatically import news articles, save them to a custom content type, and display them in a dynamic, filterable list. The module is a complete solution for building a simple, automated news aggregator on a Drupal site.

### Key Features

* **Automated Data Fetching**: A custom cron job automatically pulls the latest news from the **NewsAPI** at a scheduled interval.
* **WsData Integration**: Leverages the **WsData** module for seamless API calls and data handling, including a custom decoder for the API's JSON response.
* **Programmatic Content Creation**: Dynamically creates and updates a custom "News" content type, ensuring no duplicate articles are saved.
* **Filterable Views**: Provides a **Views** page that allows users to search and filter news articles by title or keywords.
* **Better Exposed Filters**: Utilizes the **Better Exposed Filters** module to enhance the search form's user experience.

### Requirements

* **Drupal 9.x or 10.x**
* **WsData** module
* **Better Exposed Filters** module
* A **NewsAPI API Key** (available for free at https://newsapi.org/)

### Installation

1.  Place the module folder in your `modules/custom/` directory.
2.  Install the required modules via Composer.

```bash
composer require 'drupal/wsdata' 'drupal/better_exposed_filters'
```
3. Enable the modules using drush

```bash
drush en mynews wsdata better_exposed_filters
```
### Configuration and Usage

**Create the Content Type**: In the Drupal admin UI, create a News content type with fields corresponding to the NewsAPI response (e.g., field_description, field_url, etc.).

**Configure WsData**:

1. Create a WsData Server with the URL https://newsapi.org/v2/.

2. Create a WsData Call using the top-headlines endpoint and your API key. Make sure to use your custom decoder for this call.

**Run the Cron Job**: The module's mynews_cron() function handles the data import. To run it, you can either:

1. Manually run Drupal's cron from the Status Report page.

2. Configure a server-side cron job to trigger the Drupal cron URL.

3. Use a module like Ultimate Cron to set a schedule from the admin UI.

**View the News**: Once the cron job runs, navigate to the /news URL to see the articles listed on the page with a fully functional filter form.

### License

This project is licensed under the GPL-2.0 or later license.
