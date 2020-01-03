<?php

/*
 * Helper
 * 
 * Has www functions
 */
abstract class Helper {
    
    // Detect if current client is mobile
    public static function IsMobile(): bool {
        
        $detect = new Mobile_Detect();
        
        return $detect->IsMobile();
    }
    
    // Get base url
    public static function GetBaseUrl(): string {
        $protocol = strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, 5)) === 'https' ? 'https://' : 'http://';
        $domain = $_SERVER['SERVER_NAME'];
        $relative_path = dirname($_SERVER['PHP_SELF']);
        $path_info = pathinfo($relative_path);
        
        return $protocol.$domain.$relative_path.'/';
    }
}
