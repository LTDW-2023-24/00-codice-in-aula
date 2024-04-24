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
        $debug = false,
        $ownedEntities = array(),
        $tables = array(),
        $connection,
        $result,
        $db_host,
        $db_username,
        $db_passwd;


    public function __construct($name, $host, $username, $passwd) {
        $this->name = $name;
        $this->db_host = $host;
        $this->db_username = $username;
        $this->db_passwd = $passwd;

        $this->set_connection($host, $username, $passwd, $name);

        $result = $this->connection->query("SHOW TABLES");

        if (!$result) {
            die("Query error ".$this->connection->error);
        }

        if ($result->num_rows > 0) {
            while ($data = $result->fetch_array()) {
                $this->tables[$data[0]] = true;
            }
        }

        return $this;
    }

    public function debug() {
        $this->debug = true;

        return $this;
    }

    private function set_connection($host, $username, $passwd, $name) {

        $this->connection = new mysqli($host, $username, $passwd, $name);
        if ($this->connection->connect_error) {
            die("Connection error: ".$this->connection->connect_error);
        }   

    }

    public function exist_table($table) {
        return isset($this->tables[$table]);
    }

    public function get_connection() {
        return $this->connection;
    }

    public function query($query) {

            $this->result = $this->connection->query($query);

            if(!$this->result) {
                if ($this->debug) {
                    echo $query, "<br><br>";
                    die("Query error ".$this->connection->error);
                }
                
            }

            return $this;

    }

    public function get_result() {

        $result = array();

        if ($this->result->num_rows > 0) {
            while($data = $this->result->fetch_assoc()) {
                $result[] = $data;
            }
        }

        return $result;

    }

    public function add($entity) {
        $entity->set_parent($this);
        $this->ownedEntities[$entity->name] = $entity;
        
        return $this;
    } 

    public function get_entity_by_name($entity_name) {
        return $this->ownedEntities[$entity_name];
    }

    public function commit() {

        foreach($this->ownedEntities as $entity) {
            $entity->commit();
        }

        return $this;

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

    public function commit() {

        
        if (!$this->get_parent()->exist_table($this->name)) {
            $this->get_parent()->query($this->emit_create());
            
        }

        return $this;

    }

    public function emit_create() {
        
        $result = "";
        $result .= "CREATE TABLE `{$this->name}` (";
        
        
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

        $result = "`{$this->name}` {$this->type}";
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

            $result = "`{$this->name}` ";

            $type = ($this->entity->get_primary_key())->type;

            $result .= $type;
            if ($type == VARCHAR) {
                $result .= "({$this->entity->get_primary_key()->length})";
            }

            return $result;
        }

}


$schema = (new Schema("tdw_1", "localhost", "root", "root"))
    ->debug()
    ->add((new Entity("content"))
        ->add(new Attribute("id", INT))
        ->add(new Attribute("title", VARCHAR, 100))
        ->add(new Attribute("subtitle", VARCHAR, 100))
        ->add(new Attribute("slogan", VARCHAR, 100))
        ->add(new Attribute("b_text", VARCHAR, 20))
        ->add(new Attribute("b_link", VARCHAR, 100))
        ->add(new Attribute("image", VARCHAR, 255))
        ->add(new Attribute("body", TEXT))
    )
    ->commit();



?>