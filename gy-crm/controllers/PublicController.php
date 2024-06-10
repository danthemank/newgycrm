<?php 

class PublicController {
    public $carrier_options;
    public $suffix_options;
    public $gender_options;
    public $price_per_hour;
    public $slot_week;
    
    public function __construct() {
        $this->slot_week = ['_slot_time_monday' => 'MON', '_slot_time_tuesday' => 'TUE', '_slot_time_wednesday' => 'WED', '_slot_time_thursday' => 'THU', '_slot_time_friday' => 'FRI', '_slot_time_saturday' => 'SAT', '_slot_time_sunday' => 'SUN'];

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

        $this->suffix_options = array(
            '' => 'None',
            'Jr.' => 'Jr.',
            'Sr.' => 'Sr.',
            'I' => 'I',
            'II' => 'II',
            'III' => 'III',
            'IV' => 'IV',
            'V' => 'V',
            'Other' => 'Other'
        );

        $this->gender_options = array(
            'Female' => 'Female',
            'Male' => 'Male',
            'Non-Binary' => 'Non-Binary',
            'Prefer not to say' => 'Prefer not to say'
        );

        $this->price_per_hour = ['0.30' => get_option('halfhour_week'), 1 => get_option('onehour_week'), '1.30' => get_option('onehalfhour_week'), '2' => get_option('twohour_week'), '3' => get_option('threehour_week'), '4' => get_option('fourhour_week'), '5' => get_option('fivehour_week'), '6' => get_option('sixhour_week'), '7' => get_option('sevenhour_week'), '8' => get_option('eighthour_week'), '9' => get_option('ninehour_week'), '12' => get_option('twelvehour_week'), '15' => get_option('fifteenhour_week'), '20' => get_option('twentyhour_week'),];

        $this->registration_init();
        $this->profile_editing_init();
        $this->billing();
    }

    public function registration_init()
    {
        require_once GY_CRM_PLUGIN_DIR . 'models/public/registration.php';

        new Registration($this->carrier_options, $this->suffix_options, $this->gender_options, $this->slot_week);
    }

    public function profile_editing_init()
    {
        require_once GY_CRM_PLUGIN_DIR . 'models/public/profile_editing.php';

        new ProfileEditing($this->carrier_options, $this->suffix_options, $this->gender_options);
    }

    public function billing() {
        require_once GY_CRM_PLUGIN_DIR . 'models/public/billing.php';

        new Billing($this->price_per_hour);
    }

}
