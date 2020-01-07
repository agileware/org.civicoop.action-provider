# action-provider

This extension provides a base class for actions. An action is something other extensions could reuse and which is executable.
At its own this extension does not do something but it might be used by other extensions.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

See also: [CiviCRM Form-Action-Integration Architecture](https://docs.google.com/presentation/d/1Zs6UQDXBXe4K3zV5xrt8HK2R5nxttw2sGslZ82hM9us/edit?usp=sharing)

## Requirements

* PHP v5.4+
* CiviCRM > 4.7

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl action-provider@https://lab.civicrm.org/extensions/action-provider/repository/master/archive.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://lab.civicrm.org/extensions/action-provider.git
cv en action_provider
```

## Developer documentation

* [How to create an action](docs/howto_create_an_action.md)
* [How to create an action in an extenion](docs/howto_create_an_action_in_an_extension.md)
* How to use the action provider in your extension (not yet written)
* How to use the batch functionality in your extension (not yet written)


## Roadmap
