Version 1.13 (not yet released)
------------

* Find contact by email or create with email and first/last name
* Find or create contact by email and first/last name
* Validate checksum
* Add 'details' parameter to CreateActivity action 
* Add action to retrieve the currently active/associated contact 
* Add 'set preferred communication method' action 

Version 1.12
------------

* Create membership: added source parameter
* Create membership (with parameters): added source parameter

Version 1.11
------------

**Changed actions**

* Create contribution: fixed issue with incorrect total amount and civicrm version 5.20
* Create contribution (with parameters): fixed issue with incorrect total amount and civicrm version 5.20

Version 1.10
------------

**New actions**

* Get Membership Type by Organization
* Map Value
* Format Value

**Changed actions**

* Link Contribution to Membership: added option to set the membership to pending.

Version 1.9
-----------

**New actions**

* Set value from parameter
* Modify Value with Regular Expression
* Set contact subtype
* Set employer
* Find organization by name
* Find or create a campaign
* Update membership
* Create contribution (with parameters)
* Update contribution
* Link contribution to membership

**New conditions**

* Contact Has Tag.
* Check multiple parameters

**Changed actions**

_Create update individual_
* New parameter source
* New parameter created date
* New parameter do not mail
* New parameter do not email
* New parameter do not phone
* New parameter do not sms

_Create update household_
* New parameter source
* New parameter created date
* New parameter do not mail
* New parameter do not email
* New parameter do not phone
* New parameter do not sms

_Create update organization_
* New parameter source
* New parameter created date
* New parameter do not mail
* New parameter do not email
* New parameter do not phone
* New parameter do not sms

_Create contribution action_
* New parameter contribution recur id
* New parameter note
* New parameter receive date
* New parameter trxn_id

_Create membership action_
* New parameter join date
* New parameter start date
* New parameter end date

_Create relationship action_
* New parameter description
* New parameter start date
* New parameter end date

_Create/update relationship action_
* New parameter description

_Send bulk mail action_
* New parameter Template Options (could be used to store mosaico template).
* Changed Group ID parameter to multiple so that a bulk mail could be send to multiple groups.


**Other changes**

* Collection specification on conditions.
* Fixed issue with custom token.
* Added manual geo coding to all actions which creates an address.

Version 1.8
-----------

* Added Latitude and Longitude fields to address actions.
* New action: 'Add attachment to bulk mail'.
* Added create a case action.
* Added action to find contact by external id.
* Added action to create a membership with the type as a parameter.
* Added action to mark a contact as deceased.
* Added action to create soft contribution.
* Added custom fields to create contribution action.
* Added condition contact has subtype.

Version 1.7
-----------

* Added action Get State/Province ID by name
* Added action Add Tag to Contact
* Fixed issue with action create and create/update relationship and related memberships

Version 1.6
-----------

* Update related membership when create relationship or create/update relationship action is performed.
* Added action Get Contact By Custom Field.


Version 1.5
-----------

* Added action Get Relationship By Contact ID
* Fixed issue with the Get Relationship action

Version 1.4
-----------

* Changed the implementation of the civicrm_container hook.
* Added action to upload files for a contact
* Added action to upload files for an event
* Added action to create an organization
* Added action to retrieve the labels from option values
* Added action to repeat an event (custom fields work since civicrm 5.15)
* Added action to get repetition of an event
* Added action to create a case
* Added action to concat (meger) a date and a time field into one
* Added action to update an event status
* Added action to get membership data by membership id

Version 1.3
-----------

*Major Changes*

* Added batch processing of actions. Also added the start and finish of a batch to the action provider class.
* Added a `getHelpText` method for retrieving a a help text for each action

*Changed actions*

* The create PDF action now supports batching and it also returns the filename and url of the generated PDF.

*New actions*

* Get contact IDs from an activity

Version 1.2
-----------

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
* Create PDF

