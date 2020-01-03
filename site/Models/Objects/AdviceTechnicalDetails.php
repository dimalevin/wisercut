<?php

/*
 * Advice technical details
 * 
 * Holds advice's technical details and handling that information
 */
class AdviceTechnicalDetails {
    
    // <editor-fold defaultstate="collapsed" desc="PRIVATE PROPERTIES">
    
    // tool + insert
    private $_tool_designation;     // (string)
    private $_tool_holder;          // (string)
    private $_tool_type;            // (string)
    private $_insert_type;          // (string)
    
    // cut
    private $_cut_depth;            // (float)
    private $_cut_depth_finish;     // (float)
    private $_cut_depth_rough;      // (float)
    private $_cut_length;           // (int)
    private $_cut_width;            // (int)
    
    // diameter
    private $_diameter;             // (int)
    private $_diameter_max;         // (int)
    private $_diameter_min;         // (int)
    
    // feed
    private $_feed;                 // (int)
    private $_feed_finish;          // (int)
    private $_feed_per_tooth;       // (float)
    private $_feed_rough;           // (int)
    private $_feed_table;           // (int)
    private $_feed_to_center;       // (int)
    
    // machine 
    private $_machine_depth;        // (int)
    private $_machine_length;       // (int)    
    
    // speed
    private $_rpm_const;            // (int)
    private $_rpm_max;              // (int)
    private $_speed_cut;            // (int)
    private $_speed_cut_finish;     // (int)
    private $_speed_cut_rough;      // (int)
    private $_speed_spindle;        // (int)    
    
    // teeth
    private $_teeth_effect_num;     // (int)
    private $_teeth_total_num;      // (int)    
    
    // other
    private $_chip_thickness;        // (float)
    private $_coolant_type;         // (string)
    private $_edge_preparation;     // (string)
    private $_grade;                // (string)
    private $_overhang;             // (int)
    private $_passes;               // (int)
    private $_pieces_per_cut_edge;  // (int)
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">

    /* Static */
    
    // Convert to array
    public static function ToArray(AdviceTechnicalDetails $tech_details_obj) {
        
        $result = null;
        
        // parameter is not null
        if ($tech_details_obj) {
            
            $result = [
                
                // tool + insert
                'tool_designation' => $tech_details_obj->_tool_designation ?? null,
                'tool_holder' => $tech_details_obj->_tool_holder ?? null,
                'tool_type' => $tech_details_obj->_tool_type ?? null,
                'insert_type' => $tech_details_obj->_insert_type ?? null,
                
                // cut
                'cut_depth' => $tech_details_obj->_cut_depth ?? null,
                'cut_depth_finish' => $tech_details_obj->_cut_depth_finish ?? null,
                'cut_depth_rough' => $tech_details_obj->_cut_depth_rough ?? null,
                'cut_length' => $tech_details_obj->_cut_length ?? null,
                'cut_width' => $tech_details_obj->_cut_width ?? null,
                
                // diameter
                'diameter' => $tech_details_obj->_diameter ?? null,
                'diameter_min' => $tech_details_obj->_diameter_min ?? null,
                'diameter_max' => $tech_details_obj->_diameter_max ?? null,
                
                // feed
                'feed' => $tech_details_obj->_feed ?? null,
                'feed_finish' => $tech_details_obj->_feed_finish ?? null,
                'feed_per_tooth' => $tech_details_obj->_feed_per_tooth ?? null,
                'feed_rough' => $tech_details_obj->_feed_rough ?? null,
                'feed_table' => $tech_details_obj->_feed_table ?? null,
                'feed_to_center' => $tech_details_obj->_feed_to_center ?? null,
                
                // machine 
                'machine_depth' => $tech_details_obj->_machine_depth ?? null,
                'machine_length' => $tech_details_obj->_machine_length ?? null,
                
                // speed
                'speed_cut' => $tech_details_obj->_speed_cut ?? null,
                'speed_spindle' => $tech_details_obj->_speed_spindle ?? null,
                'speed_cut_finish' => $tech_details_obj->_speed_cut_finish ?? null,
                'speed_cut_rough' => $tech_details_obj->_speed_cut_rough ?? null,
                'rpm_const' => $tech_details_obj->_rpm_const ?? null,
                'rpm_max' => $tech_details_obj->_rpm_max ?? null,
                
                // teeth
                'teeth_total_num' => $tech_details_obj->_teeth_total_num ?? null,
                'teeth_effect_num' => $tech_details_obj->_teeth_effect_num ?? null,
                
                // other
                'chip_thickness' => $tech_details_obj->_chip_thickness ?? null,
                'coolant_type' => $tech_details_obj->_coolant_type ?? null,
                'edge_preparation' => $tech_details_obj->_edge_preparation ?? null,
                'grade' => $tech_details_obj->_grade ?? null,
                'overhang' => $tech_details_obj->_overhang ?? null,
                'passes' => $tech_details_obj->_passes ?? null,
                'pieces_per_cut_edge' => $tech_details_obj->_pieces_per_cut_edge ?? null
            ];
        }
        
        return $result;
    }
    
    // Create advice tech details object
    public static function CreateTechData(array $advice_details): AdviceTechnicalDetails {
        
        $tech_details_obj = new AdviceTechnicalDetails();
        
        // fill details
        
        // tool + insert
        $tech_details_obj->_tool_designation = $advice_details['tool_designation'] ?? null;
        $tech_details_obj->_tool_holder = $advice_details['tool_holder'] ?? null;
        $tech_details_obj->_tool_type = $advice_details['tool_type'] ?? null;
        $tech_details_obj->_insert_type = $advice_details['insert_type'] ?? null;
        
        // cut
        $tech_details_obj->_cut_depth = $advice_details['cut_depth'] ?? null;
        $tech_details_obj->_cut_depth_finish = $advice_details['cut_depth_finish'] ?? null;
        $tech_details_obj->_cut_depth_rough = $advice_details['cut_depth_rough'] ?? null;
        $tech_details_obj->_cut_length = $advice_details['cut_length'] ?? null;
        $tech_details_obj->_cut_width = $advice_details['cut_width'] ?? null;
        
        // diameter
        $tech_details_obj->_diameter = $advice_details['diameter'] ?? null;
        $tech_details_obj->_diameter_max = $advice_details['diameter_max'] ?? null;
        $tech_details_obj->_diameter_min = $advice_details['diameter_min'] ?? null;
        
        // feed
        $tech_details_obj->_feed = $advice_details['feed'] ?? null;
        $tech_details_obj->_feed_finish = $advice_details['feed_finish'] ?? null;
        $tech_details_obj->_feed_rough = $advice_details['feed_rough'] ?? null;
        $tech_details_obj->_feed_table = $advice_details['feed_table'] ?? null;
        $tech_details_obj->_feed_to_center = $advice_details['feed_to_center'] ?? null;
        $tech_details_obj->_feed_per_tooth = $advice_details['feed_per_tooth'] ?? null;
        
        // machine
        $tech_details_obj->_machine_depth = $advice_details['machine_depth'] ?? null;
        $tech_details_obj->_machine_length = $advice_details['machine_length'] ?? null;
        
        // speed
        $tech_details_obj->_speed_cut = $advice_details['speed_cut'] ?? null;
        $tech_details_obj->_speed_cut_finish = $advice_details['speed_cut_finish'] ?? null;
        $tech_details_obj->_speed_cut_rough = $advice_details['speed_cut_rough'] ?? null;
        $tech_details_obj->_speed_spindle = $advice_details['speed_spindle'] ?? null;
        $tech_details_obj->_rpm_const = $advice_details['rpm_const'] ?? null;
        $tech_details_obj->_rpm_max = $advice_details['rpm_max'] ?? null;
        
        // teeth
        $tech_details_obj->_teeth_effect_num = $advice_details['teeth_effect_num'] ?? null;
        $tech_details_obj->_teeth_total_num = $advice_details['teeth_total_num'] ?? null;
        
        // other
        $tech_details_obj->_chip_thickness = $advice_details['chip_thickness'] ?? null;
        $tech_details_obj->_coolant_type = $advice_details['coolant_type'] ?? null;
        $tech_details_obj->_edge_preparation = $advice_details['edge_preparation'] ?? null;
        $tech_details_obj->_grade = $advice_details['grade'] ?? null;
        $tech_details_obj->_overhang = $advice_details['overhang'] ?? null;
        $tech_details_obj->_passes = $advice_details['passes'] ?? null;
        $tech_details_obj->_pieces_per_cut_edge = $advice_details['pieces_per_cut_edge'] ?? null;

        return $tech_details_obj;
    }
    
    // </editor-fold>
    
}
