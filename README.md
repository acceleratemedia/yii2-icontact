# yii2-iContact

Creates local tables desired to match data in iContact's system and keep that data on
iContact in sync with local data such as active users, user subscriptions, updates
to email addresses in user accounts, and more.

This also requires as a prerequisite that the admin role created in brianvb/yii2-user
to be added to the auth tables and the instructions for that can be found there. One
may also manually add the admin role and not require the full set of migrations from
brianvb/yii2-user if they would prefer.

Creates a `contact` table intended to hold a the id of the user in the current system
and relate it to the id of the contact in iContact's system

```
php yii migrate --migrationPath='@vendor/brianvb/yii2-icontact/src/console/migrations', \
	--migrationTable=m_bvb_icontact \
	--interactive=0
```

By default the `contact` table has a foreign key to the table `user` where with the column
`user_id` references `user`.`id`. This is a limitation that will need to be changed if
we require a more flexible solution, or the user can implement their own migration
to change this.

There are also console commands for syncing data. They make use of the brianvb\yii2-reporting
so make sure to check the README for that with the necessary configuration setup. This extension
can enabled with the following configuration:
```
    'modules' => [
    	'i-contact' => \bvb\icontact\console\iContactModule::class
    ],
```