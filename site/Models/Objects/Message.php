<?php

/*
 * Message
 * 
 * Used for importing/exporting from/to the db message details and handling that information
 */
class Message {
    
    // PRIVATE PROPERTIES
    private $msg_id, $sender_id, $receiver_id, $title, $body, $msg_date, $is_new, $is_sent;
    
    // <editor-fold defaultstate="collapsed" desc="GETTERS">
    public function getId(): int { return $this->msg_id; }

    public function getSenderId(): int { return $this->sender_id; }
    
    public function getReceiverId(): int { return $this->receiver_id; }
    
    public function getTitle(): string { return $this->title; }
    
    public function getBody(): string { return $this->body; }
    
    public function getDate() { return $this->msg_date; }
    
    public function isNew(): bool { return $this->is_new ?? false; }

    public function isSent(): bool { return $this->is_sent ?? false; }
    
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="SETTERS">
    public function setId(int $value) { $this->msg_id = $value; }
    
    public function setSenderId(int $value) { $this->sender_id = $value; }
    
    public function setReceiverId(int $value) { $this->receiver_id = $value; }
    
    public function setTitle(string $value) { $this->title = $value; }
    
    public function setBody(string $value) { $this->body = $value; }
    
    public function setDate($value) { $this->msg_date = $value; }
    
    public function setIsNew(bool $value) { $this->is_new = $value; }
    
    public function setIsSent(bool $value) { $this->is_sent = $value; }

    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">

    
    /* Static */

    // Convert to array
    public static function ToArray(Message $message) {
        
        $result = null;
        
        // parameter is not null
        if ($message) {
            
            $result = [
                'id' => $message->getId(),
                'title' => $message->getTitle(),
                'body' => $message->getBody(),
                'date' => $message->getDate(),
                'is_new' => $message->isNew(),
                'is_sent' => $message->isSent()
            ];
        }
        
        return $result;
    }
    
    // Create message
    public static function CreateMessage(array $message_details) {
        
        $new_message = null;
        
        // details exists
        if (!empty($message_details)) {
            
            $new_message = new Message();
            
            $body = MessagesTemplates::GeneralBodyTemplate($message_details['body']);
            
            // fill details
            $new_message->setSenderId($message_details['sender_id']);
            $new_message->setReceiverId($message_details['receiver_id']);
            $new_message->setTitle($message_details['title']);
            $new_message->setBody($body);
        }
        
        return $new_message;
    }
    
    // </editor-fold>
    
}
