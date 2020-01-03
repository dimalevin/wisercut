<?php

/*
 * Advice
 * 
 * Used for importing/exporting from/to the db advice details and handling that information
 */
class Advice {

	// PRIVATE PROPERTIES
	private $adv_id, $user_id, $title, $description, $date_created, $date_given, $tech_data;

    // <editor-fold defaultstate="collapsed" desc="GETTERS">
    
    public function getId(): int { return $this->adv_id; }
    
    public function getUserId(): int { return $this->user_id; }
    
    public function getDateCreated() { return $this->date_created; }
    
    public function getDateGiven() { return $this->date_given; }
    
    public function getTitle(): string { return $this->title; }

    public function getDescription(): string { return $this->description; }
    
    public function getTechData(): AdviceTechnicalDetails {
        
        if ($this->tech_data instanceof AdviceTechnicalDetails) {
            return $this->tech_data; 
        } else {
            return unserialize($this->tech_data); 
        }
    }
    
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="SETTERS">
    
    public function setId(int $value) { $this->adv_id = $value; }
    
    public function setUserId(int $value) { $this->user_id = $value; }
    
    public function setDateCreated($value) { $this->date_created = $value; }
    
    public function setTitle(string $value) { $this->title = $value; }
    
    public function setDescription(string $value) { $this->description = $value; }
    
    public function setTechData(AdviceTechnicalDetails $advice_tech_data) { $this->tech_data = $advice_tech_data; }
    
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">

    /* Static */

    // Convert to array
    public static function ToArray(Advice $advice) {
        
        $result = null;
        
        // parameter is not null
        if ($advice) {
            
            $result = [
                'id' => $advice->getId(),
                'user_id' => $advice->getUserId(),
                'title' => $advice->getTitle(),
                'description' => $advice->getDescription(),
                'date_created' => $advice->getDateCreated(),
                'date_given' => $advice->getDateGiven() ?? null
            ];
        }
        
        return $result;
    }
    
    // Create advice
    public static function CreateAdvice(array $advice_details, int $advising_user_id) {
        
        $new_advice = null;
        
        // details exists and advising user id is valid
        if (!empty($advice_details) && $advising_user_id > 0) {
            
            $new_advice = new Advice();
            
            // fill details
            $new_advice->setUserId($advising_user_id);
            $new_advice->setTitle($advice_details['title']);
            $new_advice->setDescription($advice_details['description']);
            $new_advice->setTechData(AdviceTechnicalDetails::CreateTechData($advice_details['tech_details']));
        }
        
        return $new_advice;
    }
    // </editor-fold>

}






