<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



abstract class namedElement {
    private $name;



    public function getName() {
        return $this->name;
    }
}


class Entity extends namedElement {

    private $ownedFeatures = array();

    public function __construct($name) {
        $this->name = $name;
    }

    public function add($feature) {
        $this->ownedFeatures[$feature->getName()] = $feature;
    }

    public function emit_create() {

        $result = "";
        $result .= "CREATE TABLE {$this->name} (";
        /* iterazione su ownedFeatures */
        $result .= ")";

        return $result;
    }
}

abstract class Feature extends namedElement {

}



class Attribute extends Feature {
    private $type;

    public function __construct($name, $type) {
        $this->type = $type;
    } 

}






$user = new Entity("user");
echo $user->emit_create();

?>