<?php

class SQL {

    private $host; 
    private $user; 
    private $pw; 
    private $db; 
    public $conn; 

    // set the DB connection to the DB you want: 
    public function set_connection($host, $user, $pw, $db) {
        $this->host = $host; 
        $this->user = $user; 
        $this->pw = $pw;  
        $this->db = $db; 
    }

    // executes a single query, or an array of queries: 
    public function query($queries = array()) {
        $this->open(); 
        $this->conn->autocommit(FALSE);
        $out = $this->_do_queries($queries); 
        $this->conn->commit(); 
        $this->close(); 
        return $out; 
    }

    // tests a query, or set of queries, using a mysql transaction. 
    public function test_query($queries) {
        $this->open(); 
        $this->conn->autocommit(FALSE);
        $out = $this->_do_queries($queries); 
        //$this->conn->commit(); 
        $this->conn->rollback(); 
        $this->close(); 
        return $out; 
    }

    // Does one query, or a bunch of queries, depending on how many were passed in: 
    // relies on _do_query_single()
    private function _do_queries($queries) {
        $out = array(); 
        if (!(gettype($queries) === 'array')) {
            $queries_new = array(); 
            $queries_new[] = $queries; 
            $queries = $queries_new; 
        }
        foreach ($queries as $query) {
            $result = $this->_do_query_single($query); 
            if (gettype($result) === 'array') {
                foreach ($result as $r) {
                    $out[] = $r; 
                }
            } else {
                $out[] = $result; 
            }
        }
        return $out; 
    }

    // internal function to preform a query and return an array of results: 
    private function _do_query_single($sql) {
        $out = array(); 
        //$this->conn = new mysqli($this->host, $this->user, $this->pw, $this->db); 

        // check connection
        if (mysqli_connect_errno()) {
              return 'mysql_error_connection'; 
        }

        // now run the query
        if ($this->conn->real_query($sql)) {
            // now get the result: 
            //$result = $this->conn->store_result(); 
            if ($result = $this->conn->store_result()) {
                while ($row = $result->fetch_assoc()) {
                    $out[] = $row; 
                }   
            } else {
                if ($this->conn->error) {
                    return 'mysql_error_store_result'; 
                } else {
                    return true; 
                }
            }   
        }
        else {
            echo "There was an error, here is that query: \n $sql";
            return 'mysql_error_query'; 
        }

        return $out; 
    }

    // opens the mysql connection
    public function open() {
        $this->conn = new mysqli($this->host, $this->user, $this->pw, $this->db);
    }

    // close the connection 
    public function close() {
        $this->conn->close();
    }

}

?>
