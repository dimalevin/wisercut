<?php

/*
 * Question-advice comments
 * 
 * Holds and manages question-advice comments
 */
class QuestionAdviceComments {
    
    // PRIVATE PROPERTIES
	private $adv_id, $que_id, $comments = '', $last_update, $_is_new = false;
    
    // <editor-fold defaultstate="collapsed" desc="GETTERS">
    public function getAdviceId(): int { return $this->adv_id; }
    
    public function getQuestionId(): int { return $this->que_id; }
    
    public function getComments(): string { return $this->comments; }
    
    public function getLastUpdate() { return $this->last_update; }
    
    public function isNew(): bool { return $this->_is_new; }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="SETTERS">
    public function setAdviceId(int $value) { $this->adv_id = $value; }
    
    public function setQuestionId(int $value) { $this->que_id = $value; }
    
    public function setComments(string $value) { $this->comments = $value; }
    
    public function setLastUpdate($value) { $this->last_update = $value; }
    
    public function setIsNew(bool $value) { $this->_is_new = $value; }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="METHODS">

    // Add comment
    public function addComment(string $comment, string $user_type) {
        
        $updated_comments = $this->comments;
        
        // last update is not today date or no date exists
        if ($this->comments === '' || !$this->_isLastUpdateToday()) {
            $current_date = date('D, j F Y');
            $updated_comments .= "<div class='chat-date-stamp'>$current_date</div>";
        }
        
        $current_time = date('H:i');
        $updated_comments .= "<div class='chat-$user_type'>$comment<div class='chat-time'>$current_time</div></div>";
        
        $this->comments = $updated_comments;
    }

    // Check if last update date is current date
    private function _isLastUpdateToday(): bool {
        
        $dt_format = 'Y-m-d';
        $today = new DateTime(date($dt_format)); 
        $last_update_time = new DateTime($this->last_update); 
        
        return $last_update_time->format($dt_format) == $today->format($dt_format);
    }
    // </editor-fold>
}
