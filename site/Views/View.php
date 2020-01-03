<?php

/*
 * View
 * 
 * -see MVC design pattern
 */
class View {

    // PRIVATE PROPERTIES
    private $_array_menu_items, $_array_content_file_names;

    // CONSTRUCTOR
    function __construct($array_menu_items, $array_content_file_names) {

        $this->_array_menu_items = $array_menu_items;
        $this->_array_content_file_names = $array_content_file_names;
    }

    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">
    // Show page
    public function showDefaultPage(string $page) {
        include_once __DIR__ . '/Pages/Templates/template.php';  // view template with auto build script
    }

    /* Static */

    // Send page to a view
    public static function SendPage(array $page) {

        $html = $page['html'];
        $content = $page['content'];
        $status = $page['http_code'] === 200;

        self::_SendDataToView($status, $html, $content);
    }

    // Send response to a view
    public static function SendResponse(bool $status, string $message = '', string $message_type = '') {

        self::_SendDataToView($status, null, null, $message, $message_type);        // TEST changed to null from false
    }

    // Send data to a view
    public static function SendData($data) {

        self::_SendDataToView(true, null, $data);
    }

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="PRIVATE METHODS">
    // Build menu mobile
    private function _buildMenuMobile(): string {

        $index = 0;

        $res = '<nav class="navbar navbar-dark navbar-expand-md">';
        $res .= "<button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#collapse-mobile'>"
                . "<span class='navbar-toggler-icon'></span></button>";
        $res .= "<a class='navbar-brand' href=''><img src='Content/images/logo.png'/ class='img-fluid'></a>";

        $res .= "<div class='collapse navbar-collapse' id='collapse-mobile'><ul class='navbar-nav main-menu'>";

        // build the menu
        foreach ($this->_array_menu_items as $menu_item) {
            $res .= '<li class="nav-item"><a class="nav-link"';

            // this button is active
            if (strcasecmp($menu_item, $this->_array_menu_items[$this->_index_to_show ?? 0]) == 0 && strcasecmp($menu_item, 'home') == 0) {
                $res .= ' href="' . $menu_item . '" data-index=' . $index . ' >' . ucfirst($menu_item) . '</a></li>';
            }
            // this button is logout
            if (strcasecmp($menu_item, 'logout') == 0) {
                $res .= ' href="logout" data-index="logout" ><i class="fa fa-sign-out" aria-hidden="true"></i>' . ucfirst($menu_item) . "</a></li>";
            } else if (strcasecmp($menu_item, 'home') != 0) {
                $res .= ' href="' . $menu_item . '" data-index=' . $index . '>' . ucfirst($menu_item) . '</a></li>';
            }

            $index++;
        }

        return $res . '</ul></div></nav>';
    }

    // Build menu desktop
    private function _buildMenuDesktop(): string {

        $index = 0;

        $res = "<nav class='navbar navbar-expand-sm sticky-top d-block'>"
                . "<a class='navbar-brand' href='#'><img src='Content/images/logo.png'/ class='img-fluid'></a><ul class='navbar-nav main-menu float-right'>";

        // build the menu
        foreach ($this->_array_menu_items as $menu_item) {

            $t_val = strcasecmp($menu_item, 'login') == 0 ? 'btn-style' : (strcasecmp($menu_item, 'registration') == 0 ? 'btn-style-white' : '');
            $res .= "<li class='nav-item'><a class='nav-link hvr-grow-shadow $t_val'";

            // this button is active
            if (strcasecmp($menu_item, $this->_array_menu_items[$this->_index_to_show ?? 0]) == 0 && strcasecmp($menu_item, 'home') == 0) {
                $res .= ' href="home" data-index=' . $index . '  ><i class="fa fa-home"></i></a></li>';
            }
            // this button is logout
            if (strcasecmp($menu_item, 'logout') == 0) {
                $res .= 'href="logout" data-index="logout">' . ucfirst($menu_item) . '</a></li>';
            } else if (strcasecmp($menu_item, 'home') != 0) {
                $res .= ' href="' . $menu_item . '" data-index=' . $index . '>' . ucfirst($menu_item) . '</a></li>';
            }

            $index++;
        }

        return $res . '</ul></nav>';
    }

    /* Static */

    // Send data to a view
    private static function _SendDataToView(bool $status, $html, $content, $message = null, $message_type = null) {

        //header('Content-type: application/json');

        $results = [
            'success' => $status,
            'html' => $html,
            'content' => $content,
            'message' => $message,
            'message_type' => $message_type
        ];

        echo json_encode($results);
    }

    // </editor-fold>
}
