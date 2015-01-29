Template file organisation
==========================

The main template dictating the overall structure is "layout.tpl".  This
includes the "utils.tpl" template, where utility functions are defined.  The
overall layout defines three extension points or blocks:
  * The "title" block is the content of the html/head/title tag.
  * The "headElements" block can be used to add other tags to the html/head
    tag, e.g. scripts or css files.
  * The "body" block is the actual content of the page, wrapped within an
    "article" tag.

Changes to the menus should be done here.

The "main.tpl" template is the main page of the addventure.  Use it as
a starting point if you want to create own templates.

If you want to put forms in your templates, you need to put a hidden CSRF field
into your forms.  Have a look at the "account_register.tpl" template on how to
to that.

The other templates have prefixes to show from which controllers they are
displayed:
  * The "account" prefix has everything about users logging in, displaying
    login errors, registration forms, etc..
  * The "doc" prefix contains the content of displaying episode chains, single
    episodes and the page for creating new ones.
  * The "recent" prefix is used for displaying the list of... well, the recent
    episodes.


Smarty Variables
================

Global variables
----------------
Every template has access to the following variables:
  * "url" contains basic URLs for resources or the site.  It's not a variable
    of itself, but a structure consisting of other information:
    * "url.base" is something like "example.com/addventure" (for resources).
    * "url.site" is e.g. "example.com/addventure/index.php" (for pages).
    * "url.current" is e.g. "example.com/addventure/index.php/doc/123", or
      (if URL rewrite is enabled) "example.com/addventure/doc/123".
    * "url.jquery" contains the resource path to the minified JQuery script.
    * "url.ckeditor" is the same, but for the CKEditor.
    * "url.bootstrap" contains itself three other URLs:
      * "url.bootstrap.js" is the Bootstrap script.
      * "url.bootstrap.css" is the core Bootstrap style.
      * and "url.bootstrap.theme" is the user-supplied theme.
  * "client" contains information about the current "User" of the
    addventure.  If the user is anonymous, it contains some default values (see
    below).

Changes to these URLs can be done in the "application/helpers/smarty_helper.php"
script.

Episode structure
-----------------
<table>
  <tr><th>Element</th><th>Meaning</th></tr>
  <tr><td>id</td><td>Episode ID for URLs.</td></tr>
  <tr><td>title</td><td>The user-supplied title.</td></tr>
  <tr><td>autoTitle</td><td>If "title" is empty, expands to "#ID", else is the "title".</td></tr>
  <tr><td>children</td><td>List of the episode's "Links", including backlinks.</td></tr>
  <tr><td>children[].subtree</td><td>Every child has an additional variable "subtree" (see below).</td></tr>
  <tr><td>backlinks</td><td>List of "Links", which point to this episode.</td></tr>
  <tr><td>comments</td><td>"Comments" for this episode.</td></tr>
  <tr><td>created</td><td>User-readable creation date.  Warning: Could be unset if migrated from an old system.</td></tr>
  <tr><td>author</td><td>An "Author".  Warning: Could be unset if migrated from an old system.</td></tr>
  <tr><td>text</td><td>The episode's text.</td></tr>
  <tr><td>hitcount, likes, dislikes</td><td>How many people have seen/liked/disliked this episode?</td></tr>
  <tr><td>preNotes</td><td>Author's notes /before/ the episode.</td></tr>
  <tr><td>notes</td><td>Author's notes /after/ the episode.</td></tr>
  <tr><td>linkable</td><td>Whether this episode can be used as a backlink target.</td></tr>
  <tr><td>parent</td><td>If this episode has a parent, this is its ID.</td></tr>
</table>

Subtree structure
-----------------
A "subtree" variable mentioned above is a simplified list of the episode's
children, allowing a "preview" of the episode's tree depth and breadth.  Every
element in the "subtree" list contains the following elements:
<table>
  <tr><th>Element</th><th>Meaning</th></tr>
  <tr><td>id</td><td>Episode ID for URLs.</td></tr>
  <tr><td>title</td><td>The episode's auto-title.</td></tr>
  <tr><td>children</td><td>A list of Subtrees for this episode.</td></tr>
</table>

Link structure
--------------
<table>
  <tr><th>Element</th><th>Meaning</th></tr>
  <tr><td>isBacklink</td><td>A boolean value, telling if this is a backlink or a child.</td></tr>
  <tr><td>fromEp</td><td>Origin episode's ID.</td></tr>
  <tr><td>title</td><td>Title of the link as seen in the "fromEp" episode.</td></tr>
  <tr><td>isWritten</td><td>Whether the destination episode has been filled with content.</td></tr>
</table>

Author structure
----------------
<table>
  <tr><th>Element</th><th>Meaning</th></tr>
  <tr><td>id</td><td>This name's internal ID.</td></tr>
  <tr><td>user</td><td>The user's id this name belongs to.</td></tr>
  <tr><td>name</td><td>The name the user chose.</td></tr>
</table>

User structure
--------------
Elements marked with "*" depend on the role of the user.
<table>
  <tr><th>Element</th><th>Default</th><th>Meaning</th></tr>
  <tr><td>userid</td><td>-1</td><td>The user's internal ID.</td></tr>
  <tr><td>blocked</td><td>false</td><td>Whether the user is blocked.</td></tr>
  <tr><td>role*</td><td>0</td><td>The user's role (see below).</td></tr>
  <tr><td>email</td><td>/empty/</td><td>The user's email.</td></tr>
  <tr><td>canCreateEpisode*</td><td>false</td><td>Whether this user can fill an unwritten episode.</td></tr>
  <tr><td>canCreateComment*</td><td>false</td><td>Can the user comment on existing episodes?</td></tr>
  <tr><td>canSubscribe*</td><td>false</td><td>Can the user subscribe to authors or episodes?</td></tr>
  <tr><td>isAdministrator*</td><td>false</td><td>true or false</td></tr>
  <tr><td>isModerator*</td><td>false</td><td>true or false</td></tr>
  <tr><td>canEdit*</td><td>false</td><td>true or false</td></tr>
  <tr><td>registeredSince</td><td>empty</td><td>The readable date and time the user has registered his account, or empty if unknown.</td></tr>
</table>

Roles:
  * 0 -- Anonymous
  * 1 -- Awaiting approval (i.e., just registered, but not actived)
  * 2 -- Registered
  * 3 -- Moderator
  * 4 -- Administrator
