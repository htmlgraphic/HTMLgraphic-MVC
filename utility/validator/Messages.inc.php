<?php

/*
 * Created on Oct 24, 2008
 *
 * Interfaces for the error messages that a detector can trigger.
 */

interface LengthCheck
{

  function min_word_count_not_met_message($found, $expected);

  function max_word_count_exceeded_message($found, $expected);

  function maximum_words_allowed();

  function minimum_words_allowed();
}

interface CannotBeBlank
{

  function empty_value_message();
}

interface HTMLNotAllowed
{

  function html_detected_message();
}

interface GeneralProblems
{

  function javascript_detected_message();

  function php_detected_message();

  function contains_restricted_word_message($word);
}

?>
