<?php

namespace Hcode;

use Rain\Tpl;

class Page {

    private $tpl;
    private $options = [];
    private $defaults = [
        "header"=>true,
        "footer"=>true,
        "data" => [],
    ];

    public function __construct($opts = array(), $tpl_dir = '/views'){
        
        // $this->default["data"]["session"] = $_SESSION;

		$this->options = array_merge($this->defaults, $opts);

		$config = array(
			"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"].$tpl_dir.DIRECTORY_SEPARATOR,
			"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache".DIRECTORY_SEPARATOR,
			"debug"         => false
	    );

		Tpl::configure( $config );

		$this->tpl = new Tpl;

		$this->setData($this->options["data"]);
        // Aqui incluimos o header no template, se a opção seja true
        // Na rota de login vem false, logo não sera carregado.
		if ( $this->options["header"] === true ) $this->tpl->draw("header");

	}

    private function setData($data = array()){
        
        foreach ($data as $key => $value) {
            $this->tpl->assign($key, $value);
        }
        
    }

    public function setTpl($name, $data = array(), $returnHTML = false ){

        $this->setData($data);
                
        return $this->tpl->draw($name, $returnHTML);
    }

    public function __destruct(){

        // Aqui incluimos o footer no template, se a opção seja true
        // Na rota de login vem false, logo não sera carregado.
        if ( $this->options["footer"] === true ) $this->tpl->draw("footer");

    }
}
?>




