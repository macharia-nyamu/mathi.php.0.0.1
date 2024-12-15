<?php

class Operations extends Database
{
    // constructor
    public function __construct() 
    {
        parent::__construct();
    }

    // CRUD OPERATIONS
    // fetch data from the database
    public function SelectQuery($containerArray = null)
    {
        $fetchResponse = new Responses();
        $fetchResponse = $fetchResponse->response;

        // fetch incoming data
        $returns    = $containerArray['returns'] ?? null;
        $table      = $containerArray['table'][0] ?? null;
        $where      = $containerArray['where'] ?? null;
        $operator   = $containerArray['operator'][0] ?? "AND";
        $logics     = $containerArray['logics'] ?? null;

        // validate common properties
        $isValid    = $this->operationCommonMethods($table, $containerArray);

        if($isValid !== null)
        {
            $fetchResponse['showText'] = $isValid;
            $fetchResponse['mainSession']['display'] = false;
            return $fetchResponse;
        }

        $table          = strtolower($table);
        // construct fetch columns
        $fetchColumns   = $this->constructFetchColumns($returns);
        
        // construct the where clause
        $whereClause    = $this->constructWhereClause($where, $operator);

        if(!$whereClause['isSuccess'])
        {
            $fetchResponse['showText'] = $whereClause['responseMessage'];
            $fetchResponse['mainSession']['display'] = false;
            return $fetchResponse;
        }

        $whereStatement     = $whereClause['responseMessage'];
        $parametersValues   = $whereClause['responseParams'];
        
        // get the categorization logic
        $orderLogic = $this->constructLogics($logics);

        if(!$orderLogic['isSuccess'])
        {
            $fetchResponse['showText'] = $orderLogic['logicResponse'];
            return $fetchResponse;
        }

        $logicString = $orderLogic['logicString'];

        // construct the whole query string
        $query      = "SELECT $fetchColumns FROM $table $whereStatement $logicString";

        //execute the query
        try 
        {
            $makeExecution = $this->executeQuery($query, $parametersValues, "SelectQuery");

            if(!$makeExecution['isSuccess'])
            {
                $fetchResponse['showText'] = $makeExecution['responseMessage'];
            }
            else
            {
                $fetchResponse['isSuccess'] = true;
                $fetchResponse['payLoad']   = $makeExecution['responseData'];
                $fetchResponse['showText']  = $makeExecution['responseMessage'];
            }
        } 
        catch (Exception $e) 
        {
            $fetchResponse['showText']  = "Error querying::". $e->getMessage();
        }

        // return
        return $fetchResponse;
    }

    // insert into the database
    public function InsertQuery($containerArray = null)
    {
        $insertResponse = new Responses();
        $insertResponse = $insertResponse->response;

        // fetch incoming data
        $table   = strtolower($containerArray['table'][0] ?? null);
        $columns = $containerArray['columns'] ?? null;
        $values  = $containerArray['values'] ?? null;
        $where   = $containerArray['where'] ?? null;
        $primary = $containerArray['primary'] ?? null;

        // validate common properties
        $isValid = $this->operationCommonMethods($table, $containerArray);
        if ($isValid !== null) 
        {
            $insertResponse['showText'] = $isValid;
            $insertResponse['mainSession']['display'] = false;
            return $insertResponse;
        }

        if (!is_array($columns) || count($columns) === 0 || !is_array($values) || count($values) === 0) 
        {
            $insertResponse['showText'] = 'Invalid data container::COL';
            $insertResponse['mainSession']['display'] = false;
            return $insertResponse;
        }

        // structure the columns
        $columnsString = implode(',', $columns);

        if (count($columns) !== count($values)) 
        {
            $insertResponse['showText'] = 'Invalid data count::COL';
            $insertResponse['mainSession']['display'] = false;
            return $insertResponse;
        }

        // check if the exists logic is already added to the database
        if (!empty($where)) 
        {
            $dataContainer = 
            [
                'returns'  => $primary,
                'table'    => [$table],
                'where'    => $where,
                'operator' => ['OR'],
                'logics'   => [['LIMIT', 1]],
            ];

            $alreadyExists = $this->SelectQuery($dataContainer);

            // if already exist and matches
            if ($alreadyExists['isSuccess']) 
            {
                // check if any data is returned
                if (count($alreadyExists['payLoad']) > 0) 
                {
                    $insertResponse['showText'] = 'Some values already in use::READ';
                    return $insertResponse;
                }
            } 
            else 
            {
                $insertResponse['mainSession']['display'] = $alreadyExists['mainSession']['display'];
                $insertResponse['showText'] = $alreadyExists['showText'];
                return $insertResponse;
            }
        }

        // insert the values
        $holders = implode(',', array_fill(0, count($columns), '?'));
        $query   = "INSERT INTO {$table} ({$columnsString}) VALUES ({$holders})";

        // execute the query
        try 
        {
            $makeExecution = $this->executeQuery($query, $values, "InsertQuery");

            if(!$makeExecution['isSuccess'])
            {
                $insertResponse['showText'] = $makeExecution['responseMessage'];
            }
            else
            {
                $insertResponse['isSuccess']   = true;
                $insertResponse['showText']  = $makeExecution['responseMessage'];
            }
        } 
        catch (Exception $e) 
        {
            $insertResponse['showText'] = "Error querying::" . $e->getMessage();
        }

        return $insertResponse;
    }

    // update entries in the db
    public function UpdateQuery($containerArray = null)
    {
        $updateResponse = new Responses();
        $updateResponse = $updateResponse->response;

        // fetch incoming data
        $table      = strtolower($containerArray['table'][0] ?? null);
        $set        = $containerArray['set'] ?? null;
        $where      = $containerArray['where'] ?? null;

        // validate common properties
        $isValid = $this->operationCommonMethods($table, $containerArray);
        if($isValid !== null)
        {
            $updateResponse['showText'] = $isValid;
            $updateResponse['mainSession']['display'] = false;
            return $updateResponse;
        }

        // check is empty
        if(empty($set) || !is_array($set) || empty($where) || !is_array($where))
        {
            $updateResponse['showText'] = "Empty request::UPD";
            $updateResponse['mainSession']['display'] = false;
            return $updateResponse;
        }

        // get the setters implode
        $setArray       = [];
        $setParameters  = [];
        foreach ($set as $eachSetter) 
        {
            $columnKey      = $eachSetter[0];
            $columnValue    = $eachSetter[1];
            $setArray[]     = "$columnKey = ?";

            $setParameters[]= $columnValue;
        }

        $setString      = implode(', ', $setArray);
        $whereString    = $this->constructWhereClause($where, "AND");

        if(!$whereString['isSuccess'])
        {
            $updateResponse['showText'] = $whereString['responseMessage'];
            $updateResponse['mainSession']['display'] = false;
            return $updateResponse;
        }

        $whereClause        = $whereString['responseMessage'];
        $whereParameters    = $whereString['responseParams'];
        $globalParameters   = array_merge($setParameters, $whereParameters);

        // whole query structure
        $query = "UPDATE {$table} SET {$setString} {$whereClause} ";

        try
        {
            $makeExecution = $this->executeQuery($query, $globalParameters, "UpdateQuery");

            if(!$makeExecution['isSuccess'])
            {
                $updateResponse['showText'] = $makeExecution['responseMessage'];
            }
            else
            {
                $updateResponse['isSuccess']   = true;
                $updateResponse['showText']  = $makeExecution['responseMessage'];
            }            
        }
        catch (mysqli_sql_exception $e)
        {
            $updateResponse['showText']  = 'Error processing request::' . $e->getMessage();
        }

        // return
        return $updateResponse;
    }

    // delete entry in the db
    public function DeleteQuery($container = null)
    {
        $deleteResponse = new Responses();
        $deleteResponse = $deleteResponse->response;

        // fetch incoming data
        $table      = strtolower($container['table'][0] ?? null);
        $matches    = $container['matches'] ?? null;

        // validate common properties
        $isValid    = $this->operationCommonMethods($table, $container);
        if($isValid !== null)
        {
            $deleteResponse['showText'] = $isValid;
            $deleteResponse['mainSession']['display'] = false;
            return $deleteResponse;
        }

        // check if the updates - column is empty
        if(empty($matches) || !is_array($matches))
        {
            $deleteResponse['showText'] = "Prohibited request::UPD";
            return $deleteResponse;
        }

        // delete query
        $whereString    = $this->constructWhereClause($matches, "AND");

        if(!$whereString['isSuccess'])
        {
            $deleteResponse['showText'] = $whereString['responseMessage'];
            $deleteResponse['mainSession']['display'] = false;
            return $deleteResponse;
        }

        $whereClause    = $whereString['responseMessage'];
        $whereParameter = $whereString['responseParams'];

        // whole query structure
        $query = "DELETE FROM {$table} {$whereClause} ";

        try 
        {
            $makeExecution = $this->executeQuery($query, $whereParameter, "DeleteQuery");

            if(!$makeExecution['isSuccess'])
            {
                $deleteResponse['showText'] = $makeExecution['responseMessage'];
            }
            else
            {
                $deleteResponse['isSuccess']   = true;
                $deleteResponse['showText']  = $makeExecution['responseMessage'];
            }
        } 
        catch (mysqli_sql_exception $e)
        {
            $deleteResponse['showText']  = 'Error processing request::' . $e->getMessage();
        }

        return $deleteResponse;
    }

    // CRUD OPERATIONS


    // INLINE VALIDATIONS
    // common methods
    private function operationCommonMethods($table, $validateContainer)
    {
        $message = null;

        // check for no parameter sent
        if($validateContainer === null)
        {
            $message = 'Invalid container data';
            return $message;
        }

        // check if there is a connection
        if(!$this->connection)
        {
            $message = 'Error connecting server::CONN';
            return $message;
        }
                
        // check the table
        if(empty($table))
        {
            $message = 'Method error::TB';
            return $message;
        }

        // return
        return $message;
    }

    // construct fetch columns
    private function constructFetchColumns($returns)
    {
        // return
        $stringImplode = implode(', ', $returns);
        return !empty($stringImplode)? $stringImplode : "*";
    }

    // construct the where clause
    private function constructWhereClause($where, $operator)
    {
        $columnsValues      = [];
        $columnsParameters  = [];
        $conditionString    = "";
        $acceptedValues     = [">", "<", ">=", "<=", "!=", "<>", "LIKE", "NOT LIKE", "IN", "NOT IN"];
        $responseStructure  = 
        [
            'isSuccess'         => false, 
            'responseMessage'   => null, 
            'responseParams'    => null,
            'requestError'      => false
        ];

        // when conditions sent
        if(count($where) > 0)
        {
            // has at least a value
            foreach ($where as $column) 
            {
                // Check if column has at least 2 elements (name and value)
                if (count($column) < 2) 
                {
                    $responseStructure['requestError'] = true;
                    $responseStructure['responseMessage'] = "Invalid condition format";
                    break;
                }

                if(count($column) === 3)
                {
                    // columnSpecial is optional (third value)
                    $columnSpecial = $column[1] ?? null; 

                    // check if accepted special
                    if(in_array($columnSpecial, $acceptedValues))
                    {
                        if ($columnSpecial === 'IN' || $columnSpecial === 'NOT IN') 
                        {
                            // Generate placeholders for each element in column[1]
                            $placeholders       = implode(', ', array_fill(0, count($column[2]), '?'));
                            $columnsValues[]    = "{$column[0]} {$columnSpecial} ({$placeholders})"; 

                            // Append each value in column[2] as individual parameters
                            foreach ($column[2] as $value) 
                            {
                                $columnsParameters[] = $value;
                            }
                        } 
                        else 
                        {
                            // For other conditions, keep it as single placeholder
                            $columnsValues[]    = "{$column[0]} {$columnSpecial} ?"; // Example: ['col', '>', $value]
                            $columnsParameters[] = $column[2];  // Assign the column value
                        }
                    }
                    else
                    {
                        $responseStructure['requestError'] = true;
                        $responseStructure['responseMessage'] = "Invalid special request";
                        break;
                    }
                }
                else
                {
                    $columnsValues[]        = "{$column[0]} = ?"; // Example: ['col', $value]
                    $columnsParameters[]    = $column[1];  // Assign the column value
                }
            }

            if($responseStructure['requestError'])
            {
                // return error
                return $responseStructure;
            }

            // Work on imploding the condition string
            $conditionString = ' WHERE ' . implode(' ' . $operator . ' ', $columnsValues);
        }

        // Return the result with the final condition string and parameters
        $responseStructure['isSuccess']         = true;
        $responseStructure['responseMessage']   = $conditionString;
        $responseStructure['responseParams']    = $columnsParameters;
        return $responseStructure;
    }

    // construct logics
    private function constructLogics($logics)
    {
        $logicArray = [];

        $responseStructure = 
        [
            'logicResponse'     => '',
            'logicString'       => '',
            'isSuccess'         => false 
        ];

        if (count($logics) > 0 && $logics !== null)
        {
            foreach ($logics as $logic)
            {
                if(count($logic) < 2)
                {
                    $responseStructure['logicResponse'] = 'Incomplete logics';
                    return $responseStructure;
                }

                // check if there are three entries
                $logicKey   = $logic[0];
                $logicValue = $logic[1];
                $thirdEntry = $logic[2]?? null;

                if($thirdEntry === null)
                {
                    // handle 2 parts logic
                    $logicArray[] = " {$logicKey} {$logicValue} ";
                }
                else
                {
                    // handle 3 parts logic
                    $logicArray[] = " {$logicKey} {$logicValue} {$thirdEntry} ";
                }
            }
        }

        $responseStructure['isSuccess']     = true;
        $responseStructure['logicResponse'] = 'Logics processed successfully';
        $responseStructure['logicString']   = implode(' ', $logicArray);
        return $responseStructure;
    }

    // execute the query
    private function executeQuery($query, $params, $methodType)
    {
        $responseStructure = 
        [
            'isSuccess'         => false,
            'responseMessage'   => '',
            'responseData'      => []
        ];

        $stmt = $this->connection->prepare($query);

        if ($stmt === false) 
        {
            $responseStructure['responseMessage'] = "Execution Error::STMT";
            return $responseStructure;
        }

        if (!empty($params) && is_array($params)) 
        {
            // Bind parameters dynamically based on the type of each parameter
            $paramTypes = '';
            foreach ($params as $param) 
            {
                $paramTypes .= is_int($param) ? 'i' : (is_double($param) ? 'd' : 's');
            }

            if (!$stmt->bind_param($paramTypes, ...$params)) 
            {
                $responseStructure['responseMessage'] = "Binding parameters failed::BIND";
                return $responseStructure;
            }
        }

        // For SelectQuery: fetch the data
        if ($methodType === 'SelectQuery') 
        {
            if (!$stmt->execute()) 
            {
                $responseStructure['responseMessage'] = "Execute failed::EXEC - {$stmt->error}";
                return $responseStructure;
            }

            $result = $stmt->get_result();

            if ($result === null) 
            {
                $responseStructure['responseMessage'] = "Getting result set failed::RES";
                return $responseStructure;
            }

            $returnData = [];
            while ($row = $result->fetch_assoc()) 
            {
                $returnData[] = $row;
            }

            $responseStructure['isSuccess']       = true;
            $responseStructure['responseMessage'] = "Success";
            $responseStructure['responseData']    = $returnData;
        } 
        else 
        {
            if (!$stmt->execute()) 
            {
                $responseStructure['responseMessage'] = "Execute failed::EXEC - {$stmt->error}";
                return $responseStructure;
            }

            // Get the affected rows count
            $affectedRows = $stmt->affected_rows;
            if ($affectedRows > 0) 
            {
                $responseStructure['isSuccess'] = true;
                $responseStructure['responseMessage'] = "{$affectedRows} rows affected.";
            } 
            else 
            {
                $responseStructure['responseMessage'] = "No rows affected.";
            }
        }

        return $responseStructure;
    }

    // INLINE VALIDATIONS
}