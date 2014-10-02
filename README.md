# Geist

This is the software that runs [http://blog.teachboost.com](http://blog.teachboost.com).

For more information, please see
[https://github.com/mikegioia/phalcon-boilerplate](https://github.com/mikegioia/phalcon-boilerplate).

## Building and Deploying

Prior to deploying a new version you must first update the asset version in the
configuration file (config.php or a local environment file):

    'app' => array(
        ...
        'assetVersion' => 10,

Next build the css and javascript with gulp:

    $> gulp build-css
    $> gulp build-js

Push and deploy!
