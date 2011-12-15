Autofolders is a plugin for MODX CMS, which automatically move documents into folders based on their publication date, to keep your blog, news or other time-based documents neatly organised. 

When a document is saved, it is automatically moved into nested folders e.g. news/2011/3/document-name - if these containers don't exist, they are created.

It can be downloaded from
http://modx.com/extras/package/autofolders

Installation Instructions

1. Download and install the package via ModX's Package Management menu
2. Click on the newly created "Autofolders" plugin under the "Elements -> Plugins" tree. 
3. In the "Properties" tab, click "Default Properties Locked" to unlock the properties
4. By default, nested folders are created for years and months, based on the "publishedon" date.  You can choose to look at any other of ModX's built in date fields (such as pub_date) or a date-based TV. Click the [+] next to this field for full guidance.
5. Set the template(s) that should be organised by entering the template ID in the "template" field. For example if you have a template called "News story", put it's ID here. If you want this to apply to multiple templates, separate them by commas. 
6. Set the parent document ID in the "parent" field. This is the root folder where date-based folders will be created. For example if you have a news section with an ID of 6, put 6 here. 
7. Set the "new_page_template" field to the template ID you would like to use for newly created year/month/day containers. This may well be a template containing a getResource snippet which lists all news stories beneath it - this automatically creates a month-based archive, e.g. news/2011/3/ would list all news stories created in March 2011.
8. If you want to change the format used in the newly created containers' aliases or titles, you can set these using the various options which are labelled. Month names languages are derived from your PHP localisation settings. Menutitles will always show only one part of the date, so that menus make sense e.g. 2011 > March 
9. You can also set the level of folders required in "folder_structure" - if you make lots of posts, you might want folders created for every day (y/m/d) or if very few, just per year (y).
Please note that implementing this plugin doesn't do any permissions checks, so the plugin may move documents into areas of the site, and create folders which the ModX user permissions wouldn't allow you to create manually. 
All other container-folder properties are chosen by duplicating their parent - for example template, access permissions. 
If you want to autofolder more than one area of the site, e.g. a news section AND a blog section, you will need to DUPLICATE the plugin (giving it a suitable name such as "autofolders - blogs") and set the different template requirements in that plugin's properties.
