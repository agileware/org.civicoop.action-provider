Version 1.101 (not yet released)
------------

Version 1.100
------------

* !82 Add nick_name support to CreateUpdateIndividual
* Fix for #25: let mosaico alter the mail content and other extensions which use hook_alter_mailContent

Version 1.99
------------

* Fixed UpdateGroupSubscriptions action fails !24
* Use CiviCRM "standard" labels for address fields. Add missing Supplemental Address 2, 3 fields  by !77
* Remove deprecated preferred_mail_format by !78


Version 1.98
------------

* Fixed issue with Create PDF action and CiviCRM and Drupal 9.

Version 1.97
------------

* Fixed issue in action Activity get most recent activity.

Version 1.96
------------

* Added action: Contact restore from trash.
* Added action: Contact: Block Communication (set all do not fields)

Version 1.95
------------

* Fixed issue with sending e-mails.

Version 1.94
------------

* Don't allow users to populate file custom fields
* Fixed regression bug #23
* Added action: Mailing Event Unsubscrive from Mailing list.

Version 1.93
------------

* Fixed #22: Typo in Activity: Get most Recent activity. And loading of configuration of an action when it contains multiple values. Such as activity type.
* Fixed bug with action Contact: add tags.
* Added is_opt_out parameter to CreateUpdateIndividual action see !73
* Added action Contact: Remove From Group see !74

Version 1.92
------------

* Added preferred language to action Create/Update Individual.
* Added action Other: Replace text.
* Error handling at the create activity action

Version 1.91
------------

* Convert tokens with the token processor (only if civicrm is newer then version 5.42).

Version 1.90
------------

* Added condition: 'Compare Parameter with an regular Expression'
* Added PDF Page Format to actions get message template by name and the create pdf and send pdf by email actions.

Version 1.89
------------

* The SendEmail action extended with from_email and from_name parameters
* Added action: Relationship Validate Checksums

Version 1.88
------------

* Fix for #21 Process mapping with customfields in the searchactiondesigner
* Added Action Update Campaign

Version 1.87
------------

* Added option to disable the sending of confirmation e-mails with the Contribution Repeat Transaction action

Version 1.86
------------

* Fixed issue action Contribution: repeat recurring contribution.

Version 1.85
------------

* Fixed issue action Contribution: repeat recurring contribution.

Version 1.84
------------

* Support nickname on create/update individual actions.
* Add action Contribution: Repeat recurring contribution.

Version 1.83
------------

* Added action Update Case status.
* Added action Update Case
* Added Case ID to create and update relationship actions
* Added extra fields to the action Create Case.
* Fix "Create or Update Relationships" to handle symmetric relationships.
* Refactoring SpecificationBag->getSpecificationByName. Processing remarks !55
* Support start/end dates on relationship create/update; related fixes by !62
* Address Component Lookup action by !63

Version 1.82
------------

* Added Action 'Activity: Assign'.
* Added Action 'Other: Replace entity tokens in HTML'.
* Fixed #18: activity contact not correctly returned for 1 record type only

Version 1.81
------------

* Changed action 'Contact: Edit communication styles' and made the communication style also available as a parameter.
* Action 'Activity: Create/Update' has now priority in the configuration (with default Normal).

Version 1.80
------------

* Added status to the create recurring contribution action.

Version 1.79
------------

* Added action 'Contribution: create recurring contribution'
* Added Contribution Recur ID as parameter to the Send E-mail, Create PDF and Send PDF by Email action.

Version 1.78
------------

* Added action 'Contact: Edit communication styles'.
* Fixed SpecificationBag->getSpecificationByName function to support custom fields.
* support note date on 'Create Note'  by !53

Version 1.77
------------

* Regenerate auto-generated code (civix) for PHP7.4 compatibility.
* Use full class path in action_provider.php instead of a "use" statement as classloader has not always completed when this file is read (fixes failures on some Wordpress site when run via CRON).
* Added new action 'Other: Retrieve Geocoding (Latitude, Longitude)'. This action uses the CiviCRM geocoding settings.

Version 1.76
------------

* Added action Participant Get by Custom Field.
* Added condition all are equal.

Version 1.75
------------

* Fixed issue with Create and Update participant actions and custom fields.

Version 1.74
------------

* Add case_id to the GetActivity action.
* Extended Create/Update Individual with Is Deceased and Deceaded Date.
* Fixed issue with custom fields and Participant: register contact for event action.

Version 1.73
------------

* Fixed issue with action create contribution and non US locale.

Version 1.72
------------

* Fixed issue with Contact: Create/Update Individual when only an email address is provided.

Version 1.71
------------

* Added action: Contribution: get by custom field.
* Added possibility to give a group name at the Group: Add to Group action.

Version 1.70
------------

* Updated action Create/Update activity with looking activity type when none is given and an existing activity is updated.

Version 1.69
------------

* New Action: Create Line Item !47
* New action 'Other: Resolve/map input to option values' !46
* Fixed regression bug with output values from get entity by id actions. Such as Get Case By Id where the case type wasn't set correctly.
* Added alternative recipient email as a parameter to the Send E-mail and Send PDF by Email actions.

Version 1.68
------------

* Changed actions Send Email and Send pdf by email in such a way that one can set a custom from name and e-mail address. And also an alternative recipient.

Version 1.67
------------

* Regenerated action_provider.civix.php (removing curly braces PHP 7.4 problem)
* Fixed issue with Contact: Get contact by custom field #15
* Fixed issue with Contact: Create/Update Individual when only an email address is provided.

Version 1.66
------------

* Fixed issue with get participant actions.
* Fixed issues with Update Participant By ID action.

Version 1.65
------------

* Changed action Contact: get or create by email and names. So that it also accepts organization name and household name.
* Create action to send SMS by !142

Version 1.64
------------

* The checksum value is now also returned by ValidateChecksum
* Added action 'Contact: Get or create organization by name'
* Fixed bug when no organization is found at the action Contact: Get organization by name.

Version 1.63
------------

* Fixed compatibility issue with send pdf and civicrm version 5.35 and newer.

Version 1.62
------------

* Added action Implode List to the other actions.
* Ability to add schedule date for sending emails  by !39
* New action to add multiple contacts to a group by !40

Version 1.61
------------

* Improved angular performance.

Version 1.60
------------

* Fixed regression issue with custom fields on a lot of actions.

Version 1.59
------------

* Fixed issue with custom fields with Create Event and Create Event From Template actions.

Version 1.58
------------

* Improved issue with action provider and CiviCRM version 5.14 whlist keeping the compatibility with civicrm version 5.34 and drupal 9.
* Add Event Full Text to Create Event and Create Event From Template actions.

Version 1.57
------------

* Fixed issue with action provider and CiviCRM version 5.14 whlist keeping the compatibility with civicrm version 5.34 and drupal 9.

Version 1.56
------------

* Improved performance of various actions by using the config container which caches the api calls of custom groups and custom fields.
* Add is_hidden as an option for Create Group action !38
* Add Max Participants and Waitlist Text to Create Event and Create Event From Template actions.

Version 1.55
------------

* Fixed issue with attachments and send e-mail action.
* Fixed issue with tokens generated by the Activity Token extension.

Version 1.54
------------

* Compatibility fix for Symfony ^3.4 ^4.0.
* Fixed issue with create pdf action and source contact id.
* Send PDF by E-mail action can also send additional attachments.
* The create note action does not require any extra permissions anymore.

Version 1.53
------------

* New action to create an event from a template !35
* Made plain text non required on the Send Email and Send PDF by email actions.

Version 1.52
------------

* Changed Add tags action so it could retrieve the tag list as a parameter.

Version 1.51
------------

* Added Formal Title to the action Contact: Create/Update Individual
* Added action Contact: Create Note
* Added action Contact: Add tags
* Added action Contact: Sync tags

Version 1.50
------------

* Create/Update group action also checks on the title. (#11)
* Added summary to create event action (!32 and #10)

Version 1.49
------------

* Added attachment parameter to send e-mail.

Version 1.48
------------

* Fixed issue with action get activity and the retrieval of the target and assignee contact ids.

Version 1.47
------------

* Added custom groups to the config class.

Version 1.46
------------

* Improved performance of angular user interface.
* Allow clearing selection of single-value parameters. (!30)
* Added Action: Activity: Upload file to a custom field  (!29)
* Fixed issue 9 where existing email is added to contact

Version 1.45
------------

* Added action: Reuse existing file

Version 1.44
------------

* Fixed issue with action Other: Show option value(s) as their Label(s) when the value is empty.

Version 1.43
------------

* Added action Contribution: Get Data.

Version 1.42.3
------------

* Fixed regression bug with compare parameter value condition.

Version 1.42.2
------------

* Fixed regression bug in action provider with multiple value custom fields and the AbstractGetSingleAction.

Version 1.42.1
------------

* Fixed regression bug in action provider.

Version 1.42
------------

* Escaping configuration values of custom fields and custom groups. In case a @ or % sign is present in the help or description text
  of the custom field or custom group.
* Fixed issue with multiple values in a custom field and AbstractGetSingleAction.

Version 1.41
------------

* Added action 'Participant: Register contact for an event' and renamed the others to 'Participant: Create/Update event registrations'.
  The action 'Participant: Register contact for an event' has the ability to only register a contact for an event if that person
  is not already registered.

Version 1.40
------------

* Added output to the action 'Communication: Create PDF and send by e-mail'.
* The action 'Communication: Get message template by name' only shows message templates and not the workflow templates.

Version 1.39
------------

* Improved caching of custom fields.
* Added prefix, suffix and contact sub type as a parameter to the action Contact: create/update individual.
* Added contact sub type to action Contact Create Organisation and Contact Create Household.

Version 1.38.1
------------

* Fixed regression bug with the caching of custom fields.

Version 1.38
------------

* Performance improvement by caching the custom fields in a code file and by providing a mechanism to cache configs.

Version 1.37.1
------------

* Fixed issue with html and the replace tokens action.

Version 1.37
------------

* Added action for replacing tokens in an HTML text.
* Added functionality to use wysiwyg editor as a configuration
* Made configuration of an action more generic when used with search action designer (or other quick form implementations)

Version 1.36
------------

* Fixed issue with reply_to email addresses on send e-mail action.
* Added condition configuration for quick form implementation
* Added mapping configuration for quick form implementation

Version 1.35
------------

* Added action "Participant: Register contact for an event (with status and role as parameter)"

Version 1.34
------------

* Added condition for Comparing Parameter value (greater than, lesser then etc.).

Version 1.33
------------

* Create PDF action: added subject parameter for the created activity.

Version 1.32
------------

* Fixed issue with AbstractGetSingleAction and retrieval of custom fields.

Version 1.31
------------

* Added Exact Match option to action 'Contact: Get Individual by name and email'.
* Added action: Participant: Send Registration Confirmation
* Added option to skip creation of line items at the create contribution action.

Version 1.30
------------

* Added participant to Send Email action.

Version 1.29
------------

* Fixed issue with tokens in CreatePDF action.

Version 1.28
------------

* Added case, activity and contribution parameter to send pdf and create pdf action.

Version 1.27
------------

* Fixed issue with Validate Checksum of Role on case when a case has multiple persons with the same role.

Version 1.26
------------

* Added CC/BCC to Send E-mail adn Send PDF actions.

Version 1.25.4
------------

* Fixed issue with tokens and send e-mail.

Version 1.25.3
------------

* Fixed issue with retrieving custom field with action Get Contact By ID.

Version 1.25.2
------------

* Fixed issue with retrieving custom field with action Get Contact By ID.

Version 1.25.1
------------

* Fixed issue with action Get Contact By ID. That action was broken.

Version 1.25
------------

* Added action to validate checksum of role on a case.
* Added action to subscribe/unsubsribe a contact to a set of groups.
* Added action to retrieve the list subscribed groups of a contact.

Version 1.24
------------

* Added more fields to contribution creation (fee amount, check number, receipt date, etc.)
* Added action Get Country By Id.
* Make Activity Date optional - defaults to 'now'
* Fixed issue with country custom fields.

Version 1.23
------------

* Added action to retrieve the Download file link
* Made output specification for get action more generic and reusable.

Version 1.22.2
--------------

* Fixed issue with CiviCase: update custom data

Version 1.22.1
--------------

* Fixed issue with Generate Checksum action.

Version 1.22
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

