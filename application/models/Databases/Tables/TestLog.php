<?phpclass Databases_Tables_TestLog extends Zend_Db_Table{    protected $_name = 'test-log';    var $log_val;            function AddLog()    {        $row = $this->createRow();        $row->log_time = date("Y-m-d H:i:s");        $row->log_val = $this->log_val;        $row_id = $row->save();                return $row_id;    }}