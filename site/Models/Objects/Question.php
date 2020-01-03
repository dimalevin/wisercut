<?php

/*
 * Question
 * 
 * Used for importing/exporting from/to the db question details and handling that information
 */
class Question {

    // PRIVATE PROPERTIES
	private $que_id, $user_id, $date_opened, $date_closed, $is_closed, $is_visible, $type, $title, $description, $tech_data;
    
	// <editor-fold defaultstate="collapsed" desc="GETTERS">
    
    public function getId(): int { return $this->que_id; }
    
    public function getUserId(): int { return $this->user_id; }
    
    public function getDateOpened() { return $this->date_opened; }
        
    public function getDateClosed() { return $this->date_closed; }

    public function isClosed(): bool { return $this->is_closed; }

    public function isVisible(): bool { return $this->is_visible; }
    
    public function getTitle(): string { return $this->title; }
    
    public function getType(): string { return $this->type; }
    
    public function getDescription(): string { return $this->description; }
    
    public function getTechData(): QuestionTechnicalDetails {
        
        if ($this->tech_data instanceof QuestionTechnicalDetails) {
            return $this->tech_data; 
        } else {
            return unserialize($this->tech_data); 
        }
    }
    
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="SETTERS">
    
    public function setId(int $value) { $this->que_id = $value; }
    
    public function setUserId(int $value) { $this->user_id = $value; }
    
    public function setDateOpened($value) { $this->date_opened = $value; }

    public function setDateClosed($value) { $this->date_closed = $value; }

    public function setClosed(bool $value) { $this->is_closed = $value; }

    public function setVisible(bool $value) { $this->is_visible = $value; }
    
    public function setTitle(string $value) { $this->title = $value; }
    
    public function setType(string $value) { $this->type = $value; }
    
    public function setDescription(string $value) { $this->description = $value; }
    
    public function setTechData(QuestionTechnicalDetails $question_tech_data) { $this->tech_data = $question_tech_data; }
    
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">

    
    /* Static */

    // Convert to array
    public static function ToArray(Question $question) {
        
        $result = null;
        
        // parameter is not null
        if ($question) {
            
            $result = [
                'id' => $question->getId(),
                'type' => $question->getType(),
                'title' => $question->getTitle(),
                'description' => $question->getDescription(),
                'is_visible' => $question->isVisible(),
                'is_closed' => $question->isClosed(),
                'date_opened' => $question->getDateOpened(),
                'date_closed' => $question->getDateClosed()
            ];
        }
        
        return $result;
    }
    
    // Create question
    public static function CreateQuestion(array $question_details, int $asking_user_id) {
        
        $new_question = null;
        
        // details exists and asking user id is valid
        if (!empty($question_details) && $asking_user_id > 0) {
            
            $new_question = new Question();
            
            // fill details
            $new_question->setUserId($asking_user_id);
            $new_question->setType($question_details['type']);
            $new_question->setTitle($question_details['title']);
            $new_question->setDescription($question_details['description']);
            $new_question->setTechData(QuestionTechnicalDetails::CreateTechData($question_details['tech_details']));
        }
        
        return $new_question;
    }
    
    // </editor-fold>

}






