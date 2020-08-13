Version 1.22 (not yet released)
------------

* Added action "Contact: Generate Checksum" 

Version 1.21
------------

* Added action to override membership status.
* Added action "CiviCase: Update custom data"
* Added action "Activity: Upload Attachement"
* Added action "CiviCase: Upload file to custom field"

Version 1.20
------------

* Added action to explode a list into an array.
* Added condition to check whether an array contains a specific value.
* Added action Contact Has Subtype
* Added action to remove a subtype from a contact.
* Added action to update an existing relationship with an ID.
* Fixed issue with clearing address fields on Create/Update address.
* Added action to Find Contact with Role on the Case.
* Added action to get case data.
* Added Case ID as a parameter to the create/update activity action.
* Added action for Using Primary address of a related contact
* Added Mix of Not Empty/Empty to condition Parameters are (not) empty.
* Added Job Title to Create or Update Individual.
* Implemented functionality to group parameter fields. (Requires also an update to form processor and/or search action designer)
* Refactored action list and named the actions in a consequent manner.

Version 1.19
------------

* Fixed #8 - saving output mapping of a condition.
* Added action to Send PDF By E-mail.
* Added action to set/update a contact's preferred communication methods
* Added action to Get the Most recent activity (!16)
* Create activity takes now an activity type as a parameter or as a configuration option (!17)
* Fixed a regression bug with updating custom multiple select field (!19).
* Fixed issue with Custom File Upload for events.
* Added action to find contact by e-mail.
* Added action to get the membership status data.

Version 1.18
------------

* Added action Get Participant Data by Id.
* Added action Update Participant by Id.

Version 1.17
------------

* Added action 'Get relationship type ID by name'.
* A parameter mapping could be mapped to multiple fields. This is useful for Activity Target Contact(s).
* Changed the Create or Update Activity action so that it accepts subject as parameter. It also accepts multiple activity targets.

Version 1.16
------------

* Added action 'Move contribution to another contact'.

Version 1.15
------------

* Added parameters _From E-mail_, _From Name_ and _Reply To_ to the action **Send Bulk mail**

Version 1.14
------------

* Add "Modify date value" action
* Add "Save Max Contact ID" action
* Fixed issue with Create Relationship action.

Version 1.13
------------

* Find contact by email or create with email and first/last name
* Find or create contact by email and first/last name
* Validate checksum
* Add 'details' parameter to CreateActivity action
* Add action to retrieve the currently active/associated contact
* Add 'set preferred communication method' action
* Add 'Create relationship (with relationship type parameter)' action
* Add 'Subscribe to mailing list' action
* Add 'Confirm mailing list subscription' action
* Add 'Link contribution to participant' action
* Changed Upload Custom File Field and Add Attachment action so it also accepts a URL for the attachment.
* Add "Calculate value (binary arithmetic operation)" action

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

