**Post Limit**, https://missallsunday.com

###### Description:

This mod allows you to set a per user post limit, the limit is set per day (although you can change this by changing its corresponding scheduled task). If the user is approaching his/her limit, there will be an alert informing them about it, you can configure this message with some predefined vars: {username} {limit}.

You can set a permission for members to be able to set limits on other users, admins cannot be limited.

Users who have their limit set at 0 (by default is 0) won't be limited

This mod needs PHP 7.4 or higher.

Version compatible with SMF 2.0 is available at TBA

###### Requirements

- SMF 2.1.x.
- PHP 7.4 or greater.


###### Languages:

- English

###### Changelog:

```
1.1 Dic 2024
- Add support for SMF 2.1
- Remove the global limit setting
- Add sending an alert when user is reaching his/her limit
- Re-write the logic to simplify the process of limiting an user
- Use the permissions hook to apply a limit
- All hooks, no code changes
- Add support for other mods using the integrate_after_create_post hook to make sure our check is applied after their logic


1.0.1 Jun 2012
- Support for SMF 2.0
- Intial release
```
