## Local Organizer

### What it is

Simple list-manager designed to be self-hosted; should be modular and easily extensible, handling miscellaneous type of links, such as
*   Todo lists
*   Wishlists
*   Grocery shopping
*   Bookmarks library
*   [...]

Extra features are tag support almost everywhere, and "completion" support where it makes sense, e.g. Todos and wishlists.
Most important missing feature (for now) is pagination.

[web_fe](/media/desktop1.jpg)

### How it works

There are 3 main components:
*   PHP backend, depends on PDO, tested to run on MySQL.
*   PHP/JS frontend, depends only on jQuery.
*   Flutter frontend, tested on Android, less feature complete than the JS one, 
    but supports offline work and sync when in the same network segment as the backend.

Backend is modular, a new "list" type should be trivially insertable by adding SQL table and model class structure.

[mobile_online](/media/mobile1.jpg)
[mobile_offline](/media/mobile2.jpg)

## Ok, but Why? 

It was a "study" project, I used the parts to learn or refresh some things, for example:
*   Design patterns and testing, in the PHP backend
*   Basics of security for web apps
*   Getting a test on how to reimplement common web framework features, 
    like pretty-urls and templating

And last but not least, it's my first Flutter app and mobile frontend (and so probably pretty horrible).