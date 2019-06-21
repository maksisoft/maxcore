<?php
namespace Maxcore\Sys;

use \Maxcore\Sys\Session;
use \Maxcore\Db\Model;

/**
 * @author Maksisoft
 */
class User extends Model {

    public $info;
    public $userId;

    public function __construct() {

        parent::__construct();

        $session = new Session();

        $this->userId = $session->get("panel_user_id");

        $this->info = $this->table("users")->find($this->userId, true);
    }

    public function setHash($code) {


        $this->col["hash"] = $code;

        $this->update();
    }

}
