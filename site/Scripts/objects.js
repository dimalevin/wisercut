// User class
export class User {
    constructor(firstname, lastname, email, password, username, usertype, company,
            company_description, specialties) {
        this.firstname = firstname;
        this.lastname = lastname;
        this.email = email;
        this.password = password;
        this.username = username;
        this.usertype = usertype;
        this.company = company;
        this.company_description = company_description;
        this.specialties = specialties;
    }
}

// Company class
export class Company {
    constructor(company_name, company_description, specialties) {
        this.company_name = company_name;
        this.company_description = company_description;
        this.specialties = specialties;
    }
}

// Ajax class
export class Ajax {
    constructor(url, type, dataType, actionName, data, isasync) {
        this.url = url;
        this.type = type;
        this.dataType = dataType;
        this.actionName = actionName;
        this.data = data;
        this.async = isasync;
    }
}

// QuestionTechnicalDetails class
export class QuestionTechnicalDetails {
    
    constructor(adaptation_direction, adaptation_size, adaptation_tool_type, adaptation_type, boring_diameter, clamping, coolant_type,
     corner_radius, cut_depth, cut_length, cut_type, depth, diameter, diameter_depth_in, diameter_depth_out, diameter_internal,
     diameter_outer, face_groove_type, groove_depth, groove_position, groove_width, hole_diameter, machine_max_rpm, machine_power,
     machine_type, material_hb, material_hrc, material_type, operation_type, overhang, part_length, penetration_length, r_max,
     shoulder_depth, shoulder_length, shoulder_width, surface_quality_n, surface_quality_ra, surface_quality_rms, surface_quality_rt,
     tolerance_r_max, tolerance_r_min, tolerance_w_max, tolerance_w_min, workpiece_diameter) {
            
        this.adaptation_direction = adaptation_direction;
        this.adaptation_size = adaptation_size;
        this.adaptation_tool_type = adaptation_tool_type;
        this.adaptation_type = adaptation_type;
        this.boring_diameter = boring_diameter;
        this.clamping = clamping;
        this.coolant_type = coolant_type;
        this.corner_radius = corner_radius;
        this.cut_depth = cut_depth;
        this.cut_length = cut_length;
        this.cut_type = cut_type;
        this.depth = depth;
        this.diameter = diameter;
        this.diameter_depth_in = diameter_depth_in;
        this.diameter_depth_out = diameter_depth_out;
        this.diameter_internal = diameter_internal;
        this.diameter_outer = diameter_outer;
        this.face_groove_type = face_groove_type;
        this.groove_depth = groove_depth;
        this.groove_position = groove_position;
        this.groove_width = groove_width;
        this.hole_diameter = hole_diameter;
        this.machine_max_rpm = machine_max_rpm;
        this.machine_power = machine_power;
        this.machine_type = machine_type;
        this.material_hb = material_hb;
        this.material_hrc = material_hrc;
        this.material_type = material_type;
        this.operation_type = operation_type;
        this.overhang = overhang;
        this.part_length = part_length;
        this.penetration_length = penetration_length;
        this.r_max = r_max;
        this.shoulder_depth = shoulder_depth;
        this.shoulder_length = shoulder_length;
        this.shoulder_width = shoulder_width;
        this.surface_quality_n = surface_quality_n;
        this.surface_quality_ra = surface_quality_ra;
        this.surface_quality_rms = surface_quality_rms;
        this.surface_quality_rt = surface_quality_rt;
        this.tolerance_r_max = tolerance_r_max;
        this.tolerance_r_min = tolerance_r_min;
        this.tolerance_w_max = tolerance_w_max;
        this.tolerance_w_min = tolerance_w_min;
        this.workpiece_diameter = workpiece_diameter;
    }
}

// Question class
export class Question {
    constructor(title, type, description, tech_details) {
        this.title = title;
        this.description = description;
        this.type = type;
        this.tech_details = tech_details;
    }
}

// AdviceTechnicalDetails class
export class AdviceTechnicalDetails {
    
    constructor(tool_designation, tool_holder, tool_type, insert_type, cut, cut_depth, cut_depth_finish, cut_depth_rough, cut_length,
     cut_width, diameter, diameter_max, diameter_min, feed, feed_finish, feed_per_tooth, feed_rough, feed_table,
     feed_to_center, machine, machine_depth, machine_length, speed, rpm_const, rpm_max, speed_cut, speed_cut_finish, speed_cut_rough,
     speed_spindle, teeth, teeth_effect_num, teeth_total_num, other, chip_thickness, coolant_type, edge_preparation, grade, overhang,
     passes, pieces_per_cut_edge) {
        
        // tool + insert
        this.tool_designation = tool_designation;
        this.tool_holder = tool_holder;
        this.tool_type = tool_type;
        this.insert_type = insert_type;

        // cut
        this.cut_depth = cut_depth;
        this.cut_depth_finish = cut_depth_finish;
        this.cut_depth_rough = cut_depth_rough;
        this.cut_length = cut_length;
        this.cut_width = cut_width;

        // diameter
        this.diameter = diameter;
        this.diameter_max = diameter_max;
        this.diameter_min = diameter_min;

        // feed
        this.feed = feed;
        this.feed_finish = feed_finish;
        this.feed_per_tooth = feed_per_tooth;
        this.feed_rough = feed_rough;
        this.feed_table = feed_table;
        this.feed_to_center = feed_to_center;

        // machine 
        this.machine_depth = machine_depth;
        this.machine_length = machine_length;

        // speed
        this.rpm_const = rpm_const;
        this.rpm_max = rpm_max;
        this.speed_cut = speed_cut;
        this.speed_cut_finish = speed_cut_finish;
        this.speed_cut_rough = speed_cut_rough;
        this.speed_spindle = speed_spindle;

        // teeth
        this.teeth_effect_num = teeth_effect_num;
        this.teeth_total_num = teeth_total_num;

        // other
        this.chip_thickness = chip_thickness;
        this.coolant_type = coolant_type;
        this.edge_preparation = edge_preparation;
        this.grade = grade;
        this.overhang = overhang;
        this.passes = passes;
        this.pieces_per_cut_edge = pieces_per_cut_edge;
    }
}

// Advice class
export class Advice {
    constructor(title, description, tech_details) {
        this.title = title;
        this.description = description;
        this.tech_details = tech_details;
    }
}

// Response class
export class Response {
    constructor(advice_id, question_id, title, description, score, is_best_advice) {
        this.advice_id = advice_id;
        this.question_id = question_id;
        this.title = title;
        this.description = description;
        this.score=score;
        this.is_best_advice = is_best_advice;
    }
}