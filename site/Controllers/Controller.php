<?php

/*
 * Controller
 * 
 * -see MVC design pattern
 */
abstract class Controller {
    
    // PRIVATE PROPERTIES
    protected $_model, $_view;
    
    // <editor-fold defaultstate="collapsed" desc="METHODS">

    // Invoke
    public function invoke($json_data) {
        
        // data exists
        if ($json_data && isset($json_data['actionName'])) {
            
            $action = $json_data['actionName'];
            $data = $json_data['data'];
            
            if ($action === 'getPage') {
                
                $index = (int) $data;
                
                $page = $this->_model->getPage($index);
                
                $this->_view::SendPage($page);
                
            } elseif ($action === 'getData') {
                
                $requested_data = $this->_model->getData($data);
                
                $this->_view::SendData($requested_data);
                
            } else { $this->_commandHandler($action, $data); }

        } else { $this->_view->showDefaultPage($this->_model->getFilePathByIndex(0)); }
    }

    
    /* Static */

    /* Abstract */
    protected abstract function _commandHandler(string $command, $data_array);    // command handler
    // </editor-fold>
}
