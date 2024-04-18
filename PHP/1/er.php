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

class Schema extends namedElement {

    private
        $ownedEntities = array();

    public function __construct($name) {
        $this->name = $name;

        return $this;
    }

    public function add($entity) {
        $entity->set_parent($this);
        $this->ownedEntities[$entity->name] = $entity;
        
        return $this;
    } 

    public function get_entity_by_name($entity_name) {
        return $this->ownedEntities[$entity_name];
    }

    public function emit_create() {

        $result = "--\n-- Generated on ".date('d.m.Y H:i')."\n--\n--\n\n";

        foreach($this->ownedEntities as $entity){

            $result .= "-- TABLE {$entity->name} \n ";
            $result .= $entity->emit_create()."\n\n";

        }

        return $result;
    }

}

class Entity extends namedElement {
    private 
        $ownedFeatures = array(),
        $parent = null;
    
    public function __construct($name) {
        $this->name = $name;

        return $this;
    }

    public function get_parent() {
        return $this->parent;
    }

    public function set_parent($parent) {
        $this->parent = $parent;

        return $this; 
    }
    
    public function add($feature) {

        $feature->set_parent($this);
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
    protected 
        $type,
        $length,
        $parent = null;
    
    public function get_parent() {
        return $this->parent;
    }

    public function set_parent($parent) {
        $this->parent = $parent;

        return $this; 
    }

    

}

class Attribute extends Feature {

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
        $entity = null,
        $entity_name;
    
        public function __construct($name, $entity_name) {
            
            $this->name = $name;
            $this->entity_name = $entity_name;

            return $this;
        }

        public function entity_is_null() {
            return ($this->entity == null);
        }

        public function set_entity($entity) {
            $this->entity = $entity;
        }

        public function get_entity_name() {
            return $this->entity_name;
        }

        public function emit_create() {

            $this->entity = $this->get_parent()->get_parent()->get_entity_by_name($this->entity_name);

            $result = "{$this->name} ";

            $type = ($this->entity->get_primary_key())->type;

            $result .= $type;
            if ($type == VARCHAR) {
                $result .= "({$this->entity->get_primary_key()->length})";
            }

            return $result;
        }

}

Header("Content-type: text/plain");

$schema = (new Schema("data_model")) 
    ->add((new Entity("group"))
        ->add(new Attribute("id", VARCHAR, 1000))
        ->add(new Attribute("name", VARCHAR, 50))
        ->add(new Attribute("description", TEXT))
    )
    ->add((new Entity("user"))
        ->add(new Attribute('id',INT))
        ->add(new Attribute('name', VARCHAR, 50))
        ->add(new Attribute('surname', VARCHAR, 100))
        ->add(new Reference('group', 'group'))
);

echo $schema->emit_create();

?>