# Tasks

This is a simple mobile app to create and manage your to-do lists. It's built with the Slim
PHP micro-framework, jQuery Mobile, and the Google Tasks API.

## Getting Started

* Download the code from [JazzHub](https://hub.jazz.net/project/vvaswani/tasks-mobile). 
* Follow the directions in [this IBM developerWorks article](http://www.ibm.com/developerworks/library/mo-php-todolist-app/index.html) to download, install and configure the necessary dependencies. 
* In particular, note that you will need to separately download two libraries: the Google API PHP client and the Slim framework. These will go in the vendor/ directory. Look in the article for the suggested folder structure. 

## Google API Access
* To use the Google Tasks API, you need an API key. You can get one for free at [https://cloud.google.com/console](https://cloud.google.com/console). The article provides details and sscreenshots for the process.
* Once you have the API key, add it near the beginning of the index.php script, replacing the current placeholder text.
