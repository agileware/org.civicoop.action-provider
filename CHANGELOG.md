Version 1.2
===========

**Renamed actions**

* Renamed the Create/Update Group action from Create to GroupCreate. If you are using the _Create/Update Group_ action you have to reconfigure it.

**Other Major changes**

* Refactor of the action provider, so that list of available actions only contains the title, name and not the instaciated class (with all the loaded configuration, such as groups, option values etc..)
* Create Address actions now also contains parameters for street name, housenumber and housenumber suffix
* Added OptionGroupByNameSpecification for parameter and configuration specification where the name of the option value is used rather than the value.

**New actions**

* Create Contribution
* Send E-mail
* Send E-mail To Participants
* Find message template by name
* Create or Update relationship
* Create or Update an E-mail address of an contact
* Get e-mail address of an contact
* Find Individual by Name and Email address
* Get Relationship
* Update Activity Status

