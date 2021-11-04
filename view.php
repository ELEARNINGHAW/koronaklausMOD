<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of koroklaus
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_koroklaus
 * @copyright  20020 werner.welte@haw-hamburg.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
// Replace koroklaus with the name of your module and remove this line.

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... koroklaus instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('koroklaus', $id, 0, false, MUST_EXIST);
    $course     = $DB -> get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $koroklaus  = $DB -> get_record('koroklaus', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $koroklaus  = $DB -> get_record('koroklaus', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB -> get_record('course', array('id' => $koroklaus->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('koroklaus', $koroklaus->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$event = \mod_koroklaus\event\course_module_viewed::create(array(
    'objectid' => $PAGE -> cm -> instance,
    'context' => $PAGE -> context,
));
$event->add_record_snapshot('course', $PAGE -> course);
$event->add_record_snapshot($PAGE -> cm -> modname, $koroklaus);
$event->trigger();

// Print the page header.

$PAGE->set_url('/mod/koroklaus/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($koroklaus -> name));
$PAGE->set_heading(format_string($course -> fullname));

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('koroklaus-'.$somevar);
 */

// Output starts here.
echo $OUTPUT->header();

$coursecontext = context_course::instance($COURSE->id);


$USER->role = 2;  /* Studi / Gast */

if ( has_capability( 'moodle/course:update', $coursecontext, $USER->id) ) 
{ 
  $USER->role = 3; /* Dozent / Tutor */
}

$idm = 
 "&uid=".rawurlencode( base64_encode( $USER -> id	             ))
."&un=" .rawurlencode( base64_encode( $USER -> username	       ))
."&fn=" .rawurlencode( base64_encode( $USER -> firstname       ))  
."&ln=" .rawurlencode( base64_encode( $USER -> lastname	       ))	
."&se=" .rawurlencode( base64_encode( $USER -> phone1		       ))
."&m="  .rawurlencode( base64_encode( $USER -> email		       ))
."&id=" .rawurlencode( base64_encode( $USER -> idnumber	       )) 
."&fa=" .rawurlencode( base64_encode( $USER -> address         )) 
."&dp=" .rawurlencode( base64_encode( $USER -> department      )) 
."&ro=" .rawurlencode( base64_encode( $USER -> role            )) 
."&sx=" .rawurlencode( base64_encode( $USER -> url             )) 
."&an=" .rawurlencode( base64_encode( $USER -> profile['ang']  ))
."&sx=" .rawurlencode( base64_encode( $USER -> profile['sx']   ))
."&ma=" .rawurlencode( base64_encode( $USER -> profile['matnr']))
."&sn=" .rawurlencode( base64_encode( $course -> shortname     ))
."&cn=" .rawurlencode( base64_encode( $course -> fullname      ))
."&cid=".rawurlencode( base64_encode( $course -> id            )) 
."&mid=".rawurlencode( base64_encode( $cm -> id                )) 
;

$wp = "index.php?x=".$idm;

if   ( isset( $_SERVER[ 'SERVER_NAME' ] ) AND ( $_SERVER[ 'SERVER_NAME' ] )   == '141.22.117.12' )
     { $srv = "https://141.22.117.12/koroklaus-DEV/htdocs/";                      /* Dev-Server */   }
else if ( isset( $_SERVER[ 'SERVER_NAME' ] ) AND ( $_SERVER[ 'SERVER_NAME' ] )   == 'localhost' )   
     { $srv = "https://localhost/haw/KoronaKlaus/vorlesungs_vz/";                 /* Dev-Server */   }

else { $srv = "https://lernserver.el.haw-hamburg.de/haw/KoronaKlaus/vorlesungs_vz/";      /* Live-Server */  }

$src = $srv.$wp;
/*
if ( $USER -> role ==  2 ) { $view = '_blank' ; }
else                       { $view = '_self'  ; }
*/
$content = "<iframe allowfullscreen allowfullscreen = \"true\"  border=\"0\" frameborder=\"0\" src=\"" .$src. "\" style=\"width:100% ; height:1000px ;  display: block;\" ></iframe>";

#$content = $_SERVER[ 'SERVER_NAME' ];
echo $OUTPUT->box($content, "generalbox center clearfix");

echo $OUTPUT->footer();
