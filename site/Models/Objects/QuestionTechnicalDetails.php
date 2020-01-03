<?php

/*
 * Question technical details
 * 
 * Holds question's technical details and handling that information
 */
class QuestionTechnicalDetails {
    
    /* Constants */
    private const _P_RATIO = 2.222;
    
    // <editor-fold defaultstate="collapsed" desc="PRIVATE PROPERTIES">

    /* MACHINE */
    
    // Machine details
    private $_machine_type;     // (string)
    private $_machine_power;    // (int)
    private $_machine_max_rpm;  // (int)
    
    // Adaptation
    private $_adaptation_type;  // (string)
    private $_adaptation_size;  // (float)
    private $_adaptation_direction; // (string)
    private $_adaptation_tool_type; // (string)
    private $_coolant_type;         // (string)
    
    /* APPLICATION */
    
    // Material
    private $_material_type;    // (string)
    private $_material_hb;      // (int)
    private $_material_hrc;     // (int)
    
    // Stability Details
    private $_overhang;    // (string)
    private $_clamping;    // (string)
    private $_cut_type; // (string)
    
    /* PARAMETERS */
    
    // Milling
    private $_shoulder_depth;         // (int)
    private $_shoulder_width;         // (int)
    private $_shoulder_length;        // (int)
    private $_corner_radius;          // (int)
    // Turning
    private $_r_max;                  // (int)
    private $_diameter;               // (int)
    private $_penetration_length;     // (int)
    private $_cut_depth;              // (int)
    private $_cut_length;             // (int)
    private $_operation_type;         // (string)
    private $_surface_quality_n;      // (int)
    private $_surface_quality_rt;     // (int)
    private $_surface_quality_ra;     // (int)
    private $_surface_quality_rms;    // (int)
    // Grooving -internal
    private $_hole_diameter;          // (int)
    private $_groove_position;        // (int)
    private $_groove_depth;           // (int)
    private $_tolerance_w_min;        // (float)
    private $_tolerance_w_max;        // (float)
    private $_tolerance_r_min;        // (float)
    private $_tolerance_r_max;        // (float)
    // Grooving -external
    private $_workpiece_diameter;     // (int)
    private $_groove_width;           // (int)
    // Grooving -face groove
    private $_face_groove_type;       // (string)
    private $_diameter_depth_in;      // (int)
    private $_diameter_depth_out;     // (int)
    // Turning -parting
    private $_diameter_outer;         // (int)
    private $_diameter_internal;      // (int)
    private $_part_length;            // (int)
    // Hole creating
    private $_depth;                  // (int)
    private $_boring_diameter;        // (int)
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="PUBLIC METHODS">

    // Compare to QTD object
    public function compareTo(QuestionTechnicalDetails $qtd2) {
        
        $sum = 0;
        
        $qtd_array1 = self::ToArray($this);
        $qtd_array2 = self::ToArray($qtd2);
        
        // summ. deltas
        foreach ($qtd_array1 as $key => $value) {
            
            // value is numeric
            if (is_numeric($value)) {

                $min_val = min($value, $qtd_array2[$key]);
                $max_val = max($value, $qtd_array2[$key]);

                $sum += $min_val / ($max_val > 0 ? $max_val : 0.1);

            } else {
                
                $sum += $value == $qtd_array2[$key] ? 1 : 0;
            }
        }
        
        $total_identity_percentage = $sum * self::_P_RATIO;
        
        return $total_identity_percentage;
    }
    
    /* Static */
    
    // Convert to array
    public static function ToArray(QuestionTechnicalDetails $tech_details_obj) {
        
        $result = null;
        
        // parameter is not null
        if ($tech_details_obj) {
            
            $result = [
                
                /* Machine */
        
                // Machine details
                'machine_type' => $tech_details_obj->_machine_type,
                'machine_power' => $tech_details_obj->_machine_power,
                'machine_max_rpm' => $tech_details_obj->_machine_max_rpm,

                // Adaptation
                'adaptation_type' => $tech_details_obj->_adaptation_type,
                'adaptation_size' => $tech_details_obj->_adaptation_size,
                'adaptation_direction' => $tech_details_obj->_adaptation_direction ?? null,
                'adaptation_tool_type' => $tech_details_obj->_adaptation_tool_type ?? null,
                'coolant_type' => $tech_details_obj->_coolant_type ?? null,

                /* Application */

                // Material
                'material_type' => $tech_details_obj->_material_type,
                'material_hb' => $tech_details_obj->_material_hb,
                'material_hrc' => $tech_details_obj->_material_hrc,

                // Stability details
                'clamping' => $tech_details_obj->_clamping,
                'cut_type' => $tech_details_obj->_cut_type ?? null,
                'overhang' => $tech_details_obj->_overhang ?? null,

                /* Parameters */

                // surface quality
                'surface_quality_n' => $tech_details_obj->_surface_quality_n ?? null,
                'surface_quality_rt' => $tech_details_obj->_surface_quality_rt ?? null,
                'surface_quality_ra' => $tech_details_obj->_surface_quality_ra ?? null,
                'surface_quality_rms' => $tech_details_obj->_surface_quality_rms ?? null,

                // tolerance
                'tolerance_w_min' => $tech_details_obj->_tolerance_w_min ?? null,
                'tolerance_w_max' => $tech_details_obj->_tolerance_w_max ?? null,
                'tolerance_r_min' => $tech_details_obj->_tolerance_r_min ?? null,
                'tolerance_r_max' => $tech_details_obj->_tolerance_r_max ?? null,

                // diameters
                'boring_diameter' => $tech_details_obj->_boring_diameter ?? null,
                'diameter_depth_in' => $tech_details_obj->_diameter_depth_in ?? null,
                'diameter_depth_out' => $tech_details_obj->_diameter_depth_out ?? null,
                'diameter_outer' => $tech_details_obj->_diameter_outer ?? null,
                'diameter_internal' => $tech_details_obj->_diameter_internal ?? null,
                'hole_diameter' => $tech_details_obj->_hole_diameter ?? null,
                'workpiece_diameter' => $tech_details_obj->_workpiece_diameter ?? null,
                'diameter' => $tech_details_obj->_diameter ?? null,

                // groove
                'face_groove_type' => $tech_details_obj->_face_groove_type ?? null,
                'groove_depth' => $tech_details_obj->_groove_depth ?? null,
                'groove_position' => $tech_details_obj->_groove_position ?? null,
                'groove_width' => $tech_details_obj->_groove_width ?? null,

                // shoulder
                'shoulder_depth' => $tech_details_obj->_shoulder_depth ?? null,
                'shoulder_length' => $tech_details_obj->_shoulder_length ?? null,
                'shoulder_width' => $tech_details_obj->_shoulder_width ?? null,

                // cut
                'cut_depth' => $tech_details_obj->_cut_depth ?? null,
                'cut_length' => $tech_details_obj->_cut_length ?? null,

                // other
                'depth' => $tech_details_obj->_depth ?? null,
                'operation_type' => $tech_details_obj->_operation_type ?? null,
                'part_length' => $tech_details_obj->_part_length ?? null,
                'penetration_length' => $tech_details_obj->_penetration_length ?? null,
                'r_max' => $tech_details_obj->_r_max ?? null,
                'corner_radius' => $tech_details_obj->_corner_radius  ?? null
            ];
        }
        
        return $result;
    }
    
    // Create question tech details object
    public static function CreateTechData(array $question_details) {
        
        $tech_details_obj = new QuestionTechnicalDetails();
        
        // fill details
        
        /* Machine */
        
        // Machine details
        $tech_details_obj->_machine_type = $question_details['machine_type'];
        $tech_details_obj->_machine_power = $question_details['machine_power'];
        $tech_details_obj->_machine_max_rpm = $question_details['machine_max_rpm'];
        
        // Adaptation
        $tech_details_obj->_adaptation_type = $question_details['adaptation_type'];
        $tech_details_obj->_adaptation_size = $question_details['adaptation_size'];
        $tech_details_obj->_adaptation_direction = $question_details['adaptation_direction'] ?? null;
        $tech_details_obj->_adaptation_tool_type = $question_details['adaptation_tool_type'] ?? null;
        $tech_details_obj->_coolant_type = $question_details['coolant_type'] ?? null;
        
        /* Application */

        // Material
        $tech_details_obj->_material_type = $question_details['material_type'];
        $tech_details_obj->_material_hb = $question_details['material_hb'];
        $tech_details_obj->_material_hrc = $question_details['material_hrc'];
        
        // Stability details
        $tech_details_obj->_clamping = $question_details['clamping'];
        $tech_details_obj->_cut_type = $question_details['cut_type'] ?? null;
        $tech_details_obj->_overhang = $question_details['overhang'] ?? null;
        
        /* Parameters */

        // surface quality
        $tech_details_obj->_surface_quality_n = $question_details['surface_quality_n'] ?? null;
        $tech_details_obj->_surface_quality_rt = $question_details['surface_quality_rt'] ?? null;
        $tech_details_obj->_surface_quality_ra = $question_details['surface_quality_ra'] ?? null;
        $tech_details_obj->_surface_quality_rms = $question_details['surface_quality_rms'] ?? null;
        
        // tolerance
        $tech_details_obj->_tolerance_w_min = $question_details['tolerance_w_min'] ?? null;
        $tech_details_obj->_tolerance_w_max = $question_details['tolerance_w_max'] ?? null;
        $tech_details_obj->_tolerance_r_min = $question_details['tolerance_r_min'] ?? null;
        $tech_details_obj->_tolerance_r_max = $question_details['tolerance_r_max'] ?? null;
        
        // diameters
        $tech_details_obj->_boring_diameter = $question_details['boring_diameter'] ?? null;
        $tech_details_obj->_diameter_depth_in = $question_details['diameter_depth_in'] ?? null;
        $tech_details_obj->_diameter_depth_out = $question_details['diameter_depth_out'] ?? null;
        $tech_details_obj->_diameter_outer = $question_details['diameter_outer'] ?? null;
        $tech_details_obj->_diameter_internal = $question_details['diameter_internal'] ?? null;
        $tech_details_obj->_hole_diameter = $question_details['hole_diameter'] ?? null;
        $tech_details_obj->_workpiece_diameter = $question_details['workpiece_diameter'] ?? null;
        $tech_details_obj->_diameter = $question_details['diameter'] ?? null;
        
        // groove
        $tech_details_obj->_face_groove_type = $question_details['face_groove_type'] ?? null;
        $tech_details_obj->_groove_depth = $question_details['groove_depth'] ?? null;
        $tech_details_obj->_groove_position = $question_details['groove_position'] ?? null;
        $tech_details_obj->_groove_width = $question_details['groove_width'] ?? null;
        
        // shoulder
        $tech_details_obj->_shoulder_depth = $question_details['shoulder_depth'] ?? null;
        $tech_details_obj->_shoulder_length = $question_details['shoulder_length'] ?? null;
        $tech_details_obj->_shoulder_width = $question_details['shoulder_width'] ?? null;
        
        // cut
        $tech_details_obj->_cut_depth = $question_details['cut_depth'] ?? null;
        $tech_details_obj->_cut_length = $question_details['cut_length'] ?? null;
        
        // other
        $tech_details_obj->_part_length = $question_details['part_length'] ?? null;
        $tech_details_obj->_depth = $question_details['depth'] ?? null;
        $tech_details_obj->_corner_radius = $question_details['corner_radius'] ?? null;
        $tech_details_obj->_operation_type = $question_details['operation_type'] ?? null;
        $tech_details_obj->_penetration_length = $question_details['penetration_length'] ?? null;
        $tech_details_obj->_r_max = $question_details['r_max'] ?? null;
        
        return $tech_details_obj;
    }

    // </editor-fold>

}
