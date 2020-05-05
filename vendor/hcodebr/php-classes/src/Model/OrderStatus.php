<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class OrderStatus extends Model {

    const PAGO = 3;
    const ENTREGUE = 4;
    const EM_ABERTO = 1;
    const AGUARDANDO_PAGAMENTO = 2;
    
    public static function listAll() {
        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_ordersstatus ORDER BY desstatus");
    }
}

?>