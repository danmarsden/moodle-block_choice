<?php //$Id: save_results.php    
    require_once("../../config.php");
    require_once("../../mod/choice/lib.php");
   
    if (!($form = data_submitted($CFG->wwwroot."/course/view.php"))) {
        error("no data was submitted");
    } else if (empty($form->answer)) {
        error("no answer was selected!");
    }
    $timenow = time();
    $id         = required_param('id', PARAM_INT);
    if (! $cm = get_record("course_modules", "id", $id)) {
        error("Course Module ID was incorrect");
    }
    if (! $course = get_record("course", "id", $cm->course)) {
        error("Course is misconfigured");
    }
   if (!$choice = choice_get_choice($cm->instance)) {
        error("Course module is incorrect");
    }
    if (isteacher($choice->course) or isstudent($choice->course)) {
    	$cm = get_coursemodule_from_id('choice', $id);
        choice_user_submit_response($form->answer, $choice, $USER->id, $choice->course,$cm);
        redirect($CFG->wwwroot."/course/view.php?id=".$choice->course);          
    } else {
        error("only members of this course can submit an answer.");   
    }    

?>