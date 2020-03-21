# Test in a Docker Container

```sh
docker build -t local/composer-gpp:snapshot .

docker run --user $(id -u):$(id -g) -it --rm -v $(pwd):/workspace -w /workspace \
    local/composer-gpp:snapshot bash
    
# now in the container, run:
composer install --prefer-source --no-interaction

# check symlink
./assert-drafter-bin.sh

# test drafter binary
./vendor/bin/drafter -h

# cleanup
rm -rf composer.lock vendor
```

If you ran `composer install` within a container and the symlink in `./vendor/bin` is not working, it is most likely due to the mountpoint in the container that was used.

In the above example the code was mounted to `/workspace`, which leads to the symlink:

```
I have no name!@1078442c254b:/workspace$ ls -la vendor/bin/
total 8
drwxr-xr-x 2 1000 1000 4096 Mar 21 13:03 .
drwxr-xr-x 6 1000 1000 4096 Mar 21 13:03 ..
lrwxrwxrwx 1 1000 1000   46 Mar 21 13:03 drafter -> /workspace/vendor/apiaryio/drafter/bin/drafter
```

Now outside of the container, the absolute path `/workspace/vendor/apiaryio/drafter/bin/drafter` is probably not working for you.

To solve this, mimic your filesystem in the container, e.g.:

Your code lives at `/home/me/drafter-installer`, then mount it in your `docker run` command using `-v /home/me/drafter-installer:/home/me/drafter-installer`, use the same path for the `-w` (working directory) option.


Then the symlink will match your host filesystem and the above changed to:

```
I have no name!@1078442c254b:/workspace$ ls -la vendor/bin/
total 8
drwxr-xr-x 2 1000 1000 4096 Mar 21 13:03 .
drwxr-xr-x 6 1000 1000 4096 Mar 21 13:03 ..
lrwxrwxrwx 1 1000 1000   46 Mar 21 13:03 drafter -> /home/me/drafter-installer/vendor/apiaryio/drafter/bin/drafter
```

This symlink will also work on the host system.
