<?php

/*
 * Response
 * 
 * Used for importing/exporting from/to the db response details and handling that information
 */
class Response {

	// PRIVATE PROPERTIES
	private $adv_id, $que_id, $date_created, $title, $description, $score, $is_best_advice, $is_new;

    // <editor-fold defaultstate="collapsed" desc="GETTERS">
    
    public function getAdviceId(): int { return $this->adv_id; }
    
    public function getQuestionId(): int { return $this->que_id; }
    
    public function getDateCreated() { return $this->date_created; }

    public function getTitle(): string { return $this->title; }
    
    public function getDescription(): string { return $this->description; }

    public function getScore(): int { return $this->score; }

    public function isBestAdvice(): bool { return $this->is_best_advice; }
    
    public function isNew(): bool { return $this->is_new; }
    
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="SETTERS">
    
    public function setAdviceId(int $value) { $this->adv_id = $value; }
    
    public function setQuestionId(int $value) { $this->que_id = $value; }
    
    public function setDateCreated($value) { $this->date_created = $value; }
    
    public function setTitle(string $value) { $this->title = $value; }
    
    public function setDescription(string $value) { $this->description = $value; }
    
    public function setScore(int $value) { $this->score = $value; }

    public function setIsBestAdvice(bool $value) { $this->is_best_advice = $value; }
    
    public function setNew(bool $value) { $this->is_new = $value; }
    
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">

    /* Static */

    // Convert to array
    public static function ToArray(Response $response) {
        
        $result = null;
        
        // parameter is not null
        if ($response) {
            
            $result = [
                'advice_id' => $response->getAdviceId(),
                'question_id' => $response->getQuestionId(),
                'title' => $response->getTitle(),
                'description' => $response->getDescription(),
                'score' => $response->getScore(),
                'is_new' => $response->isNew(),
                'is_best_advice' => $response->isBestAdvice(),
                'date_created' => $response->getDateCreated()
            ];
        }
        
        return $result;
    }
    
    // Create question
    public static function CreateResponse(array $response_details) {
        
        $new_response = null;
        
        // details exists
        if (!empty($response_details)) {
            
            /* @var $response Response */
            $new_response = new Response();
            
            // fill details
            $new_response->setAdviceId($response_details['advice_id']);
            $new_response->setQuestionId($response_details['question_id']);
            $new_response->setTitle($response_details['title']);
            $new_response->setDescription($response_details['description']);
            $new_response->setScore($response_details['score']);
            $new_response->setIsBestAdvice($response_details['is_best_advice']);
        }
        
        return $new_response;
    }
    
    // </editor-fold>

}






