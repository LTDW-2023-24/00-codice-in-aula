<?php

echo "QUI";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

DEFINE("INT","INT");
DEFINE("VARCHAR","VARCHAR");
DEFINE("BOOL","BOOL");
DEFINE("TEXT","TEXT");



abstract class namedElement {
    protected $name;

    public function getName() {
        return $this->name;
    }
}


class Entity extends namedElement {

    private $ownedFeatures = array();
    
    public function __construct($name) {
        $this->name = $name;

        return $this;
    }
    
    public function add($feature) {

        $this->ownedFeatures[$feature->name] = $feature;
        return $this;
    }
    
    
    public function emit_create() {
        
        $result = "";
        $result .= "CREATE TABLE {$this->name} (";
        /*
        
        foreach($this->ownedFeatures as $name => $attribute) {
            $result .= $attribute->emit_create().", ";
        }

        // $result = substr($result, 0, -2);

        $result .= " PRIMARY KEY()"
        $result .= ")";

        return $result;

        */
    }

    
}

echo "QUI 66666";




abstract class Feature extends namedElement {

}



class Attribute extends Feature {
    private 
        $type,
        $length;

    public function __construct($name, $type, $length = 0) {
        $this->name = $name;
        $this->type = $type;
        $this->length = $length;

        return $this;
    } 


    public function emit_create() {

        $result = "{$this->name} {$this->type}";
        if ($this->type == VARCHAR) {
            $result .= "({$this->length})";
        }

        return $result;
    }

}



echo " qui 3";

?>