Product-Hunt Clone
==================

Product Hunt clone build with [Quaver framework](https://github.com/millolab/quaver).

Quaver is developed by [Alberto González](https://github.com/albertogonzcat) & [MilloLab](http://millolab.com), and distributed under MIT license.

Features
--------

* Twitter Login (only)
* Profiles
* Post Projects with name, URL and description
* Up votes
* Comments
* Auto load more data on page scroll
* Multi language (Beta)


Comming soon
------------

* Admin panel
* Major support to multi language


Install
-------
* Import `phclone.sql` and check `Quaver/Config.php`
* Configure your Twitter API Keys on `/auth/opauth.conf.php` ([more details](https://github.com/opauth/twitter))

Note: The main page loads only 2 days but in the future will be added auto load more data on page scroll


External Lib
------------
* [Twig](http://twig.sensiolabs.org/) by SensioLabs.
* [YAML Component](http://symfony.com/doc/current/components/yaml/introduction.html) of Symfony.
* [PHPMailer](https://github.com/PHPMailer/PHPMailer).
* [Mandrill PHP API Client](https://mandrillapp.com/api/docs/).
* [Opauth](http://opauth.org/)
