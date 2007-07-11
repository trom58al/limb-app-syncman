<?php
/**
 * Limb Web Application Framework
 *
 * @link http://limb-project.com
 *
 * @copyright  Copyright &copy; 2004-2007 BIT
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 * @version    $Id: lmbOciQueryStatement.class.php 4994 2007-02-08 15:36:08Z pachanga $
 * @package    dbal
 */
lmb_require('limb/dbal/src/drivers/lmbDbQueryStatement.interface.php');
lmb_require(dirname(__FILE__) . '/lmbOciStatement.class.php');
lmb_require(dirname(__FILE__) . '/lmbOciRecord.class.php');
lmb_require(dirname(__FILE__) . '/lmbOciRecordSet.class.php');
lmb_require(dirname(__FILE__) . '/lmbOciArraySet.class.php');

class lmbOciQueryStatement extends lmbOciStatement implements lmbDbQueryStatement
{
  function paginate($start, $limit)
  {
    // Extract the fields being selected (swiped from PEAR::DB)
    $sql = "SELECT * FROM ({$this->sql}) WHERE 1=1";
    $stmt = new lmbOciStatement($this->connection, $sql);
    $queryId = $this->connection->executeStatement($stmt->getStatement());

    $ncols = oci_num_fields($queryId);

    $cols = array();
    for($i = 1; $i <= $ncols; $i++)
      $cols[] = '"' . oci_field_name($queryId, $i) . '"';
    $fields = implode(',', $cols);

    // Build the paginated query...
    $sql = "SELECT $fields FROM".
       "  (SELECT rownum as linenum, $fields FROM".
       "      ({$this->sql})".
       '  WHERE rownum <= '. ($start + $limit) .
       ') WHERE linenum >= ' . ++$start;

    $this->sql = $sql;
  }

  function addOrder($sort_params)
  {
    if(preg_match('~\s+ORDER\s+BY\s+~i', $this->sql))
      $this->sql .= ',';
    else
      $this->sql .= ' ORDER BY ';

    foreach($sort_params as $field => $order)
      $this->sql .= "$field $order,";

    $this->sql = rtrim($this->sql, ',');
  }

  function count()
  {
    $stmt = clone $this;
    $stmt->sql = "SELECT COUNT(*) AS THEROWC FROM ($this->sql)";
    $stmt->hasChanged = true;
    $queryId = $stmt->execute();

    $row = oci_fetch_assoc($queryId);
    $stmt->free();
    return $row['THEROWC'];
  }

  function getOneRecord()
  {
    $queryId = $this->connection->executeStatement($this->getStatement());
    $values = oci_fetch_array($queryId, OCI_ASSOC+OCI_RETURN_NULLS);
    oci_free_statement($queryId);
    if(is_array($values))
      return new lmbOciRecord($values);
  }

  function getOneValue()
  {
    $queryId = $this->connection->executeStatement($this->getStatement());
    $row = oci_fetch_array($queryId, OCI_NUM+OCI_RETURN_NULLS);
    oci_free_statement($queryId);
    if(is_array($row) && isset($row[0]))
      return $row[0];
  }

  function getOneColumnAsArray()
  {
    $column = array();
    $queryId = $this->connection->executeStatement($this->getStatement());
    while(is_array($row = oci_fetch_array($queryId, OCI_NUM+OCI_RETURN_NULLS)))
      $column[] = $row[0];
    oci_free_statement($queryId);
    return $column;
  }

  function getRecordSet()
  {
    return new lmbOciRecordSet($this->connection, $this);
  }
}

?>