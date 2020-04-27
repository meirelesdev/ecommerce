<?php
//ESTUDAR ESTA CLASS MAIS A FUNDO!
namespace Hcode;

class Model {

    private $values = [];

    public function __call($name, $args) {

        $method = substr($name, 0, 3);
        $fieldName = substr($name, 3, strlen($name));

        switch($method){
        case "get":
            return $this->values[$fieldName];
        break;

        case "set":
            $this->values[$fieldName] = $args[0];
        break;
        }
    }

    public function setData($data = array()) {

        foreach($data as $key => $value){
            // Esta parte é muito interessante pois vai criar os metodos set
            // para todos as chaves retornadas do banco dinamicamente.
            // as chaves que envolvem a string set concatenada com o $key
            // Formarao o nome do metodo. e o value esta sendo passado para dentro do metodo.
            $this->{"set".$key}($value);
        }
    }

    public function getValues() {
        
        return $this->values;
    }
}
?>
