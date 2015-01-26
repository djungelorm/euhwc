=== EUHWC Logo Competition ===

This plugin provides various shortcodes and an admin interface for running a
logo competition.

== Usage ==

The plugin provides 4 shortcodes, detailed below. Place any of these in the text
of a page, and it will be replaced with the appropriate functionality.

 * [euhwc_logo_competition_form]

   This will display a submission form. The user needs to be logged in for the
   form to appear. When a logo is submitted, it's year is set based on the time
   of submission.

 * [euhwc_logo_competition_entries year=2015]

   This displays a table showing all of the logos that a user has submitted. It
   also allows them to remove submissions. The year should be set to the current
   year, to hide a user's submissions from previous years.

 * [euhwc_logo_competition_voting year=2015]

   This displays a form that can be used to vote for a logo. Each user can vote
   for one logo. The form also allows users to change their vote. The year
   should be set to the current year, to hide submissions from previous years.

 * [euhwc_logo_competition_results year=2015]

   This displays a table showing all of the logos, who submitted them, and the
   number of votes each one of them got. The year should be set to the current
   year, to hide submissions from previous years.

 * [euhwc_logo_competition_winner year=2015]

   This displays just the winner(s) of the logo competition. If there are
   multiple logos with the same number of votes they are all displayed.

All of the logos that have been submitted can be viewed and edited manually from
the "Logo Comp." tab on the main menu in the admin panel.
