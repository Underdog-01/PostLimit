**Post Limit**, https://missallsunday.com

###### Description:

This mod allows you to set a per user post limit, the limit is set per day (although you can change this by changing its corresponding scheduled task).
If the user is approaching his/her limit, there will be an alert message informing them about it.
This alert message can be configured using some predefined variable placeholders: {username} {limit}.

You can set a permission for members to be able to set limits on other users, admins cannot be limited.

Users that have their limit set to 0 won't be limited (this is the default).


###### Compatibility Requirements

- PHP 7.4 or higher
- SMF 2.1 branch


###### Languages:

- English

###### Changelog:

```
1.1.1 Mar 2025
- Language and OOP - Classes + Objects loaded with pre_load hook
- Changed lengthy admin sub-text to help-text windows
- Changed hook add/remove from XML to PHP
- Changed "Remove all data" execution to unique file and info
- Fixed missing required class files
- Fixed possible duplicate key for db insertion
- Fixed HTML in profile template
- Fixed some of the grammar
- Removed autoload hook and file

1.1.0 Dec 2024
- Add support for the SMF 2.1 branch
- Removed the global limit setting
- Added sending an alert when the user is reaching his/her limit
- Re-write the logic to simplify the process of limiting each user
- Use the permissions hook to apply a limit
- All hooks, no code changes
- Add support for other mods using the integrate_after_create_post hook to make sure our check is applied after their logic
- removed support for the SMF 2.0 branch

1.0.1 Jun 2012
- Support for SMF 2.0
- Intial release
```
