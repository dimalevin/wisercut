<?php

/*
 * User
 * 
 * Used for importing/exporting from/to the db user details and handling that information
 */
class User {

    // PRIVATE PROPERTIES
    private $user_id, $username, $user_psw, $user_fname, $user_lname, $user_type, $is_blocked, $is_approved, $u_email, $date_created,
        $picture = SystemConstants::DEFAULT_IMAGE_NAME, $duplicate_to_mail = true, $allow_newsletters = true;

    // <editor-fold defaultstate="collapsed" desc="GETTERS">
    public function getId(): int { return $this->user_id; }

    public function getUsername(): string { return $this->username; }
    
    public function getFName(): string { return $this->user_fname; }
    
    public function getLName(): string { return $this->user_lname; }
    
    public function getFullName(): string { return $this->user_fname.' '.$this->user_lname; }
    
    public function getPassword(): string { return $this->user_psw; }
    
    public function getType(): string { return $this->user_type; }

    public function isBlocked(): bool { return $this->is_blocked; }
    
    public function isApproved(): bool { return $this->is_approved; }
    
    public function getEmail(): string { return $this->u_email; }

    public function getPicture(): string { return $this->picture; }
    
    public function getDateCreated() { return $this->date_created; }
    
    public function isDuplicateToMail(): bool { return $this->duplicate_to_mail; }
    
    public function isAllowNewsletters(): bool { return $this->allow_newsletters; }
    
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="SETTERS">
    
    public function setPassword(string $value) { $this->user_psw = $value; }
    
    public function setType(string $value) { $this->user_type = $value; }
    
    public function setUsername(string $value) { $this->username = $value; }
    
    public function setFName(string $value) { $this->user_fname = $value; }
    
    public function setLName(string $value) { $this->user_lname = $value; }

    public function setBlocked(bool $value) { $this->is_blocked = $value; }
    
    public function setApproved(bool $value) { $this->is_approved = $value; }
    
    public function setEmail(string $value) { $this->u_email = $value; }
    
    public function setPicture(string $value) { $this->picture = $value; }
    
    public function setDateCreated($value) { $this->date_created = $value; }
    
    public function setDuplicateToMail(bool $value) { $this->duplicate_to_mail = $value; }
    
    public function setAllowNewsletters(bool $value) { $this->allow_newsletters = $value; }
    
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">

    // Get settings
    public function getSettings() {
        
        $result = [
            'f_name' => $this->user_fname,
            'l_name' => $this->user_lname,
            'picture_filename' => $this->picture,
            'duplicate_to_mail' => (bool) $this->duplicate_to_mail,
            'allow_newsletters' => (bool) $this->allow_newsletters
        ];
        
        return $result;
    }
    
    // Set settings
    public function setSettings(array $data_array) {
        
        $this->user_fname = $data_array['f_name'];
        $this->user_lname = $data_array['l_name'];
        $this->picture = $data_array['picture_filename'];
        $this->duplicate_to_mail = (bool) $data_array['duplicate_to_mail'];
        $this->allow_newsletters = (bool) $data_array['allow_newsletters'];
    }

    /* Static */

    // Convert to array
    public static function ToArray(User $user) {
        
        $result = null;
        
        // parameter is not null
        if ($user) {
            
            $result = [
                'id' => $user->getId(),
                'type' => $user->getType(),
                'username' => $user->getUsername(),
                'name' => $user->getFullName(),
                'email' => $user->getEmail(),
                'picture' => $user->getPicture(),
                'is_blocked' => $user->isBlocked(),
                'is_approved' => $user->isApproved()
            ];
        }
        
        return $result;
    }
    
    // Create new user
    public static function CreateUser(array $user_details) {
        
        $new_user = null;
        
        // details exists
        if (!empty($user_details)) {
            
            $new_user = new User();
            
            // fill details
            $new_user->setUsername($user_details['username']);
            $new_user->setPassword($user_details['password']);
            $new_user->setType($user_details['usertype']);
            $new_user->setFName($user_details['firstname']);
            $new_user->setLName($user_details['lastname']);
            $new_user->setEmail($user_details['email']);
        }
        
        return $new_user;
    }
    
    // </editor-fold>

}
