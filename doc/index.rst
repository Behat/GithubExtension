Github Extension
================

GithubExtension provides:
* A Gherkin loader, to store your Behat features directly inside
  Github issues and to load them remotely
* A Comment Manager, to auto-comment your Github issues with
  the results of features execution
* A Label Manager, to auto apply label to your Github issues with
  the result of features execution
* A Github Issue mapper to map existing feature files to remote issues

Installation
------------

This extension requires:

* Behat 2.4+

Through PHAR
~~~~~~~~~~~~

First, download phar archives:

* `behat.phar <http://behat.org/downloads/behat.phar>`_ - Behat itself
* `github_extension.phar <http://behat.org/downloads/github_extension.phar>`_
  - github extension

After downloading and placing ``*.phar`` into project directory, you need to
activate ``GithubExtension`` in your ``behat.yml``:

    .. code-block:: yaml

        default:
          # ...
          extensions:
            github_extension.phar: ~


Through Composer
~~~~~~~~~~~~~~~~

The easiest way to keep your suite updated is to use `Composer <http://getcomposer.org>`_:

1. Define dependencies in your `composer.json`:

    .. code-block:: js

        {
            "require": {
                ...

                "behat/github-extension": "dev-master"
            }
        }

2. Install/update your vendors:

    .. code-block:: bash

        $ curl http://getcomposer.org/installer | php
        $ php composer.phar install

3. Activate extension in your ``behat.yml``:

    .. code-block:: yaml

        default:
            # ...
            extensions:
                Behat\GithubExtension\Extension: ~

Configuration
-------------

First of all you will need to provide several informations to the extensions.
First two parameters can be extracted from your repository url:

    .. code-block:: yaml

        default:
            # ...
            extensions:
                Behat\GithubExtension\Extension:
                    user: <The repository owner Github username>
                    repository: <The repository name>
                    auth:
                        username: <Your Github username>
                        password: <Your Github password>
                        token: <The generated Github token>
                    write_comments: true|false
                    apply_labels: true|false

Github token generation
~~~~~~~~~~~~~~~~~~~~~~~
Generate a token like so: `curl -u<username> -X POST "https://api.github.com/authorizations" -d"{\"scopes\": [\"repo\"]}"`


Usage
-----

After installing extension, there would be 2 usage options available for you:

1. Start creating issues inside the configured Github repository.
   Write your behat features directly inside them, and be sure to delete all non-related text.
   You can benefit of the color highlight syntax feature of Github by placing your feature
   content between ``` gherkin and ```.

2. You can also create feature file (as usual) and run the following command:
   bin/behat --create-issue
   This will create Github issue for each feature files in your feature directory.
   It will also print on the console snippets than you'll have to copy/paste inside the related
   feature files in order to link them to the Github issues.

Run Feature Suite
~~~~~~~~~~~~~~~~

In order to run feature suite of a specific Github assignee, execute:

.. code-block:: bash

    $ php behat.phar --tags="assignee:<Github username>"

In order to run feature suite of a specific Github milestone, execute:

.. code-block:: bash

    $ php behat.phar --tags="milestone:<Github milestone name>"

In order to run feature suite of a specific Github label, execute:

.. code-block:: bash

    $ php behat.phar --tags="<Github label>"


``symfony2`` Mink Session
~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony2Extension comes bundled with a custom ``symfony2`` session (driver) for Mink,
which is disabled by default. In order to use it you should download/install/activate 
MinkExtension and BrowserKit driver for Mink:

.. code-block:: js

    {
        "require": {
            ...

            "behat/symfony2-extension":      "*",
            "behat/mink-extension":          "*",
            "behat/mink-browserkit-driver":  "*"
        }
    }

Now just enable ``mink_driver`` in Symfony2Extension:

.. code-block:: yaml

    default:
        # ...
        extensions:
             symfony2_extension.phar:
                 mink_driver: true
             mink_extension.phar: ~

Also, you can make ``symfony2`` session the default one by setting ``default_session``
option in MinkExtension:

.. code-block:: yaml

    default:
        # ...
        extensions:
            symfony2_extension.phar:
                mink_driver: true
            mink_extension.phar:
                default_session: 'symfony2'

Application Level Feature Suite
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You are not forced to use bundle-centric structure for your feature suites.
If you want to keep your suite application level, you can simply do it by specifiyng
proper ``feautres`` path and ``context.class`` in your ``behat.yml``:

.. code-block:: yaml

    default:
        paths:
            features: features
        context:
            class:  YourApp\Behat\ContextClass

.. note::

    Keep in mind, that ``Symfony2Extension`` relies on ``Symfony2`` autoloader for
    context discover and disables Behat bundled autoloader (aka ``bootstrap`` folder).
    So make sure that your context class is discoverable by ``Symfony2`` autoloader
    (place it in proper folder/namespace).

.. note::

    If you're using both ``Symfony2Extension`` and ``MinkExtension`` and have defined
    wrong classname for your context class, you can run into problem where suite
    will still be runnable, but some of your custom definitions/hooks/methods will
    not be available. This happens because ``Behat`` uses bundled with ``MinkExtension``
    context class instead.

    So here's what's happening:

    1. Behat tryes to check existense of FeatureContext class (default) with
       `PredefinedClassGuesser <https://github.com/Behat/Behat/blob/master/src/Behat/Behat/Context/ClassGuesser/PredefinedClassGuesser.php>`_
       and obviously can't.
    2. Behat `tries another guessers <https://github.com/Behat/Behat/blob/master/src/Behat/Behat/Context/ContextDispatcher.php#L62-66>`_
       with lower priorities.
    3. `There is one
       <https://github.com/Behat/MinkExtension/blob/master/src/Behat/MinkExtension/Context/ClassGuesser/MinkContextClassGuesser.php#L20>`_
       defined by ``MinkExtension``, which gets matched and tells Behat to use
       ``Behat\MinkExtension\Context\MinkContext`` as main context class.
        
    So, your ``FeatureContext`` isn't used really. ``Behat\MinkExtension\Context\MinkContext``
    used instead.

    So be sure to check that your suite is runned in proper context (by looking at
    paths next to steps) and that you've defined proper, discoverable context classname.

Configuration
-------------

Symfony2Extension comes with flexible configuration system, that gives you ability to
configure Symfony2 kernel inside Behat to fullfil all your needs.

* ``bundle`` - specifies bundle to be runned for specific profile
* ``kernel`` - specifies options to instantiate kernel:

  - ``bootstrap`` - defines autoloading/bootstraping file to autoload
    all the needed classes in order to instantiate kernel.
  - ``path`` - defines path to the kernel class to be requires in order
    to instantiate it.
  - ``class`` - defines name of the kernel class.
  - ``env`` - defines environment in which kernel should be instantiated and used
    inside suite.
  - ``debug`` - defines whether kernel should be instantiated with ``debug`` option
    set to true.

* ``context`` - specifies options, used to guess context class:

  - ``path_suffix`` - suffix from bundle directory for features.
  - ``class_suffix`` - suffix from bundle classname for context class.

* ``mink_driver`` - if set to true - extension will load ``symfony2`` session
  for Mink.
