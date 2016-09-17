# Drafter Installer
Install [drafter](https://github.com/apiaryio/drafter) in your php project with ease.

## Installation
The recommended way to install drafter-installer is composer:

```bash
composer require hmaus/drafter-installer
```

Add config to `extra`:  
Pass the [tag of drafter](https://github.com/apiaryio/drafter/tags) to install

```json
"extra": {
    "drafter-installer-tag": "v3.1.1"
}
```

Add it to `scripts`:

```json
"scripts": {
    "install-drafter": "Hmaus\\Drafter\\Installer::installDrafter"
}
```

You may also consider adding a reference to your update and install hooks:

```json
"scripts": {
    "post-install-cmd": [
        "@install-drafter"
    ],
    "post-update-cmd": [
        "@install-drafter"
    ]
}
```
