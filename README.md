# Media Organizer
## About
This is a replacement to Griffith, an older application for linux and windows used to organize video media.

This will use the existing schema of griffith.

## Setup
Copy the .env.example file to .env and fill in the values.
Make sure to update your initial password.

Set up write permissions for the writeable folder
```
# From the main folder run the following command:
find writable/ -type d -exec chmod 777 {} \; -print
# Feel free to be proper about permissions, as 777 is not secure.
```

# New setup
Feel free to use this for a new setup, by simply creating a new database with our provided schema
```
mysql -u your_username -p griffith < _install/griffith_schema.sql

# or if you want to populate with the default values:

mysql -u your_username -p griffith < _install/griffith_data.sql
```

# Old Setup
Use an existing Griffith database.  There might be additioinal changes that need to be applied.
```
php spark migrate
```
