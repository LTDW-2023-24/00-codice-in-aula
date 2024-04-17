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

        return $this->ownedFeatures[array_key_first($this->ownedFeatures)];
    }

    public function emit_create() {
        
        $result = "";
        $result .= "CREATE TABLE {$this->name} (";
        
        
        foreach($this->ownedFeatures as $name => $attribute) {
            $result .= $attribute->emit_create().", ";
        }

        // $result = substr($result, 0, -2);
        
        $result .= " PRIMARY KEY({$this->get_primary_key()->name})";
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

class Reference extends Feature {
    private
        $entity;
    
        public function __construct($name, $entity) {
            $this->name = $name;
            $this->entity = $entity;

            return $this;
        }

        public function emit_create() {

            $result = "{$this->name} ";

            $type = $this->entity->get_primary_key()->type;

            $result .= $type;
            if ($type == VARCHAR) {
                $result .= "({$this->entity->get_primary_key()->length})";
            }

            return $result;
        }

}

$group = (new Entity("group"))
    ->add(new Attribute("id", INT))
    ->add(new Attribute("name", VARCHAR, 50))
    ->add(new Attribute("description", TEXT));

$user = (new Entity("user"))
    ->add(new Attribute('id',INT))
    ->add(new Attribute('name', VARCHAR, 50))
    ->add(new Attribute('surname', VARCHAR, 100));

echo $group->emit_create();


?>