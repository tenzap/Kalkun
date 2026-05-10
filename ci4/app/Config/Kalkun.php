<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Kalkun extends BaseConfig
{
    public string $css_path;
    public string $js_path;
    public string $img_path;
    public string $csv_path;
    public string $sound_path;

    public function __construct(){
        parent::__construct();

        $this->css_path = config('App')->baseURL . '../../media/css/';
        $this->js_path = config('App')->baseURL . '../../media/js/';
        $this->img_path = config('App')->baseURL . '../../media/images/';
        $this->csv_path = config('App')->baseURL . '../../media/csv/';
        $this->sound_path = config('App')->baseURL . '../../media/sound/';
    }

}
