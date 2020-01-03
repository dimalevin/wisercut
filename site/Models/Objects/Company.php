<?php

/*
 * Company
 * 
 * Used for importing/exporting from/to the db company details and handling that information
 */
class Company {

    /* Constants */
    public const COMPANY_SPECIALTIES = 'Milling Turning Hole-Making';
    
    // PRIVATE PROPERTIES
    private $company_id, $manager_id, $company_name, $score = 0, $company_description, $company_specialties, $logo = SystemConstants::DEFAULT_IMAGE_NAME,
        $is_blocked, $is_approved, $date_approved = '0000-00-00';
    
    // <editor-fold defaultstate="collapsed" desc="GETTERS">
    public function getId(): int { return $this->company_id; }

    public function getManagerId(): int { return $this->manager_id; }
    
    public function getName(): string { return $this->company_name; }
    
    public function getScore(): int { return $this->score; }
    
    public function getDescription(): string { return $this->company_description; }
        
    public function getSpecialties(): string { return $this->company_specialties; }
    
    public function getLogo(): string { return $this->logo; }
    
    public function isBlocked(): bool { return $this->is_blocked; }
    
    public function isApproved(): bool { return $this->is_approved; }
    
    public function getDateApproved() { return $this->date_approved; }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="SETTERS">
    public function setId(int $value) { $this->company_id = $value; }

    public function setManagerId(int $value) { $this->manager_id = $value; }
    
    public function setName(string $value) { $this->company_name = $value; }
    
    public function setScore(int $value) { $this->score = $value; }
    
    public function setDescription(string $value) { $this->company_description = $value; }
        
    public function setSpecialties(string $value) { $this->company_specialties = $value; }
    
    public function setLogo(string $value) { $this->logo = $value; }
    
    public function setBlocked(bool $value) { $this->is_blocked = $value; }

    public function setApproved(bool $value) { $this->is_approved = $value; }
    
    public function setDateApproved($value) { $this->date_approved = $value; }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">

    // Get settings
    public function getSettings() {
        
        $result = [
            'company_description' => $this->company_description,
            'company_specialties' => $this->company_specialties,
            'logo_filename' => $this->logo
        ];
        
        return $result;
    }
    
    // Set settings
    public function setSettings(array $data_array) {
        
        $this->company_description = $data_array['company_description'];
        $this->company_specialties = $data_array['company_specialties'];
        $this->logo = $data_array['logo_filename'];
    }
    
    /* Static */

    // Convert to array
    public static function ToArray(Company $company) {
        
        $result = null;
        
        // parameter is not null
        if ($company) {
            
            $result = [
                'id' => $company->getId(),
                'name' => $company->getName(),
                'description' => $company->getDescription(),
                'specialties' => $company->getSpecialties(),
                'score' => $company->getScore(),
                'date_approved' => $company->getDateApproved(),
                'is_blocked' => $company->isBlocked(),
                'is_approved' => $company->isApproved(),
                'logo' => $company->getLogo()
            ];
        }
        
        return $result;
    }
    
    // Create company
    public static function CreateCompany(array $company_details, int $manager_id) {
        
        $new_company = null;
        
        // details exists and manager id is valid
        if (!empty($company_details) && $manager_id > 0) {
            
            $new_company = new Company();
            
            // fill details
            $new_company->setManagerId($manager_id);
            $new_company->setName($company_details['company_name']);
            $new_company->setDescription($company_details['company_description']);
            $new_company->setSpecialties($company_details['specialties']);
        }
        
        return $new_company;
    }
    // </editor-fold>

}
