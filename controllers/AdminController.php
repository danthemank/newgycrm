<?php 

class AdminController {

    public $price_per_hour;
    public $team_level;
    public $carrier_options;
    public $slot_week;
    public function __construct() {
        $this->slot_week = ['_slot_time_monday' => 'MON', '_slot_time_tuesday' => 'TUE', '_slot_time_wednesday' => 'WED', '_slot_time_thursday' => 'THU', '_slot_time_friday' => 'FRI', '_slot_time_saturday' => 'SAT', '_slot_time_sunday' => 'SUN'];
        $this->team_level = ['AGL', 'Xcel Bronze',  'Xcel Silver', 'Xcel Platinum', 'Xcel Gold', 'Xcel Diamond', 'Xcel Sapphire', 'Level 6', 'Level 7','Level 8','Level 9','Level 10'];

        $this->price_per_hour = ['0,5' => get_option('halfhour_week'), '0.5' => get_option('halfhour_week'), 1 => get_option('onehour_week'), '1.5' => get_option('onehalfhour_week'), '1,5' => get_option('onehalfhour_week'), '2' => get_option('twohour_week'), '3' => get_option('threehour_week'), '4' => get_option('fourhour_week'), '5' => get_option('fivehour_week'), '6' => get_option('sixhour_week'), '7' => get_option('sevenhour_week'), '8' => get_option('eighthour_week'), '9' => get_option('ninehour_week'), '12' => get_option('twelvehour_week'), '15' => get_option('fifteenhour_week'), '20' => get_option('twentyhour_week'),];

        $this->carrier_options = array(
            "appalachianwireless" => "Appalachian Wireless",
            "at&tmobility" => "AT&T Mobility",
            "bigriverbroadband" => "Big River Broadband",
            "boostmobile" => "Boost Mobile",
            "bravadowireless" => "Bravado Wireless",
            "bugtusselwireless" => "Bug Tussel Wireless",
            "carolinawestwireless" => "Carolina West Wireless",
            "cellcom" => "Cellcom",
            "cellularone" => "Cellular One",
            "cellularoneofnortheastarizona" => "Cellular One of North East Arizona",
            "choicewireless" => "Choice Wireless",
            "coloradovalleycommunications" => "Colorado Valley Communications",
            "commnetwireless" => "Commnet Wireless",
            "cricket" => "Cricket Wireless",
            "cspire" => "C Spire",
            "custertelephonecooperative" => "Custer Telephone Cooperative",
            "dishwireless" => "Dish Wireless",
            "dtcwireless" => "DTC Wireless",
            "etc" => "ETC",
            "evolvebroadband" => "Evolve Broadband",
            "ftcwireless" => "FTC Wireless",
            "googlefi" => "Google Fi",
            "h2owireless" => "H2O Wireless",
            "illinoisvalleycellular" => "Illinois Valley Cellular",
            "indigowireless" => "Indigo Wireless",
            "infrastructurenetworks" => "Infrastructure Networks",
            "inlandcellular" => "Inland Cellular",
            "limitlessmobile" => "Limitless Mobile",
            "lycamobile" => "Lycamobile",
            "metrotmobile" => "Metro by T-Mobile",
            "mintmobile" => "Mint Mobile",
            "mobi" => "Mobi",
            "nemont" => "Nemont",
            "net10" => "Net10",
            "nex-techwireless" => "Nex-Tech Wireless",
            "nntcwireless" => "NNTC Wireless",
            "northernpacificwireless" => "Northern Pacific Wireless",
            "northwestcell" => "NorthwestCell",
            "nvc" => "NVC",
            "pinebeltwireless" => "Pine Belt Wireless",
            "pinecellular" => "Pine Cellular",
            "pioneercellular" => "Pioneer Cellular",
            "ptci" => "PTCI",
            "puretalk" => "Pure Talk",
            "redzonewireless" => "Redzone Wireless",
            "republicwireless" => "Republic Wireless",
            "rockwireless" => "Rock Wireless",
            "rtccommunications" => "RTC Communications",
            "silverstarcommunications" => "Silver Star Communications",
            "simplemobile" => "Simple Mobile",
            "snakeriverpcs" => "Snake River PCS",
            "southernlinc" => "Southern Linc",
            "spectrummobile" => "Spectrum Mobile",
            "straighttalk" => "Straight Talk",
            "stratanetworks" => "STRATA Networks",
            "t-mobileus" => "T-Mobile US",
            "tampnet" => "Tampnet",
            "ting" => "Ting",
            "thumbcellular" => "Thumb Cellular",
            "tracfone" => "TracFone",
            "ultramobile" => "Ultra Mobile",
            "unionwireless" => "Union Wireless",
            "unitedwireless" => "United Wireless",
            "uscellular" => "U.S. Cellular",
            "verizonwireless" => "Verizon Wireless",
            "viaerowireless" => "Viaero Wireless",
            "visible" => "Visible",
            "vtelwireless" => "VTel Wireless",
            "westcentralwireless" => "West Central Wireless",
            "wue" => "WUE",
            "xfinitymobile" => "Xfinity Mobile",
            "other" => "Other"
        );

        $this->attendance_init();
        $this->customer_information();
        $this->program_status();
        $this->programs_init();
        $this->email_templates_init();
        $this->import_users();
        $this->cron_schedules();
        $this->admin_settings();
        $this->staff_member_access();
        $this->easy_pos();
    }

    public function admin_settings() {
        require_once GY_CRM_PLUGIN_DIR . 'models/admin/settings.php';

        new AdminSettings();
    }

    public function cron_schedules() {
        require_once GY_CRM_PLUGIN_DIR . 'models/admin/cron_schedules.php';

        new CronSchedules($this->price_per_hour);
    }

    public function attendance_init()
    {
        require_once GY_CRM_PLUGIN_DIR . 'models/admin/attendance.php';

        new Attendance($this->slot_week);
    }

    public function email_templates_init()
    {
        require_once GY_CRM_PLUGIN_DIR  . 'models/admin/email_templates.php';

        new EmailTemplates($this->price_per_hour);
    }

    public function customer_information()
    {
        require_once GY_CRM_PLUGIN_DIR . 'models/admin/customer_information.php';
        
        new get_customer_information($this->slot_week, $this->team_level, $this->carrier_options);
    }

    public function programs_init()
    {
        require_once GY_CRM_PLUGIN_DIR . 'models/admin/programs.php';

        new ProgramClasses($this->price_per_hour);
    }

    public function import_users()
    {
        require_once GY_CRM_PLUGIN_DIR . 'models/admin/import_users.php';

        new ImportUsers();
    }

    public function program_status() {
        require_once GY_CRM_PLUGIN_DIR . 'models/admin/program_status.php';

        new ProgramStatus($this->slot_week);
    }

    public function staff_member_access() {
        require_once GY_CRM_PLUGIN_DIR . 'models/admin/staff_member_access.php';

        new StaffAccess();
    }

    public function easy_pos() {
        require_once GY_CRM_PLUGIN_DIR . 'models/admin/easy_pos.php';

        new EasyPos();
    }

}
