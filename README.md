# Behat Github Extension

[![Build Status](https://secure.travis-ci.org/Behat/GithubExtension.png)](http://travis-ci.org/Behat/GithubExtension)

This extension provides a gherkin loader which allows you to load your feature suite from Github issues.
It also allows you to:
 - automatically comment your issue/feature with the result of the feature suite execution.
 - automatically put a label on your issue/feature to distinguished which feature has passed and which one is broken.

We find it really useful to communicate with our client around the features they want us to implement.

## Installation
Create a composer.json file with following content:
``` json
{
    "require": {
        "behat/behat":            "2.4.*",
        "behat/github-extension": "dev-master"
    },

    "config": {
        "bin-dir": "bin"
    },

    "minimum-stability": "dev"
}

```

And run `php composer.phar install`

### Public repository
Create a `behat.yml` file at the root of your project with the following content:
```yml
default:
    paths:
        bootstrap: tests/behat
    extensions:
        Behat\GithubExtension\Extension:
            user:       <the user who owns the github repository>
            repository: <the name of the repository>
```

### Private repository
Generate a token like so: `curl -u<username> -X POST "https://api.github.com/authorizations" -d"{\"scopes\": [\"repo\"]}"`
## Usage
If you just want to run the whole test:
```
bin/behat
```

If you want to run a specific feature:
```
bin/behat https://github.com/<user>/<repository>/issues/<issue number>
```

Assignee, milestones and labels are automatically added as tag to the feature so you can run the features assigned to a personn:
```
bin/behat --tags="@assignee:<github username>"
```

Or the features inside a milestone:
```
bin/behat --tags="@milestone:sprint_1"
```

Or the features labelled with "Urgent":
```
bin/behat --tags="@Urgent"
```
