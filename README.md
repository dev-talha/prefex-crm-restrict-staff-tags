# Restrict Staff Tags for Perfex CRM

A lightweight Perfex CRM module that prevents non-admin staff from creating new custom tags while still allowing them to use existing tags.

This module helps keep CRM tags clean, consistent, and controlled by administrators.

---

## Features

- Restrict non-admin staff from creating new custom tags
- Allow staff to use existing tags
- Allow admins to create custom tags normally
- Show an alert when staff try to create a custom tag
- Automatically remove the blocked custom tag from the tag field
- Prevent blocked custom tags from being saved in the database
- Avoid repeated alerts when staff move to another field
- Lightweight and simple to install

---

## Alert Message

When a non-admin staff member tries to add a new custom tag, they will see:

```text
Custom tags are not allowed. If you need to add, contact your admin.
```

After the alert appears, the custom tag is automatically removed from the tag field.

---

## Use Case

Perfex CRM allows staff users to create tags in different areas such as leads, customers, tasks, projects, tickets, and other tag-supported modules.

Unrestricted tag creation can cause issues such as:

- Duplicate tags
- Spelling mistakes
- Inconsistent naming
- Messy CRM data
- Poor filtering and segmentation
- Uncontrolled reporting labels

This module solves those issues by allowing only administrators to create new tags.

---

## How It Works

The module checks tag submissions and blocks new custom tags for non-admin staff.

When a staff user enters a tag:

- If the tag already exists, it is allowed.
- If the tag does not exist, it is removed from the field.
- An alert is shown only once for the blocked tag attempt.
- The blocked tag is not saved in the database.
- Admin users can create and use tags normally.

---

## User Permissions

| User Type | Use Existing Tags | Create New Custom Tags |
|---|---:|---:|
| Admin | Yes | Yes |
| Non-admin Staff | Yes | No |

---

## Database Behavior

For non-admin staff:

- New custom tags are not inserted into the `tbltags` table.
- Existing tags can still be attached normally.
- Blocked custom tags are removed from the field and ignored during save.

You can verify the latest tags with:

```sql
SELECT * FROM tbltags ORDER BY id DESC;
```

---

## Compatibility

This module is designed for Perfex CRM installations that use the standard tag handling system.

The tag logic is commonly handled through:

- `application/libraries/App_tags.php`
- `application/helpers/tags_helper.php`

---

## Installation

1. Download the module ZIP file.
2. Extract the ZIP file.
3. Upload the module folder to:

   ```text
   modules/restrict_staff_tags/
   ```

4. Go to the Perfex CRM admin panel.
5. Navigate to:

   ```text
   Setup > Modules
   ```

6. Find **Restrict Staff Tags**.
7. Click **Activate**.

---

## Update Instructions

To update an existing installation:

1. Backup the current module folder:

   ```text
   modules/restrict_staff_tags/
   ```

2. Replace it with the updated module folder.
3. Clear browser cache or perform a hard refresh.
4. Test using a non-admin staff account.

---

## Recommended Testing

### Admin Test

1. Log in as an admin.
2. Add a new custom tag.
3. Save the record.
4. Confirm the tag is saved successfully.

Expected result:

- Admin can create new custom tags.

### Staff Existing Tag Test

1. Log in as a non-admin staff user.
2. Select or type an existing tag.
3. Save the record.

Expected result:

- Existing tag is saved successfully.

### Staff Custom Tag Test

1. Log in as a non-admin staff user.
2. Type a new custom tag.
3. Press Enter or move to another field.

Expected result:

- Alert message is shown once.
- The custom tag is removed from the tag field.
- The custom tag is not saved in the database.

---

## Folder Structure

```text
restrict_staff_tags/
├── restrict_staff_tags.php
├── install.php
├── uninstall.php
└── README.md
```

---

## Troubleshooting

### Admin panel shows a 500 error

If a 500 Internal Server Error appears after installing the module, remove or rename the module folder:

```text
modules/restrict_staff_tags
```

Example:

```text
modules/restrict_staff_tags_old
```

Then reload the admin panel.

### Alert is not showing

Check the following:

- The module is active
- Browser cache has been cleared
- You are logged in as a non-admin staff user
- The module files were uploaded to the correct path
- JavaScript files are loading correctly

### Staff can still create custom tags

Check the following:

- The module is active
- The correct module folder path is used
- The backend restriction was applied successfully
- You are testing with a non-admin staff account
- Existing browser cache is cleared

### Alert appears repeatedly

Clear browser cache and make sure the latest module assets are loaded.

The updated frontend behavior should remove the blocked custom tag immediately after the alert, preventing repeated alerts when moving to another field.

---

## Security

This module does not rely only on frontend validation.

Frontend behavior improves the user experience by showing an alert and removing blocked tags from the field. Backend validation ensures that non-admin staff cannot save new custom tags to the database even if they try to bypass the browser interface.

---

## Backup Recommendation

Before installing or updating this module, backup:

- Perfex CRM files
- Database
- Existing module folder, if updating

---

## License

This module is provided for private/custom Perfex CRM usage.

You may modify it for your own CRM installation.

---

## Disclaimer

This is a custom Perfex CRM module and is not an official Perfex CRM product.

Always backup your CRM files and database before installing or updating any custom module.
