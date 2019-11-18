Version 1.9 (not yet released)
-----------

* Added condition Contact Has Tag.
* Added contribution recur id parameter to create contribution action.
* Added receive data parameter to create contribution action.
* Added action to set value from a parameter.
* Added action to set contact sub type.
* Added action to link contribution to membership.
* Added possibility to create a specification collection for conditions.
* Added condition to check multiple parameters.
* Added action to find or create a campaign.
* Added parameters: join date, start date, end date to the create membership actions.
* Fixed issue with custom token
* Added action to update an membership.
* Added parameters: source and created date to the actions create individual, household and organization.
* Added action to set employer.

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

