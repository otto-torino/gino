<?php
/**
 * @file datepicker.php
 * @brief Javascript per attivare il DatePicker
 * 
 * @copyright 2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 */
namespace Gino;

/*
 * Documentation - http://api.jqueryui.com/datepicker/
 * 
 * Follow some options.
 *
 * # First day of the week
 * Set the first day of the week: Sunday is 0, Monday is 1, etc.
 * { firstDay: 1 }
 *
 * # Names of the days
 * 1. The list of long day names; default:
 * { dayNames: [ "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday" ] }
 *
 * 2. The list of minimised day names for use as column headers within the datepicker; default:
 * { dayNamesMin: [ "Su", "Mo", "Tu", "We", "Th", "Fr", "Sa" ] }
 *
 * 3. The list of abbreviated day names; default:
 * { dayNamesShort: [ "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat" ] }
 *
 * # Names of the months
 * 1. The list of full month names; default:
 * { monthNames: [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ] }
 *
 * 2. The list of abbreviated month names, as used in the month header on each datepicker; default:
 * { monthNamesShort: [ "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ] }
 *
 * # Format Date
 * Display date feedback in a variety of ways.
 * { dateFormat : 'dd/mm/yy' }
 *
 * Format Options:
 * "mm/dd/yy" (Default)
 * "yy-mm-dd" (ISO 8601)
 * "d M, y" (Short)
 * "d MM, y" (Medium)
 * "DD, d MM, yy" (Full)
 * "'day' d 'of' MM 'in the year' yy" (With text)
 *
 * # Animations
 * You can use different animations when opening or closing the datepicker.
 * { showAnim: '' }
 *
 * Animation Options:
 * "show" (Show (default))
 * "slideDown" (Slide down)
 * "fadeIn" (Fade in)
 * "blind" (Blind (UI Effect))
 * "bounce" (Bounce (UI Effect))
 * "clip" (Clip (UI Effect))
 * "drop" (Drop (UI Effect))
 * "fold" (Fold (UI Effect))
 * "slide" (Slide (UI Effect))
 * "" (None)
 *
 * # Display Button Bar
 * Display a button for selecting Today’s date and a Done button for closing the calendar.
 * { showButtonPanel: true }
 *
 * # Display Month and Year Menus
 * Show month and year dropdowns in place of the static month/year header to facilitate navigation through large timeframes.
 * { changeMonth: true, changeYear: true }
 *
 * # Display Multiple Months
 * Set the numberOfMonths option to an integer of 2 or more to show multiple months in a single datepicker.
 * { numberOfMonths: 3 }
 *
 * # Icon Trigger
 * Click the icon next to the input field to show the datepicker.
 * Set the datepicker to open on focus (default behavior), on icon click, or both.
 * {
 *      showOn: 'button',
 *      buttonImage: '".$ico_calendar_path."',
 *      buttonImageOnly: true,
 *      buttonText: 'Select date'
 * }
 *
 */

/**
 * @brief Javascript del DatePicker
 * @param string $id valore id dell'input tag
 * @param array $options array associativo di opzioni
 * @return string
 */
function getDatePicker($id, $options=[]) {
    
    if(!is_array($options)) {
        $options = [];
    }
    
    $days = "['"._("Domenica")."', '"._("Lunedì")."', '"._("Martedì")."', '"._("Mercoledì")."', '"._("Giovedì")."', '"._("Venerdì")."', '"._("Sabato")."']";
    $days_min = "['"._("Do")."', '"._("Lu")."', '"._("Ma")."', '"._("Me")."', '"._("Gi")."', '"._("Ve")."', '"._("Sa")."']";
    $days_short = "['"._("Dom")."', '"._("Lun")."', '"._("Mar")."', '"._("Mer")."', '"._("Gio")."', '"._("Ven")."', '"._("Sab")."']";
    
    $months = "['"._("Gennaio")."', '"._("Febbraio")."', '"._("Marzo")."', '"._("Aprile")."', '"._("Maggio")."', '"._("Giugno")."', '"._("Luglio")."', '"._("Agosto")."', '"._("Settembre")."', '"._("Ottobre")."', '"._("Novembre")."', '"._("Dicembre")."']";
    $months_short = "['"._("Gen")."', '"._("Feb")."', '"._("Mar")."', '"._("Apr")."', '"._("Mag")."', '"._("Giu")."', '"._("Lug")."', '"._("Ago")."', '"._("Set")."', '"._("Ott")."', '"._("Nov")."', '"._("Dic")."']";
    
    $ico_calendar_path = SITE_IMG."/ico_calendar.png";
    
    $buffer = "<script>
(function($) {
    $( '#".$id."' ).datepicker({
        dateFormat : 'dd/mm/yy',
        firstDay: 1,
        showOtherMonths: true,
        selectOtherMonths: true,
        changeMonth: true,
        changeYear: true,
        showAnim: '',
        dayNames: ".$days.",
        dayNamesMin: ".$days_min.",
        dayNamesShort: ".$days_short.",
        monthNames: ".$months.",
        monthNamesShort: ".$months_short.",
    });
})(jQuery)
</script>";
    
    return $buffer;
}
