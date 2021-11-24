<?php
/**
 * @category  Database Access
 * @package   Db_model
 * @author Tumuhimbise Usher Godwin
 * @copyright Copyright (c) 2020-2021
 * @version   2.0
 */
class Db_model 
{
    private $_conn;

    private  $_insertQuery = "";

    private   $_updateQuery = "";

    private  $_deleteQuery = "";

    private   $_selectQuery = "";

    private  $_cols = array();

    private   $values = array();

    private  $place_holders = array();

    private  $insert_place_holders = array();

    private   $model;

    private   $named_keys = array();

    private   $_where = array();

    private   $_whereCols = array();

    private   $_orderBy = "";

    private   $_operator = "";

    private   $_rows = array();

    private   $_updateData = array();

    private   $_updateQueryData = array();

    private   $_joinWhere = array();

    private   $_whereTable = "";

    private $_and = array();
    
    private $_andOperator = "";

    private $_andKeys = array();
    /**
     * @var array
     */
    private $_andValues = array();

    public function __construct() {
        $this->model = new Model();
        global $config;
        $this->_conn = $this->connect($config);
    }

    private function connect(array $config) {
        $dsn = "mysql:host=".$config["HOST"].";dbname=".$config['DBNAME'];
        $conn = new PDO($dsn, $config["USERNAME"], $config["PASSWORD"], array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    }
        /**
     * Reset states after an execution
     *
     * @return Db_model Returns the current instance.
     */
    private function reset() {
        $this->_insertQuery = "";
        $this->_updateQuery = "";
        $this->_deleteQuery = "";
        $this->_selectQuery = "";
        $this->_cols = array();
        $this->values = array() ;
        $this->place_holders = array();
        $this->insert_place_holders = array();
        $this->named_keys = array();
        $this->_where = array();
        $this->_whereCols = array();
        $this->_orderBy = "";
        $this->_operator = "";
        $this->_updateData = array();
        $this->_updateQueryData = array();
        $this->_joinWhere = array();
        $this->_whereTable = "";
        $this->_andOperator = "";
        $this->_andKeys = array();
        return $this;
    }

    /**
     * @param string $tableName The name of the table to insert the data to.
     * @param array $tableData An associative array containing column names and their data to insert in the table.
     *
     * @return array|false ::rowCount|int Returns number of affected rows
     * @throws PDOException
     */
    public  function insert(string $tableName, array $tableData) {
        $this->_conn->beginTransaction();
        if(!is_array($tableData)) {
            return false;
        }
            $stmt = $this->_conn->prepare($this->buildInsert($tableName, $tableData));
            $stmt->execute($tableData);
            $this->_conn->commit();
            if ($stmt->rowCount() > 0){
             $this->_rows = array_merge($this->_rows, array("Insert" => $stmt->rowCount()));
            }
            else
            $stmt->errorInfo();
            $this->reset();
        return $this->_rows;

    }

    /**
     * @param string $columns A string of columns to return separated by commas
     * @param string $tableName A string containing the table name to select data from.
     * @param int|null $limit An optional integer of number of rows to select from the table.
     * @return array     An associative array of returned rows
     * @throws Exception
     */
    public function getValues(string $columns, string $tableName, int $limit = null) {
        if(!is_string($columns)):
            throw new Exception("Expected a string of columns separated by commas");
        endif;    
        $stmt = $this->_conn->prepare($this->buildSelect($columns, $tableName, $limit));
        empty($this->_where) ? $stmt->execute() :  $stmt->execute($this->_where);
        $this->reset();
        return $stmt->fetchAll();
    }
     /**
     * @param string $columns A string of columns to return separated by commas
     * @param string $tableName A string containing the table name to select data from.
     * @param int $limit An optional integer of number of rows to select from the table.
     * @return object     An Object with property and value representation of the returned rows
     * @throws Exception When the where method is not called first
     */
    public function getOneRow(string $columns, string $tableName, int $limit = 1) {
        if(!is_string($columns)):
            throw new Exception("Expected a string of columns separated by commas");
        endif;
        if(empty($this->_where)):
            throw new Exception("Expected a condition to be passed to the where method first, 0 given.");
        endif;
        $stmt = $this->_conn->prepare($this->buildSelect($columns, $tableName, $limit));
        empty($this->_where) ? $stmt->execute() :  $stmt->execute($this->_where);
        $stmt->setFetchMode(PDO::FETCH_OBJ);
        $this->reset();
        return $stmt->fetch();
    }
    /**
     * Helper function to close the database connection
     * @return null
     */
    private function disconnect() {
        return $this->_conn = null;
    }
    /**
     * @param array $wherePos  An associative array with the where column and the condition in the first index (0). The Optional second index (1) takes the AND clause with the column and the condition. The Optional third index takes the OR with column name and the condition.
     * @param string $operator Takes the operator to be used. Default is =;
     * @return array           An associative array of column and condition for WHERE, AND , OR
     */
    public function where(array $wherePos, $operator = '=') {
        $this->_where = $wherePos;
        $this->_operator = $operator;
        return $this->_where;
    }

    /**
     * The where() method should be called first with only 2 conditons ie WHERE, AND
     * @param array $extraConditions  An associative array with extra 2 (AND) conditions
     * @param string $operator Takes the operator to be used. Default is =;
     * @return array           An associative array of column and condition for AND AND
     */
    public function and(array $extraConditions, $operator = '=') {
        $this->_and = $extraConditions;
        $this->_andOperator = $operator;
        return $this->_and;
    }
    /**
     * The where() and or and() methods should be called first
     * @param string $orderColumns  A string of column name(s) separated by commas if many to base on for ordering.
     * @param string $order         A string representing the type of order for the rows. Default is ASC.
     * @return string               An orderby string to complete the select query
     */
    public function orderBy(string $orderColumns, $order = "ASC") {
        $this->_orderBy = $orderColumns." ".$order;
        return $this->_orderBy;
    }

    /**
     * @param string $column A string containing the column name to select
     * @param string $tableName A string containing the table name to select data from.
     * Call The where method if you have a condition to base on in selection of data
     * @param int $limit A string containing the limit of data to select
     * @return string A string of data for a single column
     * @throws Exception
     * The where() and or and() methods should be called first if a condition is reqiured
     */
    public function getOne(string $column, string $tableName, int $limit = 1) {
        if(!preg_match("/^[a-zA-Z-_]*$/", $column)):
            throw new Exception("Expected only 1 column name to be parsed");
        endif;
        if(empty($this->_where)):
            throw new Exception("Expected a condition to be passed to the where method first:: 0 given");
        endif;    
        $stmt = $this->_conn->prepare($this->buildSelect($column, $tableName, $limit));
        $stmt->execute($this->_where);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();
        $this->reset();
        return $result[$column];
    }
     /**
     * @param string $column A string of columns to return separated by commas
     * @param string $tableName A string containing the table name to select data from.
     * @param int $limit An optional integer of number of rows to select from the table.
     * @return object     An Object with property and value representation of the returned row
     * @throws Exception When the where method is not called first
     * The where() and or and() methods should be called first if a condition is reqiured
     */
    public function getObject(string $column, string $tableName, int $limit = 1) {
        if(!preg_match("/^[a-zA-Z-_]*$/", $column)):
            throw new Exception("Expected only 1 column name to be parsed");
        endif;
        if(empty($this->_where)):
            throw new Exception("Expected a condition to be passed to the where method first:: 0 given");
        endif;    
        $stmt = $this->_conn->prepare($this->buildSelect($column, $tableName, $limit));
        $stmt->execute($this->_where);
        $stmt->setFetchMode(PDO::FETCH_OBJ);
        $this->reset();
        return $stmt->fetch();
    }

    /**
     * An abstract function to build the inset query
     * @param string $tableName
     * @param array $tableData
     * @return string
     */
    private function buildInsert(string $tableName, array $tableData) {
        if(!empty($tableData) && !empty($tableName)):
            foreach ($tableData as $key => $columns) {
                $this->_cols[] = $key;
                $this->insert_place_holders[] = $key;
            }
            $this->_insertQuery = "INSERT INTO $tableName (".implode(',',$this->_cols).") VALUES (";
            foreach ($tableData as $values) {
                $this->values[] = $values;
            }
            $this->_insertQuery .= ":".implode(', :', $this->insert_place_holders);
            $this->_insertQuery .=")";
        endif;
        return $this->_insertQuery;    
    }

    /**
     * Abstraction class that will build the select query
     * @param string $columns
     * @param string $tableName
     * @param null $limit
     * @return string
     * @throws Exception
     */
    private function buildSelect(string $columns, string $tableName, $limit = null){
        $this->_selectQuery = "SELECT ".$columns." FROM ".$tableName;
        if(!empty($this->_where)):
            $this->named_keys =array_keys($this->_where);
            $this->_selectQuery .= " WHERE ".$this->named_keys[0]." $this->_operator :".$this->named_keys[0];
        if(count($this->_where) >= 2):
            $this->_selectQuery .= " AND ".$this->named_keys[1]." ".$this->_operator." :".$this->named_keys[1];
        endif;
        if(count($this->_where) >= 3):
            $this->_selectQuery .=" OR ".$this->named_keys[2]." ".$this->_operator." :".$this->named_keys[2];
        endif; 
        if (!empty($this->_and)):
            foreach ($this->_and as $values) {
                $this->_andValues[] = $values;
            }
            $this->_andKeys = array_keys($this->_and);
            $this->_selectQuery .= " AND ".$this->_andKeys[0]." $this->_andOperator :".$this->_andKeys[0];
            if (count($this->_and) >= 2):
                $this->_selectQuery .= " AND ".$this->_andKeys[1]." $this->_andOperator :".$this->_andKeys[1];
            endif;
            $this->_where = array_merge($this->_where, $this->_and);
            if (count($this->_and) > 2):
                throw new Exception("Expected at most 2 indexes in the array parsed to the and method");
            endif;
        endif;
        endif;  
        if(!empty($this->_orderBy)):
            $this->_selectQuery .=" ORDER BY ".$this->_orderBy;
        endif;    
        if($limit !== null):
            $this->_selectQuery .=" LIMIT ".$limit;
        endif;
        return $this->_selectQuery;
    }
    /**
     * Method that returns the number of affected rows
     * @return int   Number of the affected rows
     */
    public function affectedRows() {
        $affectedRows = 0;
        if(isset($this->_rows['Insert'])):
            $affectedRows = $this->_rows['Insert'];
        endif;
        if(isset($this->_rows['Delete'])):
            $affectedRows = $this->_rows['Delete'];
        endif;
        if(isset($this->_rows['Update'])):
            $affectedRows = $this->_rows['Update'];
        endif;            
        return $affectedRows;
    }

    /**
     * Abstract function to build the update query
     * @param string $tableName
     * @param array $updateData
     * @return string
     * @throws Exception When the data array is unset
     */
    private function buildUpdate(string $tableName, array $updateData) {
        $this->_updateData = array_merge($this->_updateData, $updateData);  
        $this->_cols = array_keys($this->_updateData);
        $count = count($this->_updateData);
        $this->_updateQuery = "UPDATE $tableName SET ".$this->_cols[0]." = :".$this->_cols[0];
        if($count == 0):
            throw new Exception("Expected at least 1 column and value to update");
        endif;        
        if($count > 1):
            $this->_updateQuery .=" , ".$this->_cols[1]." = :".$this->_cols[1];
        endif;    
        if($count > 2):
             $this->_updateQuery .=" , ".$this->_cols[2]." = :".$this->_cols[2];
        endif;     
        if($count > 3):
            $this->_updateQuery .=" , ".$this->_cols[3]." = :".$this->_cols[3];
        endif;    
        if($count > 4):
            $this->_updateQuery .=" , ".$this->_cols[4]." = :".$this->_cols[4];
        endif;    
        if($count > 5):
            $this->_updateQuery .=" , ".$this->_cols[5]." = :".$this->_cols[5];
        endif;    
        if($count >= 6):
            throw new Exception("You can only update 5 columns at once");
        endif;    
        if(!empty($this->_where)):
            if(count($this->_where) > 2):
                throw new Exception("Expected one or two indexes in the array parsed to the where method");
            endif; 
        $this->_updateQueryData = array_merge($this->_updateQueryData, $this->_updateData, $this->_where);
        foreach ($this->_updateQueryData as $key => $value) {
            $this->_whereCols[] = $key;
        }
        if(count($this->_updateQueryData) == 2 && count($this->_where) == 1):
            $this->_updateQuery .= " WHERE ".$this->_whereCols[1]." = :".$this->_whereCols[1];
        endif;
        if(count($this->_updateQueryData) == 3 && count($this->_where) == 1):
            $this->_updateQuery .= " WHERE ".$this->_whereCols[2]." = :".$this->_whereCols[2];
        endif;
         if(count($this->_updateQueryData) == 3 && count($this->_where) == 2):
            $this->_updateQuery .= " WHERE ".$this->_whereCols[1]." = :".$this->_whereCols[1]." AND ".$this->_whereCols[2]." = :".$this->_whereCols[2];
        endif;
        if(count($this->_updateQueryData) == 4 && count($this->_where) == 1):
            $this->_updateQuery .= " WHERE ".$this->_whereCols[3]." = :".$this->_whereCols[3];
        endif;
         if(count($this->_updateQueryData) == 4 && count($this->_where) == 2):
            $this->_updateQuery .= " WHERE ".$this->_whereCols[2]." = :".$this->_whereCols[2]." AND ".$this->_whereCols[3]." = :".$this->_whereCols[3];
        endif;
        if(count($this->_updateQueryData) == 5 && count($this->_where) == 1):
            $this->_updateQuery .= " WHERE ".$this->_whereCols[4]." = :".$this->_whereCols[4];
        endif;
         if(count($this->_updateQueryData) == 5 && count($this->_where) == 2):
            $this->_updateQuery .= " WHERE ".$this->_whereCols[3]." = :".$this->_whereCols[3]." AND ".$this->_whereCols[4]." = :".$this->_whereCols[4];
        endif;
        if(count($this->_updateQueryData) == 6 && count($this->_where) == 1):
            $this->_updateQuery .= " WHERE ".$this->_whereCols[5]." = :".$this->_whereCols[5];
        endif;
         if(count($this->_updateQueryData) == 6 && count($this->_where) == 2):
            $this->_updateQuery .= " WHERE ".$this->_whereCols[4]." = :".$this->_whereCols[4]." AND ".$this->_whereCols[5]." = :".$this->_whereCols[5];
        endif;
        if(count($this->_updateQueryData) == 7 && count($this->_where) == 1):
            $this->_updateQuery .= " WHERE ".$this->_whereCols[6]." = :".$this->_whereCols[6];
        endif;
         if(count($this->_updateQueryData) == 7 && count($this->_where) == 2):
            $this->_updateQuery .= " WHERE ".$this->_whereCols[5]." = :".$this->_whereCols[5]." AND ".$this->_whereCols[6]." = :".$this->_whereCols[6];
        endif;
        endif;
        return $this->_updateQuery;        
    }

    /**
     * @param string $tableName The name of the table to update
     * @param array $updateData An associative array containing columns and column values to update. 9 columns  can be updated at once.
     * @return array Number of affected rows, Can be accessed by calling the affectedRows method.
     * @throws Exception When the where method is not called first
     * 
     */
    public function update(string $tableName, array $updateData) {
        if(!is_array($updateData)):
            throw new Exception("Expected the Update data to be of type array");
        endif;
        if(empty($this->_where)):
            throw new Exception("Expected the condition to be parsed first in the where method");
        endif; 
        $this->_conn->beginTransaction();
        $stmt = $this->_conn->prepare($this->buildUpdate($tableName, $updateData));
        $stmt->execute($this->_updateQueryData);
        $this->_conn->commit();
        $stmt->rowCount() > 0 ? $this->_rows = array_merge($this->_rows, array("Update" => $stmt->rowCount())) : null;
        $this->reset();
        return $this->_rows;
    }
    private function buildInsertMany(string $tableName, array $tableData) {
        if(!empty($tableData) && !empty($tableName)):
            foreach ($tableData[0] as $key => $columns) {
                $this->_cols[] = $key;
                $this->insert_place_holders[] = $key;
            }
            $this->_insertQuery = "INSERT INTO $tableName (".implode(', ',$this->_cols).") VALUES (";
            foreach ($tableData as $values) {
                $this->values[] = $values;
            }
            $this->_insertQuery .= ":".implode(', :', $this->insert_place_holders);
            $this->_insertQuery .=")";
        endif;
        return $this->_insertQuery;  
    }

    /**
     * @param string $tableName  The name of the table to insert values to
     * @param array $tableData  A Multi-dimensional array containing rows of data to insert
     * @return array An array of affected rows, accessed by calling the affectedRows method
     * @throws Exception When the data parsed is not of type array
     */
    public function insertMany(string $tableName, array $tableData) {
        $this->_conn->beginTransaction();
        if(!is_array($tableData)):
            throw new Exception("Expected insertion data to be of type array");
        endif;
        $stmt = $this->_conn->prepare($this->buildInsertMany($tableName, $tableData));
        foreach($tableData as $row) {
            $stmt->execute($row);
        }
        $this->_conn->commit();
        $stmt->rowCount() > 0 ? $this->_rows = array_merge($this->_rows, array("Insert" => $stmt->rowCount())) : null;
        $this->reset();
        return $this->_rows;   
    }

    /**
     * @param string $tableName A string containing the name of the table to delete a record from
     * @param string $operator An optional string containing the operator to use in the where clause, Default is "="
     * @return array ::rowCount    An array of affected rows, accessed by calling the affectedRows method.
     * @throws Exception When the where method is not called, and when the parsed array has more than 2 indexes
     */
    public function delete(string $tableName, string $operator = "=") {
        $this->_conn->beginTransaction();
        $stmt = $this->_conn->prepare($this->buildDelete($tableName, $operator));
        $stmt->execute($this->_where) ? $this->_rows = array_merge($this->_rows, array("Delete" => $stmt->rowCount())) : null;
        $this->_conn->commit();
        $this->reset();
        return $this->_rows;
    }

    /**
     * Abstract function to build the delete query
     * @param string $tableName
     * @param string $operator
     * @return string
     * @throws Exception When the where method is not called.
     * The where method should be called first
     */
    private function buildDelete(string $tableName, string $operator = "="){
        $this->_deleteQuery = "DELETE FROM ".$tableName;
        $this->_operator = $operator;
        if(empty($this->_where)):
            throw new Exception("Expected a condition to be parsed to where method first");
        endif;
        if(!empty($this->_where)):
            if(count($this->_where) > 2):
                throw new Exception("Expected at least one WHERE condition and or AND condition");
            endif;    
            foreach ($this->_where as $key => $value) {
                $this->place_holders[] = $key;
                }
                $this->_deleteQuery .= " WHERE ".$this->place_holders[0]." $this->_operator :".$this->place_holders[0];
            if(count($this->_where) == 2):
                $this->_deleteQuery .= " AND ".$this->place_holders[1]." ".$this->_operator." :".$this->place_holders[1];
            endif;
        endif;
        return $this->_deleteQuery;    
    }

    /**
     * @param string $tableName A string containing the name of the table to delete rows from.
     * @return bool True if all data is erased and false otherwise
     * Be Sure of the consequences resulting from calling this method.
     * All data is erased
     */
    public function deleteAll(string $tableName){
        $this->_conn->beginTransaction();
        $this->_deleteQuery = "TRUNCATE table ".$tableName;
        $stmt = $this->_conn->prepare($this->_deleteQuery);
        $stmt->execute();
        $stmt->rowCount() > 0 ? $this->_rows = array_merge($this->_rows, array("delete" => $stmt->rowCount())) : null;
        $this->_conn->commit();
        $this->reset();
        if ($stmt->rowCount() > 0) {
            return true;
        }else
            return false;
       
    }

    /**
     * @param string $tableName A string containing the name of the table to act on
     * @param array $tableData An array containing a status name and the value to change. Use logical True(1) and False(0) as values to mark trashed rows. The same method can be used to untrash a row.
     * @return array ::rowCount     An array containing the number of affected rows.
     * @throws Exception When the where method is not called.
     * The where method should be called first
     */
    public function trash(string $tableName, array $tableData){
        if(empty($this->_where)):
            throw new Exception("Expected the condition to be parsed first in the where method");
        endif;
        $this->_conn->beginTransaction();
        $stmt = $this->_conn->prepare($this->buildUpdate($tableName, $tableData));
        $stmt->execute($this->_updateQueryData);
        $this->_conn->commit();
        $stmt->rowCount() > 0 ? $this->_rows = array_merge($this->_rows, array("Update" => $stmt->rowCount())) : null;
        $this->reset();
        return $this->_rows;

    }

    /**
     * Abstract function to build the join query
     * @param array $tableColumns
     * @param array $tableNames An associative array of tablename and unique key of the table
     * @param string $type
     * @return string
     * @throws Exception
     */
    private function buildJoin(array $tableColumns , array $tableNames, string $type = "INNER JOIN"){
        $this->_selectQuery = "SELECT ".implode(", ",$tableColumns);
        foreach( $tableNames as $key => $names) {
            $this->_cols[] = $key;
            $this->values[] = $names;
        }
        $this->_selectQuery .= " FROM ".$this->_cols[0]." $type ".$this->_cols[1]." ON ".$this->_cols[0].".".$this->values[0]." = ".$this->_cols[1].".".$this->values[1];
        if(count($tableNames) > 2):
            $this->_selectQuery .=" $type ".$this->_cols[2]." ON ".$this->_cols[1].".".$this->values[1]." = ".$this->_cols[2].".".$this->values[2];
        endif; 
        if(count($tableNames) > 3):
            $this->_selectQuery .=" $type ".$this->_cols[3]." ON ".$this->_cols[2].".".$this->values[2]." = ".$this->_cols[3].".".$this->values[3];
        endif;
        if(count($tableNames) >=5):
            throw new Exception("You can only join a maximum of 4 tables with the same type of join");
        endif;           
        if(!empty($this->_where)):
            foreach($this->_where as $key => $value) {
                $this->_whereCols[] = $key;
            }
            $this->_selectQuery .=" WHERE ".$this->_whereCols[0]." = :".$this->_whereCols[0];
            if(count($this->_where) == 2):
                $this->_selectQuery .=" AND ".$this->_whereCols[1]." = :".$this->_whereCols[1];
            endif; 
            if(count($this->_where) == 3):
                $this->_selectQuery .=" OR ".$this->_whereCols[2]." = :".$this->_whereCols[2];
            endif;
        endif;
        if(!empty($this->_where) && !empty($this->_joinWhere)):
            throw new Exception("You can not call both the where and joinWhere methods in a join");
        endif;
        if(!empty($this->_joinWhere)):
            foreach($this->_joinWhere as $key => $value){
                $this->_whereCols[] = $key;
            }   
            $this->_selectQuery .=" WHERE ".$this->_whereTable.".".$this->_whereCols[0]." = :".$this->_whereCols[0];
            if(count($this->_where) == 2):
                $this->_selectQuery .=" AND ".$this->_whereTable.".".$this->_whereCols[1]." = :".$this->_whereCols[1];
            endif; 
            if(count($this->_where) == 3):
                $this->_selectQuery .=" OR ".$this->_whereTable.".".$this->_whereCols[2]." = :".$this->_whereCols[2];
            endif;  
        endif;
        if(!empty($this->_orderBy)):
            $this->_selectQuery .=" ORDER BY ".$this->_orderBy;
        endif;  
        return $this->_selectQuery;
    }

    /**
     * @param array $tableColumns A numerical array containing column names to select
     * @param array $tableNames An associative array containing the table name and column name to join the table. You can join a maximum of 4 tables using the same type of join.
     * @param string $type A string containing the type of join to perform. Default is INNER JOIN
     * @return array
     * @throws Exception When data supplied is not of type array
     */
    public function join(array $tableColumns , array $tableNames, string $type = "INNER JOIN"){
        if(!is_array($tableColumns) && !is_array($tableNames)):
            throw new Exception("Expected data supplied to be of type array");
        endif;
        $stmt = $this->_conn->prepare($this->buildJoin($tableColumns, $tableNames, $type));
        if(!empty($this->_where)):
            $stmt->execute($this->_where);
        endif;    
        if(!empty($this->_joinWhere)):
            $stmt->execute($this->_joinWhere);
        endif;    
        if(empty($this->_where) && empty($this->_joinWhere)):
        $stmt->execute();
        endif;
        $this->reset();
        return $stmt->fetchAll();
    }

    /**
     * @param string $tableName The name of the table to use in the where clause when the column name is ambiguous(exists in both tables)
     * @param array $whereData An associative array of column name and value.
     * @return bool
     * @throws Exception WHen the data supplied in the condition is not of type array
     */
    public function joinWhere(string $tableName, array $whereData) {
        if(!is_array($whereData)):
            throw new Exception("Expected the condition data to be of type array");
        endif;    
        $this->_joinWhere = array_merge($this->_joinWhere, $whereData);
        $this->_whereTable = $tableName;
        return true;
    }

    /**
     * @param string $query A string containing a custom query to execute. Must be a select Query
     * @return array::fetchAll An associative array containing rows returned from the query
     * @throws Exception when a select query is not parsed
     */
    public function customQuery(string $query){
        if(strpos($query, "SELECT") === false):
            throw new Exception("Expected a SELECT query but instead saw ".$query);
        endif;    
        $stmt = $this->_conn->prepare($this->model->xss_clean($query));
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * @param string $query A string containing a custom query to execute. Must be an UPDATE Query
     * @return int affected rows returned from the query
     * @throws Exception When an Update query is not parsed 
     */
    public function customUpdate(string $query){
        if(strpos($query, "UPDATE") === false):
            throw new Exception("Expected an Update query but instead saw ".$query);
        endif;    
        $this->_conn->beginTransaction();
        $stmt = $this->_conn->prepare($this->model->xss_clean($query));
        $stmt->execute();
        $this->_conn->commit();
        return $stmt->rowCount();
    }
    private function buildCount(string $column, string $tableName) {
        $this->_selectQuery = "SELECT count($column) FROM ".$tableName;
        if(!empty($this->_where)):
            foreach($this->_where as $key => $value) {
                $this->_whereCols[] = $key;
            }
            $this->_selectQuery .=" WHERE ".$this->_whereCols[0]." = :".$this->_whereCols[0];
            if(count($this->_where) == 2):
                $this->_selectQuery .=" AND ".$this->_whereCols[1]." = :".$this->_whereCols[1];
            endif; 
            if(count($this->_where) == 3):
                $this->_selectQuery .=" OR ".$this->_whereCols[2]." = :".$this->_whereCols[2];
            endif;    
        endif;
        return $this->_selectQuery;
    }
    private function buildSum(string $column, string $tableName) {
        $this->_selectQuery = "SELECT SUM($column) FROM ".$tableName;
        if(!empty($this->_where)):
            foreach($this->_where as $key => $value) {
                $this->_whereCols[] = $key;
            }
            $this->_selectQuery .=" WHERE ".$this->_whereCols[0]." = :".$this->_whereCols[0];
            if(count($this->_where) == 2):
                $this->_selectQuery .=" AND ".$this->_whereCols[1]." = :".$this->_whereCols[1];
            endif; 
            if(count($this->_where) == 3):
                $this->_selectQuery .=" OR ".$this->_whereCols[2]." = :".$this->_whereCols[2];
            endif;    
        endif;
        return $this->_selectQuery;
    }
     /**
     * @param string $column A column name from where the count will happen
     * @param string $tableName A table name to count from
     * @return int   Number of count returned
     */
    public function row_count(string $column, string $tableName){ 
        $stmt = $this->_conn->prepare($this->buildCount($column, $tableName));
        empty($this->_where) ? $stmt->execute(): $stmt->execute($this->_where);
        $this->reset();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();
        return $result['count('.$column.')'];
    }

    /**
     * @abstract Function to built counting of data
     * @param string $column
     * @param string $tableName
     * @return string Query to executed
     */
    private function buildMax(string $column, string $tableName) {
                $this->_selectQuery = "SELECT MAX($column) FROM $tableName";
        if(!empty($this->_where)):
            foreach($this->_where as $key => $value) {
                $this->_whereCols[] = $key;
            }
            $this->_selectQuery .=" WHERE ".$this->_whereCols[0].$this->_operator." :".$this->_whereCols[0];
            if(count($this->_where) == 2):
                $this->_selectQuery .=" AND ".$this->_whereCols[1].$this->_operator." :".$this->_whereCols[1];
            endif; 
            if(count($this->_where) == 3):
                $this->_selectQuery .=" OR ".$this->_whereCols[2].$this->_operator." :".$this->_whereCols[2];
            endif;    
        endif;
        return $this->_selectQuery;

    }

    /**
     * @abstract Function to built counting of data
     * @param string $column
     * @param string $tableName
     * @return string Query to executed
     */
    private function buildMin(string $column, string $tableName) {
                $this->_selectQuery = "SELECT MIN($column) FROM $tableName";
        if(!empty($this->_where)):
            foreach($this->_where as $key => $value) {
                $this->_whereCols[] = $key;
            }
            $this->_selectQuery .=" WHERE ".$this->_whereCols[0].$this->_operator." :".$this->_whereCols[0];
            if(count($this->_where) == 2):
                $this->_selectQuery .=" AND ".$this->_whereCols[1].$this->_operator." :".$this->_whereCols[1];
            endif; 
            if(count($this->_where) == 3):
                $this->_selectQuery .=" OR ".$this->_whereCols[2].$this->_operator." :".$this->_whereCols[2];
            endif;    
        endif;
        return $this->_selectQuery;

    }

    /**
     * @param string $column The column name to get the maximum value from
     * @param string $tableName The name of the table to act upon
     * @return int|string The maximum value obtained
     */
    public function get_max_value(string $column, string $tableName) {
        $stmt = $this->_conn->prepare($this->buildMax($column, $tableName));
        empty($this->_where)? $stmt->execute() : $stmt->execute($this->_where);
        $this->reset();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();
        return $result['MAX('.$column.')'];
    }

    /**
     * @param string $column The column name to get the minimum value from
     * @param string $tableName The name of the table to act upon
     * @return int|string The minimum value obtained
     */
    public function get_min_value(string $column, string $tableName) {
        $stmt = $this->_conn->prepare($this->buildMin($column, $tableName));
        empty($this->_where)? $stmt->execute() : $stmt->execute($this->_where);
        $this->reset();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();
        return $result['MIN('.$column.')'];
    }

      /**
     * @param string $column The column name to sum up the value from
     * @param string $tableName The name of the table to act upon
     * @return int|string The sum of value obtained
     */
    public function get_total_value(string $column, string $tableName) {
        $stmt = $this->_conn->prepare($this->buildSum($column, $tableName));
        empty($this->_where)? $stmt->execute() : $stmt->execute($this->_where);
        $this->reset();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();
        return $result['SUM('.$column.')'];
    }

    public function __destruct()
    {
        $this->_conn = null;
    }
}
//End of Db_model
