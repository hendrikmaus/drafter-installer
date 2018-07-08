# Known Issue

This test case is flawed when running in a pull-request environment.

The `composer.json` file will instruct the package manager to fetch the defined version,
which is not the code contained in the pull-request.

If you have time to make it better, we'd appreciate it.