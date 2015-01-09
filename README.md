Product-Hunt Clone
==================

DEMO: [http://phclone.artistarter.com](http://phclone.artistarter.com)

Product Hunt clone build with OLD version (0.4) of [Quaver framework](https://github.com/millolab/quaver).

Quaver is developed by [Alberto González](https://github.com/albertogonzcat) & [MilloLab](http://millolab.com), and distributed under MIT license.

Features
--------

* Twitter Login (only)
* Profiles
* Post Projects with name, URL and description
* Up votes
* Comments
* Auto load more data on page scroll
* Multi language website	
* Admin panel (only manage language strings)


Coming soon
------------
* Stats
* Major support to multi language


Install
-------
* Import `phclone.sql` and check `Quaver/Config.php`
* REMEMBER: only works on root directory of domain or subdomain, if you want install on subfolder you must change Routes.yml and Resources paths of Config.php
* Configure your Twitter API Keys on `/auth/opauth.conf.php` ([more details](https://github.com/opauth/twitter))


External Lib
------------
* [Twig](http://twig.sensiolabs.org/) by SensioLabs.
* [YAML Component](http://symfony.com/doc/current/components/yaml/introduction.html) of Symfony.
* [PHPMailer](https://github.com/PHPMailer/PHPMailer).
* [Mandrill PHP API Client](https://mandrillapp.com/api/docs/).
* [Opauth](http://opauth.org/)
