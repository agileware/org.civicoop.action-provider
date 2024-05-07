Version 1.143 (not yet released)
-------------

* Fixed action: CiviCase has contact role on a case
* Added action: "Membership: Get by Contact ID". See !162
* Added action: "Other: Format date value". See !161
* Exclude deleted contacts when searching by Email. See !164

Version 1.142
-------------

* Added action: CiviCase has contact role on a case
* Added validation: CiviCase has contact role on a case

Version 1.141
-------------

* Fixed issue with CiviCRM 5.72

Version 1.140
-------------

* Fixed issue with CiviCRM 5.68 and newer.

Version 1.139
-------------

* Added Action: Relationship: Create with one or more contacts
* Added validators for Is Empty and Parameter Has Value.
* Added Action: Activities: Create activities and assign to members of a group.
* Added Action: Send email to a group.
* Update GetEmailAddress.php: is_active does not exist for e-mail addresses. See !159
* Add Action: Mailing Event: Get Contact ID from Mailing

Version 1.138
-------------
* New action: Validate a checksum for the case client of a provided case id.
* Handle zero values in CalculateValue and ConcatenateValues. see !155
* Added action: Other: Combine Lists

Version 1.137
-------------

* Fixed memory issue when downloading a large file.
* Add Membership to Create PDF action and Tokens. See !153
* New Action: ParticipantPayment: get by Participant Id or Contribution ID

Version 1.136
-------------

* Fix for 'TypeError: count():' error in the SearchActionDesigner.

Version 1.135
-------------

* New action, Stop Execution which can be used in combination with Conditions to cause the Form Processor to stop. See !150
* Fixed action: Contribution: Update recurring contribution
* Create PDF do not create zip when batch only consists of one item.
* Added action: Event: Get availability information
* PHP 8 in quick form templates
* Removed check on related deleted entities. See #43
* Added action: Participant: Calculate Participant Fee
* Added action: Participant: Create contribution for participant
* New action, Membership: Get type by ID. Required to get the details for a Membership Type. See !149
* New action: CiviCase: Create or update a role on the case. Allows to omit permission checks and ensures that each role is created only once.

Version 1.134
-------------

* CreateContribution now also takes Financial Type as parameter (For the configuration Financial Type is still required but will function as default).
* Subscribe to mailing list, improve error reporting and parameter checking, consistent parameter label. See !147

Version 1.133
-------------

* Fixed issue with action membership extend.

Version 1.132
-------------

* fix notice in parseRawAmount
* Added action: Membership: Extend or Create.

Version 1.131
-------------

* Reduce API calls for the FindExistingPhone... and add limit 1 to the civicrm_api3 getvalue API calls. See !143
* Add num_terms to CreateOrUpdateMembership actions. See !144
* Provide Action Contribution: Create recurring contribution - adds Payment Token ID parameter to allow saving of a Payment Token. See !145
* Provide Action Contribution: Update recurring contribution - to update details of an existing recurring contribution. See !145
* Provide Action Payment Token: Get by Payment Processor - to get a Payment Token for a given Payment Processor. See !145
* Provide Action Payment Token: Create or Update - creating or updating Payment Token. See !145
* Added action Campaign: Create

Version 1.130
-------------
* Add check for optional Organisation Name and Household Name to the CreateUpdateOrganisation and CreateUpdateHouseHold actions. See !139
* Small bug fix. Email is not valid as a parameter type. See !141
* Add "external_identifier" field to contact create/update actions. See !142
* Added Metadata to actions, conditions and validations

Version 1.129
-------------

* Added action 'Activity: Get Most Recent Activity By Custom Field'.

Version 1.128
-------------

* Added action 'Contact: Update Profile Photo'
* Reworked the action 'Campaign: Get by ID'. See !127
* Added action 'Other: Round Number'. See !136
* Changed Created or Update Membership actions: status should be optional configuration, not required. See !137
* Fixed issue with create and update address. See !138

Version 1.127
-------------

* Fixed issue with action Activity: Upload file to custom field.
* Add Action: Contact: Add to multiple Groups (with list of group IDs as parameter). See !135

Version 1.126
-------------

* Compatibility with CiviCRM 5.45
*  Added CiviCase: addRole action
*  Add support for custom field type EntityReference. See !119
*  Add action to swap participant
*  Add action to add notes to entities
*  Add action to find event by custom field
*  Add action to concatenate values
*  Add action to sum up to 6 fields
*  Reworked action: 'Other: Calculate value' does not rely on `eval` anymore.
*  A generic approach to check the is_deleted flag see MR !125 and issue #38
*  Bug fixed, Create Contribution With Parameters action the Is Pay Later parameter was incorrect, see MR !128
* Contribution: Create (with parameters), provide parameter to record the Contribution Page ID for the Contribution . See !130
* Create Update Membership and Create Update Membership Type, provide Membership Status configuration. See !132

Version 1.125
-------------

* Add: Action to get information about a file.
* Add: Action to export a file (or multiple files into one zip)
* Add: Contact: Find entry of a custom group with multi values
* Add: Contact: Edit or create entry for a custom group with multi values.
* Add 'Other: Increment integer value'
* Add 'Activities: Find similar activities'

Version 1.124
-------------

* Add "Campaign: Get by ID" action. See !115
* Add "Contribution: Send confirmation / receipt" action. See !116
* Changed configuration of Phone: Phone type to be not required anymore. See !114

Version 1.123
-------------

* Fix Other: Map Value action - when the result is a single value, do not return an array, just return the value. See !112 and #36
* Fix Action "Address: Get country or state/province from ID" crashes due to incorrect parameter usage. See !113
* Fixed issue with condition contact has tag.

Version 1.122
-------------

* Enable CiviCase:Update and CiviCase:Create to update case type.
* Contact: Create or update Organization expose fields: Legal Name, Nickname, SIC Code. See !103
* Contribution: Update, provide Configuration parameters for Financial Type, Payment Instrument, Contribution Status and Is Pay Later. See !104
* Contribution: Create Line Item, add financial items for the related Contribution. See !105
* Add Payment action to provide partial or complete payment for a Contribution. See !106
* Link Contribution to Membership action should set the Skip Line Items parameter, so that Line Item actions can be responsible for creating the Line Items correctly. See !107
* Two changes to the CreateLineItem action: 1) skip creating the Line Item if the Unit Price is zero and 2) provide the ability to set the Financial Type for a Line Item. See !108
* Contribution: Create, provide parameter to record the Contribution Page ID for the Contribution . See !109
* Fix of AbstractGetSingleAction to ensure, that for custom contact references the id instead of the contact name is returned.
* Replace deprecated `CRM_Core_OptionGroup::getValue` in the send e-mail functionality. See !111
* Added Phone Type to create/update contact actions.
* Added CC/BCC to parameters of Send e-mail and Send PDF by E-mail actions.
* Added action get message template by ID
* Use PDF Format when merging multiple pdf documents into one.

Version 1.121
-------------

* Create Contribution actions, provide option to create a "Pay Later" Contribution by !102
* Added framework for validator actions.

Version 1.120
-------------

* Sets the CiviCRM default Priceset for the line item, if not already set by !100
* Work on PHP 8 and PHP 7 compatibility.

Version 1.119
-------------

* Fixed regression bug from: optionValueToLabel action incorrectly returns an array when single parameter is provided by !91

Version 1.118
------------
* round amount in amount parser

Version 1.117
------------

* Added delete case action.

Version 1.116
------------

* Added configuration option to set bulk mail to Create contact actions.

Version 1.115
------------
* add help text to format individual name, parse raw date and parse raw amount
* add Contribution:Invoice action. To be used in combination with the action 'Communication: Create PDF and send by e-mail'.

Version 1.114
------------
* change format individual name with test results AIVL
* fix text label in action for country iso code
* format phone allows + in first character

Version 1.113
------------
* fix format individual name for more name parts
* output format phone to string so preceding 0 does not disappear

Version 1.112
------------

* add contact action to format individual name - fixes #32, funded by Amnesty International Vlaanderen
* add contact action to format phone string into numbers only - fixes #33, funded by Amnesty International Vlaanderen

Version 1.111
------------

* add generic action to parse raw amount, fixes #30, , funded by Amnesty International Vlaanderen
* Add parameter to Contact: Get e-mail address action to fallback to the active email address when the location type email address is not found.

Version 1.110
------------
*
* add generic action to parse raw data, fixes #29, funded by Amnesty International Vlaanderen
* Communication:Sendmail Enabled sending email for a contact with no email when an alternative email is provided.
* Add campaign parameter to Communication:Sendmail action. The email is added to the campaign.
* OptionValueToLabel action incorrectly returns an array when single parameter is provided by !91
* Custom field using option group returns incorrect values when option values are non-numeric  by !90
* PHP 8.1 compatibility - set return types on iterator methods by !92
* Changes the Action: Contact: Get By Email* to search any email address instead of just primary email by !94 and !95
* Added the parameter Preferred Communication Method to the Action Contact: CreateUpdateIndividual


Version 1.109
------------

* Implemented the condition parameter configuration screen for the condition All are (not) empty in the search action designer.

Version 1.108
------------

* Fix for https://lab.civicrm.org/extensions/form-processor/issues/47
* !88 Activity: Create/Update does not expose Duration and Location fields
* !87 PHP 8.1 compatibility - jsonSerialize must have a return type

Version 1.107
------------

* Fixed regression bug. After performance update.

Version 1.106
------------

* Added location type to Send E-mail and Send PDF by Email actions. With a fall back to primary email address.

Version 1.105
------------

* Performance improvements.

Version 1.104
------------
* Added action CiviCase: Role Group Sync
* Added action CiviCase: Get Role Group
* Added action Other: Logged-in Contact Id
* Added parameter source to action Create Participant

Version 1.103
------------
* Added action GetRelationshipById.
* Added action GetEmployer (Retrieves the employer_id and name for a contact)
* Make subject optional in the Civi:Case Update Action.
* Added condition "Parameter is Not"
* Fixed issue with Send PDF By Email action and token replacements for participant tokens.

Version 1.102
------------

* Fixed issue with attachment and sending mails in batch mode.

Version 1.101
------------

* !83 Added Condition: Contact has Activity
* !84 CiviCase Create and CiviCase Update actions provides Case Subject as an input field, but should be provided as a parameter option field
* Added action CiviCase get most recent activity of a case

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

