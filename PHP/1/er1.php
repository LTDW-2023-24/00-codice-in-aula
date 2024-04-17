<?php


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

    public function get_primary_key() {

        foreach($this->ownedFeatures as $attribute) {
            if ($attribute->is_primary_key()) {
                return $attribute;
            }
        }

    }
 
    public function emit_create() {

        $result = "";
        $result .= "CREATE TABLE {$this->name} (";
        
        foreach($this->ownedFeatures as $attribute) {
            $result .= $attribute->emit_create().", ";
        }

        // $result = substr($result, 0, -2);

        $result .= " PRIMARY KEY()"
        $result .= ")";

        return $result;
    }
}

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

$user = (new Entity("user"))
    ->add(new Attribute("id", INT))
    ->add(new Attribute("name", VARCHAR, 100))
    ->add(new Attribute("surname", VARCHAR, 100));

?>