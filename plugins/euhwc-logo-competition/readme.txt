=== EUHWC Logo Competition ===

This plugin provides various shortcodes and an admin interface for running a
logo competition.

== Usage ==

The plugin provides 5 shortcodes, detailed below. Place any of these in the text
of a page, and they will be replaced with the required functionality:

 * [euhwc_logo_competition_upload]

   This displays a logo submission form. The user needs to be logged in for the
   form to appear. When a logo is submitted, it's year is set to the current
   year.

 * [euhwc_logo_competition_entries year=2015]

   This displays a table showing all of the logos that a user has submitted. It
   also allows them to delete submissions. The year should be set to the current
   year, to hide submissions from previous years.

 * [euhwc_logo_competition_voting year=2015]

   This displays a form that can be used to vote. Users can only vote for one
   logo. The form also allows users to change their vote at any time. The year
   should be set to the current year, to hide submissions from previous years.

 * [euhwc_logo_competition_results year=2015]

   This displays a table showing all of the logos, who submitted them, and the
   number of votes each one got. The year should be set to the current year, to
   hide submissions from previous years.

 * [euhwc_logo_competition_winner year=2015]

   This displays the winning logo and who submitted it. If there are multiple
   logos with the same number of votes they are all displayed.

All of the logos that have been submitted can be viewed from the "Logo
Comp." tab on the main menu in the admin panel. This admin page can be used to
manually edit logos if needed.

