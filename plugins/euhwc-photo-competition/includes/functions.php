<?php
/*
Copyright © 2013 Alex Collins
This work is free. You can redistribute it and/or modify it under the
terms of the Do What The Fuck You Want To Public License, Version 2,
as published by Sam Hocevar. See the COPYING file for more details.
*/

/** Get the number of votes for a given logo */
function euhwc_logo_competition_num_votes($logo) {
  return count(get_post_meta($logo->ID, 'logo_competition_vote', false));
}

/** Comparison function for sorting an array of logos in descending order of votes */
function euhwc_logo_competition_results_cmp($a, $b) {
  $na = euhwc_logo_competition_num_votes($a);
  $nb = euhwc_logo_competition_num_votes($b);
  return $nb - $na;
}

?>
