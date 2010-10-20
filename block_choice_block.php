<?php //$Id: 
require_once($CFG->dirroot.'/mod/choice/lib.php');
require_once($CFG->dirroot.'/config.php');
class block_choice_block extends block_base {

    function init() {
	    $this->title = get_string('blockname', 'block_choice_block');
        $this->version = 2006041100;
        $this->choiceid = '';
    }

    function applicable_formats() {
        return array('all' => true);
    }

    function specialization() {				 		 
		if (empty($this->config->choiceid)) {
            $this->title = get_string('pleaseconfigure', 'block_choice_block');		
            $this->choiceid = '';
        } else {			
			$this->title = !empty($this->config->title) ? format_string($this->config->title) : get_field('choice', 'name', 'id', $this->config->choiceid);
			$this->choiceid = $this->config->choiceid;
		}		
    }

    function instance_allow_multiple() {
        return true;
    }
    

    function get_content() {
		global $USER, $CFG;
        if ($this->content !== NULL) {
            return $this->content;
        }
        if (!empty($this->config->choiceid)) {
            $choice = choice_get_choice($this->config->choiceid);
	    }
        $this->content = new stdClass;

        if (!empty($choice)) {       
        
        		$moduleid = get_record("modules", "name", "choice"); //get module id of choice so we can get $cm correctly.
				$moduleid = $moduleid->id;
				
			    $cm = get_record("course_modules", "course", $choice->course, "module", $moduleid, "instance", $choice->id);
			    $course = get_record("course", "id", $cm->course); 
			    
            if ($choice->timeopen > time() ) {
                $this->content->text .= get_string("notopenyet", "choice", userdate($choice->timeopen));           
            }
        
            //if user has already made a selection, and they are not allowed to update it, show their selected answer.
            if (isset($USER->id) && ($current = get_record('choice_answers', 'choiceid', $choice->id, 'userid', $USER->id)) && !$choice->allowupdate) {
                 $this->content->text .= get_string("yourselection", "choice", userdate($choice->timeopen)).": ".format_string(choice_get_option_text($choice, $current->optionid));
            }
        
            if ( (!$current or $choice->allowupdate) and ($choice->timeclose >= time() or $choice->timeclose == 0) ) {
			    // They haven't made their choice yet or updates allowed and choice is open
			
                $this->content->text = "<form name=\"form\" method=\"post\" action=\"".$CFG->wwwroot."/blocks/choice_block/save_results.php?id=".$choice->course."\">";  
                $this->content->text .= choice_show_form($choice, $USER, $cm, CHOICE_DISPLAY_VERTICAL);
                $this->content->text .= "<input type=\"hidden\" name=\"cid\" value=\"". $choice->id." \" />";
                $this->content->text .= "</form>";            
            }
     

            if ( $choice->showresults == CHOICE_SHOWRESULTS_ALWAYS or
               ( $choice->showresults == CHOICE_SHOWRESULTS_AFTER_ANSWER and $current ) or
               ( $choice->showresults == CHOICE_SHOWRESULTS_AFTER_CLOSE and $choice->timeclose <= time() ) )  {     
				

			    $this->content->text .= choice_show_results($choice, $course, $cm, CHOICE_PUBLISH_ANON_MINI);
		    }
        }
        
        $this->content->footer = '';
        return $this->content;
    }
}
?>