<?php

class MyXML
{
    protected $_stack = array();
    protected $_catlist = array();
    protected $_file = "";
    protected $_parser = null;

    protected $_current = null;

    public function __construct($file=null)
    {
        $this->_file = $file;

        $this->_parser = xml_parser_create("UTF-8");
        xml_set_object($this->_parser, $this);
        xml_set_element_handler($this->_parser, "startTag", "endTag");
        xml_set_character_data_handler($this->_parser, "characterData");
    }

    public function startTag($parser, $name, $attrs)
    {
        if(!empty($name)){
            if($name=='RECORD'){
                $this->_stack[] = array();
            }
            $this->_current = $name;
        }
    }

    public function endTag($parser, $name)
    {
        if(!empty($name)){
            $this->_current = null;
        }
    }

    public function characterData($parser, $data) {
        $data = trim($data);
        if(!empty($data)) {
            if ($this->_current == 'TITLE' || $this->_current == 'ADDRESS' || $this->_current == 'DESCRIPTION'
                || $this->_current == 'JOURNAL' || $this->_current == 'VOLUME' ||  $this->_current == 'CATEGORY'
                ||  $this->_current == 'LINK' ||  $this->_current == 'LAT' ||  $this->_current == 'LON') {
                if($this->_current=='CATEGORY'){
                    $catlist = array();
                    $cats = explode(",", $data);
                    foreach($cats as $cat){
                        $cat = trim($cat);
                        $idx = array_search($cat, $this->_catlist);
                        if($idx===FALSE){
                            $this->_catlist[] = $cat;
                            $catlist[] = count($this->_catlist)-1;
                        }else{
                            $catlist[] = $idx;
                        }
                    }
                    $this->_stack[count($this->_stack)-1]['CATLIST'] = $catlist;
                    $this->_stack[count($this->_stack)-1]['CATEGORY'] = $data;
//                }elseif($this->_current=='ADDRESS'){
//                    //$latlon = $this->getLatLon($data);
//                    $latlon = array(rand(0, 7000)/100, rand(0, 18000)/100);
//                    if($latlon){
//                        $this->_stack[count($this->_stack)-1]['LAT'] = $latlon[0];
//                        $this->_stack[count($this->_stack)-1]['LON'] = $latlon[1];
//                    }
//                    $this->_stack[count($this->_stack)-1]['ADDRESS'] = $data;
                }else{
                    $this->_stack[count($this->_stack)-1][$this->_current] = $data;
                }
            }
        }
    }

    public function getLatLon($address){
        $address_list = explode(",", $address);
        $json = 0;
        while(1){
            $address = implode(",", $address_list);
            $address = $this->cleanStr($address);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false");
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $json = curl_exec($ch);
            curl_close($ch);

//            $json = @file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false");
            if($json===FALSE){
                return array(0,0);
            }
            $json = json_decode($json);

            if(count($json->{'results'}) || count($address_list)==1){
                break;
            }
            array_pop($address_list);
        }

        if(!$json || !$json->{'results'} || !count($json->{'results'}))
            return array(0,0);
        $lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
        $lon = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
        return array($lat, $lon);
    }
    public function getLatLon1($address){
        $address = $this->cleanStr($address);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false");
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $json = curl_exec($ch);
        curl_close($ch);
        if($json===FALSE){
            return array(0,0);
        }
        $json = json_decode($json);
        if(!$json || !$json->{'results'} || !count($json->{'results'}))
            return array(0,0);
        $lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
        $lon = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
        return array($lat, $lon);
    }
    public function cleanStr($string) {
        $string = str_replace(' ', '+', $string);
        $string = preg_replace('/[^A-Za-z0-9\+]/', '', $string);
        //$string = preg_replace('/++/', '+', $string);
        return $string;
    }

    public function parse(){
        if (!($handle = fopen($this->_file, "r"))) {
            die("could not open XML input");
        }
        while($data = fread($handle, 4096)) {
            xml_parse($this->_parser, $data);
        }
        xml_parser_free($this->_parser);
    }

    public function getList(){
        return $this->_stack;
    }

    public function getCatList(){
        return $this->_catlist;
    }
}